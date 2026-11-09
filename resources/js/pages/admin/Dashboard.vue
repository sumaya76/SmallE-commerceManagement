<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth.user.name }}.</p>

        <div class="dash-grid">
            <router-link to="/admin/shops" class="card">
                <h3 style="margin-top: 0">Shops</h3>
                <span class="muted">Manage shops</span>
            </router-link>
            <router-link to="/admin/products" class="card">
                <h3 style="margin-top: 0">Products</h3>
                <span class="muted">Manage shared catalog</span>
            </router-link>
            <router-link to="/admin/shop-owners" class="card">
                <h3 style="margin-top: 0">Shop Owners</h3>
                <span class="muted">Create and assign owners</span>
            </router-link>
        </div>
    </div>
</template>