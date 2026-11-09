<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    protected $fillable = [
        'shop_id',
        'product_id',
        'price',
        'name',
        'description',
        'thumbnail',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'thumbnail_url',
        'display_name',
        'display_description',
        'display_thumbnail_url',
    ];

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null
        );
    }

    // Shop-specific value thakle seta, nahole catalog product-er value
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->name ?: $this->product?->name);
    }

    protected function displayDescription(): Attribute
    {
        return Attribute::get(fn () => $this->description ?: $this->product?->description);
    }

    protected function displayThumbnailUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->thumbnail) {
                return asset('storage/' . $this->thumbnail);
            }

            return $this->product?->thumbnail_url;
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}