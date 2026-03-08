<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    private $categories;
    private $rowCount = 0;

    public function __construct()
    {
        $this->categories = Category::pluck('id', 'name')->toArray();
    }

    public function model(array $row)
    {
        // Cari category by nama atau ID
        $categoryId = $this->resolveCategory($row['kategori'] ?? $row['category'] ?? null);

        if (!$categoryId) {
            return null;
        }

        $stock = (int) ($row['stok'] ?? $row['stock'] ?? 0);

        $this->rowCount++;

        return new Product([
            'name'          => $row['nama'] ?? $row['name'],
            'sku'           => $row['sku'],
            'barcode'       => $row['barcode'] ?? null,
            'description'   => $row['deskripsi'] ?? $row['description'] ?? null,
            'price'         => $row['harga_jual'] ?? $row['price'] ?? 0,
            'cost'          => $row['harga_modal'] ?? $row['cost'] ?? 0,
            'stock'         => $stock,
            'initial_stock' => $stock,
            'min_stock'     => (int) ($row['stok_minimum'] ?? $row['min_stock'] ?? 0),
            'category_id'   => $categoryId,
            'is_active'     => true,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama'       => 'required_without:name',
            'name'       => 'required_without:nama',
            'sku'        => 'required|unique:products,sku',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required_without' => 'Kolom nama/name wajib diisi',
            'name.required_without' => 'Kolom nama/name wajib diisi',
            'sku.required'          => 'Kolom SKU wajib diisi',
            'sku.unique'            => 'SKU ":input" sudah ada di database',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    private function resolveCategory($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        // Cek apakah value adalah ID
        if (is_numeric($value) && Category::where('id', $value)->exists()) {
            return (int) $value;
        }

        // Cek apakah value adalah nama category
        if (isset($this->categories[$value])) {
            return $this->categories[$value];
        }

        // Buat category baru jika belum ada
        $category = Category::create([
            'name' => $value,
            'is_active' => true,
        ]);

        $this->categories[$value] = $category->id;

        return $category->id;
    }
}
