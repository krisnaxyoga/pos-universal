<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Test Category',
        ]);
    }

    public function test_product_is_low_stock_when_stock_below_minimum(): void
    {
        $product = Product::create([
            'name' => 'Low Stock Product',
            'sku' => 'LOW-001',
            'price' => 10000,
            'stock' => 5,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    public function test_product_is_not_low_stock_when_stock_above_minimum(): void
    {
        $product = Product::create([
            'name' => 'Normal Stock Product',
            'sku' => 'NORM-001',
            'price' => 10000,
            'stock' => 20,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertFalse($product->isLowStock());
    }

    public function test_product_is_low_stock_when_stock_equals_minimum(): void
    {
        $product = Product::create([
            'name' => 'Equal Stock Product',
            'sku' => 'EQ-001',
            'price' => 10000,
            'stock' => 10,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    public function test_product_has_barcode(): void
    {
        $productWithBarcode = Product::create([
            'name' => 'With Barcode',
            'sku' => 'BAR-001',
            'barcode' => '1234567890123',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertTrue($productWithBarcode->hasBarcode());

        $productWithoutBarcode = Product::create([
            'name' => 'Without Barcode',
            'sku' => 'NOBAR-001',
            'barcode' => null,
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertFalse($productWithoutBarcode->hasBarcode());
    }

    public function test_product_price_is_cast_to_decimal(): void
    {
        $product = Product::create([
            'name' => 'Decimal Price',
            'sku' => 'DEC-001',
            'price' => 10000.50,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertIsString($product->price);
    }

    public function test_product_is_active_is_cast_to_boolean(): void
    {
        $activeProduct = Product::create([
            'name' => 'Active',
            'sku' => 'ACT-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => 1,
        ]);

        $this->assertIsBool($activeProduct->is_active);
        $this->assertTrue($activeProduct->is_active);

        $inactiveProduct = Product::create([
            'name' => 'Inactive',
            'sku' => 'INACT-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => 0,
        ]);

        $this->assertIsBool($inactiveProduct->is_active);
        $this->assertFalse($inactiveProduct->is_active);
    }

    public function test_product_fillable_attributes(): void
    {
        $fillable = [
            'name', 'description', 'sku', 'barcode', 'price', 'cost',
            'stock', 'min_stock', 'category_id', 'image', 'is_active',
        ];

        $product = new Product();

        $this->assertEquals($fillable, $product->getFillable());
    }
}
