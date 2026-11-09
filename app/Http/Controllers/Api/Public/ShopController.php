<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Active shop-er list (customer home page).
     * Shudhu active listing gulor count dekhabe.
     */
    public function index(Request $request)
    {
        $shops = Shop::where('is_active', true)
            ->withCount(['listings as products_count' => fn ($q) => $q->where('is_active', true)])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 12));

        $shops->getCollection()->transform(fn ($shop) => [
            'id' => $shop->id,
            'name' => $shop->name,
            'slug' => $shop->slug,
            'description' => $shop->description,
            'products_count' => $shop->products_count,
        ]);

        return response()->json($shops);
    }

    /**
     * Ekta shop-er shob visible product (shop-specific price, name, thumbnail).
     */
    public function products(Request $request, string $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $listings = ShopProduct::with('product')
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($q) => $this->applySearch($q, $request->search))
            ->latest()
            ->paginate($request->integer('per_page', 12));

        $listings->getCollection()->transform(fn ($l) => $this->format($l));

        return response()->json([
            'shop' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'description' => $shop->description,
            ],
            'products' => $listings,
        ]);
    }

    private function applySearch($query, string $search)
    {
        $s = '%' . $search . '%';

        return $query->where(function ($q) use ($s) {
            $q->where('name', 'like', $s)
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $s));
        });
    }

    private function format(ShopProduct $l): array
    {
        return [
            'id' => $l->id,
            'name' => $l->display_name,
            'description' => $l->display_description,
            'price' => $l->price,
            'thumbnail_url' => $l->display_thumbnail_url,
        ];
    }
}