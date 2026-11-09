<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'shop';
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

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