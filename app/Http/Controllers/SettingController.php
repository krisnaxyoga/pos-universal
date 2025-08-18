<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        // Initialize default settings if they don't exist
        Setting::initializeDefaults();
        
        $settings = Setting::all()->keyBy('key');
        
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:20',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ipaymu_va' => 'nullable|string|max:255',
            'ipaymu_api_key' => 'nullable|string|max:255',
            'ipaymu_environment' => 'required|in:sandbox,production',
            'receipt_footer' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            // Handle logo upload
            if ($request->hasFile('app_logo')) {
                $logoFile = $request->file('app_logo');
                
                // Delete old logo if exists
                $oldLogo = Setting::get('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                
                // Store new logo
                $logoPath = $logoFile->store('logos', 'public');
                Setting::set('app_logo', $logoPath, 'file', 'Logo aplikasi (maksimal 2MB)', true);
            }

            // Update other settings
            $settingsToUpdate = [
                'app_name' => ['type' => 'string', 'description' => 'Nama aplikasi yang akan ditampilkan', 'public' => true],
                'company_name' => ['type' => 'string', 'description' => 'Nama perusahaan untuk struk', 'public' => true],
                'company_address' => ['type' => 'string', 'description' => 'Alamat perusahaan untuk struk', 'public' => true],
                'company_phone' => ['type' => 'string', 'description' => 'Nomor telepon perusahaan untuk struk', 'public' => true],
                'ipaymu_va' => ['type' => 'string', 'description' => 'iPaymu Virtual Account', 'public' => false],
                'ipaymu_api_key' => ['type' => 'string', 'description' => 'iPaymu API Key', 'public' => false],
                'ipaymu_environment' => ['type' => 'string', 'description' => 'iPaymu Environment (sandbox/production)', 'public' => false],
                'receipt_footer' => ['type' => 'string', 'description' => 'Footer yang ditampilkan di struk', 'public' => true]
            ];

            foreach ($settingsToUpdate as $key => $config) {
                if ($request->has($key)) {
                    Setting::set(
                        $key,
                        $request->input($key),
                        $config['type'],
                        $config['description'],
                        $config['public']
                    );
                }
            }

            return redirect()->route('settings.index')
                           ->with('success', 'Pengaturan berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    public function removeLogo()
    {
        try {
            $logo = Setting::get('app_logo');
            
            if ($logo && Storage::disk('public')->exists($logo)) {
                Storage::disk('public')->delete($logo);
            }
            
            Setting::set('app_logo', null, 'file', 'Logo aplikasi (maksimal 2MB)', true);
            
            return redirect()->back()
                           ->with('success', 'Logo berhasil dihapus');
            
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Gagal menghapus logo: ' . $e->getMessage());
        }
    }

    public function testIpaymu(Request $request)
    {
        try {
            // Test iPaymu connection with provided credentials
            $va = $request->input('ipaymu_va');
            $apiKey = $request->input('ipaymu_api_key');
            $environment = $request->input('ipaymu_environment', 'sandbox');
            
            if (!$va || !$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'VA dan API Key wajib diisi untuk test koneksi'
                ]);
            }

            // Create temporary iPaymu service instance with provided credentials
            $baseUrl = $environment === 'production' 
                ? 'https://my.ipaymu.com/api/v2' 
                : 'https://sandbox.ipaymu.com/api/v2';

            // Test with simple API call (get payment channels)
            $method = 'GET';
            $endpoint = '/payment-channels';
            $body = [];
            
            $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
            $requestBody = strtolower(hash('sha256', $jsonBody));
            $stringToSign = strtoupper($method) . ':' . $va . ':' . $requestBody . ':' . $apiKey;
            $signature = hash_hmac('sha256', $stringToSign, $apiKey);
            $timestamp = date('YmdHis');
            
            $url = $baseUrl . $endpoint;
            
            $ch = curl_init($url);
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'va: ' . $va,
                'signature: ' . $signature,
                'timestamp: ' . $timestamp
            ];
            
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection Error: ' . $curlError
                ]);
            }
            
            if ($httpCode === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi iPaymu berhasil! API credentials valid.',
                    'environment' => $environment
                ]);
            } else {
                $responseData = json_decode($response, true);
                return response()->json([
                    'success' => false,
                    'message' => 'iPaymu API Error (HTTP ' . $httpCode . '): ' . ($responseData['message'] ?? 'Unknown error')
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test Error: ' . $e->getMessage()
            ]);
        }
    }
}