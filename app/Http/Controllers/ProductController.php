<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $products = $query->paginate(15);
        $categories = Category::where('is_active', true)->get();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        
        Product::create($data);
        
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    public function show(Product $product)
    {
        $product->load('category');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product->update($data);
        
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah pernah ditransaksikan');
        }
        
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    /**
     * Generate barcode for product
     */
    public function generateBarcode(Product $product, BarcodeService $barcodeService): JsonResponse
    {
        try {
            $product = $barcodeService->assignBarcodeToProduct($product);
            
            return response()->json([
                'success' => true,
                'barcode' => $product->barcode,
                'barcode_image' => $product->barcode_image,
                'message' => 'Barcode berhasil digenerate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate barcode for product
     */
    public function regenerateBarcode(Product $product, BarcodeService $barcodeService): JsonResponse
    {
        try {
            $product = $barcodeService->regenerateBarcodeForProduct($product);
            
            return response()->json([
                'success' => true,
                'barcode' => $product->barcode,
                'barcode_image' => $product->barcode_image,
                'message' => 'Barcode berhasil di-regenerate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal regenerate barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print barcode for product
     */
    public function printBarcode(Product $product, BarcodeService $barcodeService): Response
    {
        try {
            if (!$product->hasBarcode()) {
                $product = $barcodeService->assignBarcodeToProduct($product);
            }

            $html = $barcodeService->generatePrintableBarcode($product, true);
            
            $printHtml = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Barcode - ' . $product->name . '</title>
                <style>
                    @page { margin: 1cm; size: auto; }
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
                    .barcode-container { display: flex; flex-wrap: wrap; justify-content: space-around; }
                    .barcode-print { 
                        width: 6cm; 
                        height: 4cm; 
                        border: 1px dashed #ccc; 
                        margin: 5mm; 
                        display: flex; 
                        flex-direction: column; 
                        justify-content: center; 
                        align-items: center;
                        text-align: center;
                        box-sizing: border-box;
                        padding: 2mm;
                    }
                    @media print {
                        .no-print { display: none; }
                        .barcode-print { border: none; page-break-inside: avoid; }
                    }
                </style>
                <script>
                    window.onload = function() {
                        window.print();
                    }
                </script>
            </head>
            <body>
                <div class="no-print" style="text-align: center; margin-bottom: 20px;">
                    <h2>Print Barcode: ' . $product->name . '</h2>
                    <p>Jendela print akan terbuka otomatis</p>
                </div>
                <div class="barcode-container">
                    ' . str_repeat($html, 8) . '
                </div>
            </body>
            </html>';
            
            return response($printHtml)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get barcode image for product
     */
    public function getBarcodeImage(Product $product, BarcodeService $barcodeService): JsonResponse
    {
        try {
            if (!$product->hasBarcode()) {
                $product = $barcodeService->assignBarcodeToProduct($product);
            }
            
            return response()->json([
                'success' => true,
                'barcode' => $product->barcode,
                'barcode_image' => $product->barcode_image
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search product by barcode
     */
    public function searchByBarcode(Request $request, BarcodeService $barcodeService): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $product = $barcodeService->findProductByBarcode($request->barcode);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk dengan barcode tersebut tidak ditemukan'
            ], 404);
        }

        $product->load('category');

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => $product->category->name ?? 'N/A',
                'image' => $product->image ? asset('storage/' . $product->image) : null
            ]
        ]);
    }
}
