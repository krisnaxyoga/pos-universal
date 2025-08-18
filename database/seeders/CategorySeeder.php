<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Kategori untuk produk makanan',
                'is_active' => true,
            ],
            [
                'name' => 'Minuman',
                'description' => 'Kategori untuk produk minuman',
                'is_active' => true,
            ],
            [
                'name' => 'Snack',
                'description' => 'Kategori untuk produk snack dan cemilan',
                'is_active' => true,
            ],
            [
                'name' => 'Alat Tulis',
                'description' => 'Kategori untuk alat tulis kantor',
                'is_active' => true,
            ],
            [
                'name' => 'Peralatan Rumah Tangga',
                'description' => 'Kategori untuk peralatan rumah tangga',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
