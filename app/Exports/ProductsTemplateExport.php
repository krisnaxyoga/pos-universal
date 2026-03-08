<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nama',
            'sku',
            'barcode',
            'deskripsi',
            'kategori',
            'harga_jual',
            'harga_modal',
            'stok',
            'stok_minimum',
        ];
    }

    public function array(): array
    {
        return [
            ['Contoh Produk 1', 'SKU001', '8991234567890', 'Deskripsi produk 1', 'Makanan', 15000, 10000, 100, 10],
            ['Contoh Produk 2', 'SKU002', '8991234567891', 'Deskripsi produk 2', 'Minuman', 8000, 5000, 50, 5],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }
}
