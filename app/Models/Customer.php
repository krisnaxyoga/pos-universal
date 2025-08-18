<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'birth_date',
        'gender',
        'customer_code',
        'total_spent',
        'total_transactions',
        'last_transaction_at',
        'is_active'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_transaction_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    // Accessors & Mutators
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Helper methods
    public function generateCustomerCode()
    {
        $prefix = 'CUST';
        $number = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        return $prefix . $number;
    }

    public function updateTransactionStats($amount)
    {
        $this->increment('total_transactions');
        $this->increment('total_spent', $amount);
        $this->update(['last_transaction_at' => now()]);
    }

    public static function findOrCreateByEmail($email, $data = [])
    {
        $customer = static::byEmail($email)->first();
        
        if (!$customer) {
            $customer = static::create(array_merge($data, ['email' => $email]));
            $customer->update(['customer_code' => $customer->generateCustomerCode()]);
        }
        
        return $customer;
    }

    public static function findOrCreateByPhone($phone, $data = [])
    {
        $customer = static::byPhone($phone)->first();
        
        if (!$customer) {
            $customer = static::create(array_merge($data, ['phone' => $phone]));
            $customer->update(['customer_code' => $customer->generateCustomerCode()]);
        }
        
        return $customer;
    }
}
