<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Nasi Goreng',
                'description' => 'Nasi goreng spesial dengan telur',
                'sku' => 'NG001',
                'barcode' => '123456789001',
                'price' => 15000,
                'cost' => 10000,
                'stock' => 50,
                'min_stock' => 5,
                'category_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ayam Geprek',
                'description' => 'Ayam geprek dengan sambal pedas',
                'sku' => 'AG001',
                'barcode' => '123456789002',
                'price' => 18000,
                'cost' => 12000,
                'stock' => 30,
                'min_stock' => 5,
                'category_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'sku' => 'ETM001',
                'barcode' => '123456789003',
                'price' => 5000,
                'cost' => 2000,
                'stock' => 100,
                'min_stock' => 10,
                'category_id' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Kopi Hitam',
                'description' => 'Kopi hitam tanpa gula',
                'sku' => 'KH001',
                'barcode' => '123456789004',
                'price' => 8000,
                'cost' => 4000,
                'stock' => 80,
                'min_stock' => 10,
                'category_id' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Keripik Singkong',
                'description' => 'Keripik singkong rasa original',
                'sku' => 'KS001',
                'barcode' => '123456789005',
                'price' => 10000,
                'cost' => 6000,
                'stock' => 60,
                'min_stock' => 8,
                'category_id' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pulpen Biru',
                'description' => 'Pulpen tinta biru',
                'sku' => 'PB001',
                'barcode' => '123456789006',
                'price' => 3000,
                'cost' => 1500,
                'stock' => 200,
                'min_stock' => 20,
                'category_id' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Sapu Lidi',
                'description' => 'Sapu lidi untuk kebersihan',
                'sku' => 'SL001',
                'barcode' => '123456789007',
                'price' => 25000,
                'cost' => 15000,
                'stock' => 20,
                'min_stock' => 3,
                'category_id' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
