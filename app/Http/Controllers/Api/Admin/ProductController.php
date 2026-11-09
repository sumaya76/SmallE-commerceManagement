<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('creator:id,name,role')
            ->withCount('listings')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($products);
    }

    public function store(Request $request)
    {
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

        $product = Product::create($data);

        return response()->json($product->load('creator:id,name,role'), 201);
    }

    public function show(Product $product)
    {
        return response()->json(
            $product->load('creator:id,name,role')->loadCount('listings')
        );
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ]);

        unset($data['remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $this->deleteFile($product->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $this->deleteFile($product->thumbnail);
            $data['thumbnail'] = null;
        } else {
            unset($data['thumbnail']);
        }

        $product->update($data);

        return response()->json($product->load('creator:id,name,role'));
    }

    public function destroy(Product $product)
    {
        $this->deleteFile($product->thumbnail);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}