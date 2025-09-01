<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BarcodeService
{
    private BarcodeGeneratorSVG $svgGenerator;
    private BarcodeGeneratorPNG $pngGenerator;

    public function __construct()
    {
        $this->svgGenerator = new BarcodeGeneratorSVG();
        $this->pngGenerator = new BarcodeGeneratorPNG();
    }

    /**
     * Generate a unique barcode for a product
     */
    public function generateBarcodeNumber(Product $product): string
    {
        do {
            $barcode = $this->createBarcodeNumber($product->id);
        } while (Product::where('barcode', $barcode)->where('id', '!=', $product->id)->exists());

        return $barcode;
    }

    /**
     * Create barcode number based on product ID
     */
    private function createBarcodeNumber(int $productId): string
    {
        // Generate EAN-13 compatible barcode
        // Format: 2 (country code) + 5 (manufacturer) + 5 (product) + 1 (check digit)
        
        $countryCode = '20'; // Indonesia-like code
        $manufacturer = str_pad('12345', 5, '0', STR_PAD_LEFT); // Fixed manufacturer code
        $productCode = str_pad($productId, 5, '0', STR_PAD_LEFT);
        
        // Calculate check digit for EAN-13
        $code = $countryCode . $manufacturer . $productCode;
        $checkDigit = $this->calculateEAN13CheckDigit($code);
        
        return $code . $checkDigit;
    }

    /**
     * Calculate EAN-13 check digit
     */
    private function calculateEAN13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Generate barcode as SVG string
     */
    public function generateSVG(string $barcode, int $widthFactor = 2, int $height = 30): string
    {
        return $this->svgGenerator->getBarcode(
            $barcode,
            $this->svgGenerator::TYPE_EAN_13,
            $widthFactor,
            $height
        );
    }

    /**
     * Generate barcode as PNG and save to storage
     */
    public function generatePNG(string $barcode, int $widthFactor = 2, int $height = 30): string
    {
        $barcodeImage = $this->pngGenerator->getBarcode(
            $barcode,
            $this->pngGenerator::TYPE_EAN_13,
            $widthFactor,
            $height
        );

        $filename = 'barcodes/' . $barcode . '.png';
        Storage::disk('public')->put($filename, $barcodeImage);

        return $filename;
    }

    /**
     * Generate barcode as base64 encoded PNG
     */
    public function generateBase64PNG(string $barcode, int $widthFactor = 2, int $height = 30): string
    {
        $barcodeImage = $this->pngGenerator->getBarcode(
            $barcode,
            $this->pngGenerator::TYPE_EAN_13,
            $widthFactor,
            $height
        );

        return 'data:image/png;base64,' . base64_encode($barcodeImage);
    }

    /**
     * Validate barcode format
     */
    public function isValidBarcode(string $barcode): bool
    {
        // Check if barcode is 13 digits
        if (!preg_match('/^\d{13}$/', $barcode)) {
            return false;
        }

        // Validate EAN-13 check digit
        $checkDigit = $this->calculateEAN13CheckDigit(substr($barcode, 0, 12));
        return (int)$barcode[12] === $checkDigit;
    }

    /**
     * Generate and assign barcode to product
     */
    public function assignBarcodeToProduct(Product $product): Product
    {
        if (empty($product->barcode)) {
            $product->barcode = $this->generateBarcodeNumber($product);
            $product->save();
        }

        return $product;
    }

    /**
     * Regenerate barcode for product
     */
    public function regenerateBarcodeForProduct(Product $product): Product
    {
        $product->barcode = $this->generateBarcodeNumber($product);
        $product->save();

        return $product;
    }

    /**
     * Find product by barcode
     */
    public function findProductByBarcode(string $barcode): ?Product
    {
        return Product::where('barcode', $barcode)->first();
    }

    /**
     * Generate printable barcode HTML
     */
    public function generatePrintableBarcode(Product $product, bool $includeProductInfo = true): string
    {
        if (empty($product->barcode)) {
            $product = $this->assignBarcodeToProduct($product);
        }

        $svg = $this->generateSVG($product->barcode, 2, 40);
        
        $html = '<div class="barcode-print" style="text-align: center; page-break-inside: avoid; margin: 10px;">';
        
        if ($includeProductInfo) {
            $html .= '<div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">' . 
                     htmlspecialchars($product->name) . '</div>';
            $html .= '<div style="font-size: 10px; color: #666; margin-bottom: 5px;">SKU: ' . 
                     htmlspecialchars($product->sku) . '</div>';
        }
        
        $html .= '<div style="margin: 10px 0;">' . $svg . '</div>';
        $html .= '<div style="font-size: 10px; font-family: monospace; letter-spacing: 1px;">' . 
                 $product->barcode . '</div>';
        
        if ($includeProductInfo) {
            $html .= '<div style="font-size: 12px; font-weight: bold; margin-top: 5px;">Rp ' . 
                     number_format($product->price, 0, ',', '.') . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}