<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Shob shop-er visible product ek jaygay, shop-er naam shoho.
     * Kono shop-e assign na thaka product ekhane ashbe na
     * (karon ei query shop_products table theke ashe).
     */
    public function index(Request $request)
    {
        $listings = ShopProduct::with(['product', 'shop:id,name,slug'])
            ->where('is_active', true)
            ->whereHas('shop', fn ($q) => $q->where('is_active', true))
            ->when($request->filled('shop'), function ($q) use ($request) {
                $q->whereHas('shop', fn ($s) => $s->where('slug', $request->shop));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', $s)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $s));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 12));

        $listings->getCollection()->transform(fn (ShopProduct $l) => [
            'id' => $l->id,
            'name' => $l->display_name,
            'description' => $l->display_description,
            'price' => $l->price,
            'thumbnail_url' => $l->display_thumbnail_url,
            'shop' => [
                'id' => $l->shop->id,
                'name' => $l->shop->name,
                'slug' => $l->shop->slug,
            ],
        ]);

        return response()->json($listings);
    }
}