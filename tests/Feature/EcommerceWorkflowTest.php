<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_owners_can_browse_shared_products_and_create_a_listed_product(): void
    {
        [$admin, $owner, $otherOwner, $shop, $otherShop] = $this->createAccountsAndShops();

        $catalogProduct = Product::create([
            'name' => 'Admin Product',
            'description' => 'Created by admin',
            'base_price' => 10,
            'created_by' => $admin->id,
        ]);

        Product::create([
            'name' => 'Other Owner Product',
            'description' => 'Created by another owner',
            'base_price' => 20,
            'created_by' => $otherOwner->id,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->getJson('/api/owner/catalog')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Admin Product'])
            ->assertJsonFragment(['name' => 'Other Owner Product']);

        $this->postJson('/api/owner/catalog', [
            'name' => 'Owner Product',
            'description' => 'Created by the shop owner',
            'base_price' => 30,
        ])->assertCreated()
            ->assertJsonPath('product.name', 'Owner Product')
            ->assertJsonPath('shop_id', $shop->id);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Owner Product'])
            ->assertJsonMissing(['name' => 'Admin Product']);

        $this->postJson('/api/owner/listings', [
            'product_id' => $catalogProduct->id,
            'price' => 12,
            'name' => 'Shop-specific Admin Product',
        ])->assertCreated();

        $this->getJson('/api/shops/'.$shop->slug.'/products')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Shop-specific Admin Product', 'price' => 12]);

        $this->getJson('/api/shops/'.$otherShop->slug.'/products')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Shop-specific Admin Product']);
    }

    public function test_customers_only_see_active_listings_in_active_shops(): void
    {
        [, , , $shop, $otherShop] = $this->createAccountsAndShops();

        $visibleProduct = $this->createProduct('Visible Product');
        $hiddenProduct = $this->createProduct('Hidden Listing');
        $inactiveShopProduct = $this->createProduct('Inactive Shop Listing');

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $visibleProduct->id,
            'price' => 15,
        ]);
        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $hiddenProduct->id,
            'price' => 16,
            'is_active' => false,
        ]);
        ShopProduct::create([
            'shop_id' => $otherShop->id,
            'product_id' => $inactiveShopProduct->id,
            'price' => 17,
        ]);
        $otherShop->update(['is_active' => false]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Visible Product'])
            ->assertJsonMissing(['name' => 'Hidden Listing'])
            ->assertJsonMissing(['name' => 'Inactive Shop Listing']);
    }

    public function test_shop_owners_cannot_change_another_shops_listing(): void
    {
        [, $owner, , , $otherShop] = $this->createAccountsAndShops();
        $product = $this->createProduct('Protected Product');
        $listing = ShopProduct::create([
            'shop_id' => $otherShop->id,
            'product_id' => $product->id,
            'price' => 10,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->putJson('/api/owner/listings/'.$listing->id, [
                'price' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(10.0, (float) $listing->fresh()->price);
    }

    public function test_paginated_shop_products_return_the_requested_page_and_range(): void
    {
        [, , , $shop] = $this->createAccountsAndShops();

        for ($index = 1; $index <= 13; $index++) {
            $product = $this->createProduct('Pagination Product '.$index);
            ShopProduct::create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'price' => $index,
            ]);
        }

        $this->getJson('/api/shops/'.$shop->slug.'/products?page=2&per_page=5')
            ->assertOk()
            ->assertJsonPath('products.current_page', 2)
            ->assertJsonPath('products.last_page', 3)
            ->assertJsonPath('products.from', 6)
            ->assertJsonPath('products.to', 10)
            ->assertJsonPath('products.total', 13)
            ->assertJsonFragment(['name' => 'Pagination Product 6'])
            ->assertJsonMissing(['name' => 'Pagination Product 1']);
    }

    private function createAccountsAndShops(): array
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'password',
            'role' => 'shop_owner',
        ]);
        $otherOwner = User::create([
            'name' => 'Other Owner',
            'email' => 'other-owner@example.test',
            'password' => 'password',
            'role' => 'shop_owner',
        ]);
        $shop = Shop::create([
            'name' => 'Owner Shop',
            'slug' => 'owner-shop',
            'owner_id' => $owner->id,
        ]);
        $otherShop = Shop::create([
            'name' => 'Other Shop',
            'slug' => 'other-shop',
            'owner_id' => $otherOwner->id,
        ]);

        return [$admin, $owner, $otherOwner, $shop, $otherShop];
    }

    private function createProduct(string $name): Product
    {
        return Product::create([
            'name' => $name,
            'base_price' => 10,
        ]);
    }
}
