<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if settings table exists and has been migrated
        if (Schema::hasTable('settings')) {
            try {
                // Initialize default settings if needed
                Setting::initializeDefaults();
                
                // Load settings into config
                $this->loadSettingsToConfig();
                
                // Share settings with all views
                $this->shareSettingsWithViews();
                
            } catch (\Exception $e) {
                // If there's an error (like during migration), skip settings loading
                \Log::warning('Settings could not be loaded: ' . $e->getMessage());
            }
        }
    }

    /**
     * Load settings into Laravel config
     */
    private function loadSettingsToConfig(): void
    {
        // Load iPaymu settings into config
        $ipaymuVa = Setting::get('ipaymu_va');
        $ipaymuApiKey = Setting::get('ipaymu_api_key');
        $ipaymuEnvironment = Setting::get('ipaymu_environment', 'sandbox');
        
        if ($ipaymuVa) {
            Config::set('services.ipaymu.va', $ipaymuVa);
        }
        
        if ($ipaymuApiKey) {
            Config::set('services.ipaymu.api_key', $ipaymuApiKey);
        }
        
        Config::set('services.ipaymu.environment', $ipaymuEnvironment);
        
        // Update app name in config
        $appName = Setting::get('app_name');
        if ($appName) {
            Config::set('app.name', $appName);
        }
    }

    /**
     * Share settings with all views
     */
    private function shareSettingsWithViews(): void
    {
        View::composer('*', function ($view) {
            try {
                // Get all public settings for use in views
                $settings = [
                    'app_name' => Setting::get('app_name', config('app.name', 'POS System')),
                    'app_logo' => Setting::get('app_logo'),
                    'company_name' => Setting::get('company_name', 'Your Company Name'),
                    'company_address' => Setting::get('company_address', 'Your Company Address'),
                    'company_phone' => Setting::get('company_phone', 'Your Phone Number'),
                    'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!')
                ];
            } catch (\Exception $e) {
                // Fallback settings if database is not available
                $settings = [
                    'app_name' => config('app.name', 'POS System'),
                    'app_logo' => null,
                    'company_name' => 'Your Company Name',
                    'company_address' => 'Your Company Address',
                    'company_phone' => 'Your Phone Number',
                    'receipt_footer' => 'Terima kasih atas kunjungan Anda!'
                ];
            }
            
            $view->with('appSettings', $settings);
        });
    }
}