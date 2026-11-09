<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function listings()
    {
        return $this->hasMany(ShopProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'shop_products')
            ->withPivot(['price', 'name', 'description', 'thumbnail', 'is_active'])
            ->withTimestamps();
    }
}