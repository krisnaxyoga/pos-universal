<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('category')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'SKU',
            'Barcode',
            'Deskripsi',
            'Kategori',
            'Harga Jual',
            'Harga Modal',
            'Stok Awal',
            'Stok Saat Ini',
            'Stok Minimum',
            'Status',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->barcode,
            $product->description,
            $product->category->name ?? '',
            $product->price,
            $product->cost,
            $product->initial_stock,
            $product->stock,
            $product->min_stock,
            $product->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
