<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Shared catalog: shob product (admin + onno owner-der), search + pagination.
     * in_my_shop = true hole ei owner-er shop-e already listed.
     */
    public function index(Request $request)
    {
        $shop = $this->ownerShop($request);

        $products = Product::with('creator:id,name,role')
            ->withExists([
                'listings as in_my_shop' => fn ($q) => $q->where('shop_id', $shop->id),
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return response()->json($products);
    }

    /**
     * Notun product create: shared catalog-e jabe + nijer shop-e auto-list hobe.
     */
    public function store(Request $request)
    {
        $shop = $this->ownerShop($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $data['created_by'] = $request->user()->id;

        $listing = DB::transaction(function () use ($data, $shop) {
            $product = Product::create($data);

            return ShopProduct::create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'price' => $product->base_price,
                'is_active' => true,
            ]);
        });

        return response()->json($listing->load('product'), 201);
    }

    private function ownerShop(Request $request)
    {
        $shop = $request->user()->shop;

        abort_if(!$shop, 422, 'You do not have a shop assigned yet. Please contact the admin.');

        return $shop;
    }
}