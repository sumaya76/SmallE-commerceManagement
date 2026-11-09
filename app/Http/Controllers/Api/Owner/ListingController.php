<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $shop = $this->ownerShop($request);

        $listings = ShopProduct::with('product')
            ->where('shop_id', $shop->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', $s)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $s));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return response()->json($listings);
    }

    /**
     * Catalog theke product nijer shop-e add.
     */
    public function store(Request $request)
    {
        $shop = $this->ownerShop($request);

        $data = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('shop_products', 'product_id')->where('shop_id', $shop->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'product_id.unique' => 'This product is already in your shop.',
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $data['shop_id'] = $shop->id;

        $listing = ShopProduct::create($data);

        return response()->json($listing->load('product'), 201);
    }

    public function show(Request $request, ShopProduct $listing)
    {
        $this->authorizeListing($request, $listing);

        return response()->json($listing->load('product'));
    }

    /**
     * Nijer shop-er price/details/thumbnail update.
     * File thakle POST + _method=PUT use korte hobe.
     */
    public function update(Request $request, ShopProduct $listing)
    {
        $this->authorizeListing($request, $listing);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        unset($data['remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $this->deleteFile($listing->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $this->deleteFile($listing->thumbnail);
            $data['thumbnail'] = null;
        } else {
            unset($data['thumbnail']);
        }

        $listing->update($data);

        return response()->json($listing->fresh()->load('product'));
    }

    /**
     * Shop theke remove (catalog product thakei jabe).
     */
    public function destroy(Request $request, ShopProduct $listing)
    {
        $this->authorizeListing($request, $listing);

        $this->deleteFile($listing->thumbnail);
        $listing->delete();

        return response()->json(['message' => 'Product removed from your shop.']);
    }

    // ---------- helpers ----------

    private function ownerShop(Request $request)
    {
        $shop = $request->user()->shop;

        abort_if(!$shop, 422, 'You do not have a shop assigned yet. Please contact the admin.');

        return $shop;
    }

    // Listing nijer shop-er kina check (onno shop-er listing chhuye dile 403)
    private function authorizeListing(Request $request, ShopProduct $listing): void
    {
        $shop = $this->ownerShop($request);

        abort_if($listing->shop_id !== $shop->id, 403, 'This listing does not belong to your shop.');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}