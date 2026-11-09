<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <h1>My Dashboard</h1>
        <p>Welcome, {{ auth.user.name }}.</p>
        <p v-if="auth.user.shop">Your shop: <strong>{{ auth.user.shop.name }}</strong></p>
        <div v-else class="alert alert-error">
            No shop assigned yet. Please contact the admin.
        </div>

        <div class="dash-grid">
            <router-link to="/owner/catalog" class="card">
                <h3 style="margin-top: 0">Catalog</h3>
                <span class="muted">Browse shared products and add to your shop</span>
            </router-link>
            <router-link to="/owner/products" class="card">
                <h3 style="margin-top: 0">My Products</h3>
                <span class="muted">Edit price, details, thumbnails</span>
            </router-link>
            <router-link to="/owner/create" class="card">
                <h3 style="margin-top: 0">Create Product</h3>
                <span class="muted">Add a new product to the catalog</span>
            </router-link>
        </div>
    </div>
</template>