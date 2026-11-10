<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <div class="page-header">
            <div>
                <h1>Admin overview</h1>
                <p class="page-intro">Welcome back, {{ auth.user.name }}. Manage your shops, products, and team.</p>
            </div>
        </div>

        <div class="dash-grid">
            <router-link to="/admin/shops" class="card dash-card">
                <h2>Shops</h2>
                <span class="muted">Create shops, update details, and assign owners.</span>
            </router-link>
            <router-link to="/admin/products" class="card dash-card">
                <h2>Products</h2>
                <span class="muted">Manage the shared product catalog.</span>
            </router-link>
            <router-link to="/admin/shop-owners" class="card dash-card">
                <h2>Shop owners</h2>
                <span class="muted">Create owner accounts and assign shops.</span>
            </router-link>
        </div>
    </div>
</template>