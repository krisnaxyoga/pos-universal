<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'price',
        'cost',
        'stock',
        'min_stock',
        'category_id',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function hasBarcode(): bool
    {
        return !empty($this->barcode);
    }

    public function getBarcodeImageAttribute(): ?string
    {
        if (!$this->hasBarcode()) {
            return null;
        }

        $barcodeService = app(\App\Services\BarcodeService::class);
        return $barcodeService->generateBase64PNG($this->barcode);
    }

    public function generateBarcode(): self
    {
        $barcodeService = app(\App\Services\BarcodeService::class);
        return $barcodeService->assignBarcodeToProduct($this);
    }

    public function regenerateBarcode(): self
    {
        $barcodeService = app(\App\Services\BarcodeService::class);
        return $barcodeService->regenerateBarcodeForProduct($this);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($product) {
            if (empty($product->barcode)) {
                $barcodeService = app(\App\Services\BarcodeService::class);
                $barcodeService->assignBarcodeToProduct($product);
            }
        });
    }
}
