<script setup>
import { useRouter } from 'vue-router';
import { auth, logout } from './auth';

const router = useRouter();

async function handleLogout() {
    await logout();
    router.push('/login');
}
</script>

<template>
    <nav class="navbar">
        <div class="navbar-inner">
            <router-link to="/" class="brand">Mini Shop</router-link>

            <div class="nav-links">
                <router-link to="/">Home</router-link>

                <template v-if="auth.user?.role === 'admin'">
                    <router-link to="/admin">Dashboard</router-link>
                    <router-link to="/admin/shops">Shops</router-link>
                    <router-link to="/admin/products">Products</router-link>
                    <router-link to="/admin/shop-owners">Shop Owners</router-link>
                </template>

                <template v-if="auth.user?.role === 'shop_owner'">
                    <router-link to="/owner">Dashboard</router-link>
                    <router-link to="/owner/catalog">Catalog</router-link>
                    <router-link to="/owner/products">My Products</router-link>
                    <router-link to="/owner/create">Create Product</router-link>
                </template>
            </div>

            <div class="nav-user">
                <template v-if="auth.user">
                    <span>{{ auth.user.name }} ({{ auth.user.role }})</span>
                    <button class="btn btn-secondary btn-sm" @click="handleLogout">Logout</button>
                </template>
                <router-link v-else to="/login" class="btn btn-sm">Login</router-link>
            </div>
        </div>
    </nav>

    <main class="container">
        <router-view />
    </main>
</template>