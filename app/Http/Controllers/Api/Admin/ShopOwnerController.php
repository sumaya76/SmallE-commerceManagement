<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShopOwnerController extends Controller
{
    public function index(Request $request)
    {
        $owners = User::where('role', 'shop_owner')
            ->with('shop:id,name,slug,owner_id')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', $s)->orWhere('email', 'like', $s);
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($owners);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ]);

        $owner = DB::transaction(function () use ($data) {
            $owner = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'shop_owner',
            ]);

            $this->assignShop($owner, $data['shop_id'] ?? null);

            return $owner;
        });

        return response()->json($owner->load('shop:id,name,slug,owner_id'), 201);
    }

    public function show(User $shop_owner)
    {
        $this->ensureShopOwner($shop_owner);

        return response()->json($shop_owner->load('shop:id,name,slug,owner_id'));
    }

    public function update(Request $request, User $shop_owner)
    {
        $this->ensureShopOwner($shop_owner);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($shop_owner->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ]);

        DB::transaction(function () use ($request, $data, $shop_owner) {
            $shop_owner->name = $data['name'];
            $shop_owner->email = $data['email'];

            if (!empty($data['password'])) {
                $shop_owner->password = $data['password'];
            }

            $shop_owner->save();

            if ($request->has('shop_id')) {
                $this->assignShop($shop_owner, $data['shop_id'] ?? null);
            }
        });

        return response()->json($shop_owner->fresh()->load('shop:id,name,slug,owner_id'));
    }

    public function destroy(User $shop_owner)
    {
        $this->ensureShopOwner($shop_owner);

        $shop_owner->tokens()->delete();
        $shop_owner->delete();

        return response()->json(['message' => 'Shop owner deleted successfully.']);
    }

    private function ensureShopOwner(User $user): void
    {
        abort_unless($user->role === 'shop_owner', 404);
    }

    private function assignShop(User $owner, ?int $shopId): void
    {
        // Ei owner-er purano shop (jodi thake ar notun shop theke alada hoy) unassign koro
        Shop::where('owner_id', $owner->id)
            ->when($shopId, fn ($q) => $q->where('id', '!=', $shopId))
            ->update(['owner_id' => null]);

        if ($shopId) {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id && $shop->owner_id !== $owner->id) {
                throw ValidationException::withMessages([
                    'shop_id' => ['This shop already has an owner.'],
                ]);
            }

            $shop->update(['owner_id' => $owner->id]);
        }
    }
}