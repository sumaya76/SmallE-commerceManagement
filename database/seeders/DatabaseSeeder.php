<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $owner1 = User::create([
            'name' => 'Owner One',
            'email' => 'owner1@gmail.com',
            'password' => 'password',
            'role' => 'shop_owner',
        ]);

        $owner2 = User::create([
            'name' => 'Owner Two',
            'email' => 'owner2@gmail.com',
            'password' => 'password',
            'role' => 'shop_owner',
        ]);

        $shop1 = Shop::create([
            'name' => 'Tech Hub',
            'slug' => 'tech-hub',
            'description' => 'Gadgets and electronics',
            'owner_id' => $owner1->id,
        ]);

        $shop2 = Shop::create([
            'name' => 'Fashion Corner',
            'slug' => 'fashion-corner',
            'description' => 'Clothing and accessories',
            'owner_id' => $owner2->id,
        ]);

        $items = [
            ['Wireless Mouse', 'Ergonomic wireless mouse', 25.00],
            ['Mechanical Keyboard', 'RGB mechanical keyboard', 80.00],
            ['USB-C Hub', '7-in-1 USB-C hub', 35.00],
            ['Bluetooth Speaker', 'Portable speaker', 45.00],
            ['Cotton T-Shirt', '100% cotton t-shirt', 15.00],
            ['Denim Jeans', 'Slim fit jeans', 40.00],
            ['Leather Wallet', 'Genuine leather wallet', 30.00],
            ['Sunglasses', 'UV protected sunglasses', 20.00],
            ['Backpack', 'Water resistant backpack', 50.00],
            ['Smart Watch', 'Fitness tracking watch', 99.00],
        ];

        $products = [];
        foreach ($items as $i => [$name, $desc, $price]) {
            $products[] = Product::create([
                'name' => $name,
                'description' => $desc,
                'base_price' => $price,
                'created_by' => $i < 5 ? $admin->id : $owner2->id,
            ]);
        }

        // Shop 1 (Tech Hub): products 0-3
        foreach ([0, 1, 2, 3] as $i) {
            ShopProduct::create([
                'shop_id' => $shop1->id,
                'product_id' => $products[$i]->id,
                'price' => $products[$i]->base_price + 5,
            ]);
        }

        // Shop 2 (Fashion Corner): products 4-6
        foreach ([4, 5, 6] as $i) {
            ShopProduct::create([
                'shop_id' => $shop2->id,
                'product_id' => $products[$i]->id,
                'price' => $products[$i]->base_price,
            ]);
        }

        // products 7, 8, 9 kono shop-e nai, customer dekhbe na
    }
}