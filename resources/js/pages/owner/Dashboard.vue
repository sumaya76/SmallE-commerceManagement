<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <div class="page-header">
            <div>
                <h1>Shop overview</h1>
                <p class="page-intro">
                    Welcome back, {{ auth.user.name }}<span v-if="auth.user.shop">. You’re managing <strong>{{ auth.user.shop.name }}</strong></span>.
                </p>
            </div>
        </div>
        <div v-if="!auth.user.shop" class="alert alert-error">
            No shop assigned yet. Please contact the admin.
        </div>

        <div class="dash-grid">
            <router-link to="/owner/catalog" class="card dash-card">
                <h2>Browse catalog</h2>
                <span class="muted">Find shared products and add them to your shop.</span>
            </router-link>
            <router-link to="/owner/products" class="card dash-card">
                <h2>My products</h2>
                <span class="muted">Update prices, product details, and images.</span>
            </router-link>
            <router-link to="/owner/create" class="card dash-card">
                <h2>Add a product</h2>
                <span class="muted">Create a new product for the shared catalog.</span>
            </router-link>
        </div>
    </div>
</template>