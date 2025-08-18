<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;
        
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:255|unique:products,sku,' . $productId,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $productId,
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi',
            'sku.required' => 'SKU wajib diisi',
            'sku.unique' => 'SKU sudah digunakan',
            'barcode.unique' => 'Barcode sudah digunakan',
            'price.required' => 'Harga jual wajib diisi',
            'price.numeric' => 'Harga jual harus berupa angka',
            'cost.required' => 'Harga beli wajib diisi',
            'cost.numeric' => 'Harga beli harus berupa angka',
            'stock.required' => 'Stok wajib diisi',
            'stock.integer' => 'Stok harus berupa angka',
            'min_stock.required' => 'Minimum stok wajib diisi',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
