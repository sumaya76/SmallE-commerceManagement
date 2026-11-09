<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::with('owner:id,name,email')
            ->withCount('listings')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($shops);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'shop_owner'),
                Rule::unique('shops', 'owner_id'),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Shop::uniqueSlug($data['name']);

        $shop = Shop::create($data);

        return response()->json($shop->load('owner:id,name,email'), 201);
    }

    public function show(Shop $shop)
    {
        return response()->json(
            $shop->load('owner:id,name,email')->loadCount('listings')
        );
    }

    public function update(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'shop_owner'),
                Rule::unique('shops', 'owner_id')->ignore($shop->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['name'] !== $shop->name) {
            $data['slug'] = Shop::uniqueSlug($data['name'], $shop->id);
        }

        $shop->update($data);

        return response()->json($shop->load('owner:id,name,email'));
    }

    public function destroy(Shop $shop)
    {
        $shop->delete();

        return response()->json(['message' => 'Shop deleted successfully.']);
    }
}