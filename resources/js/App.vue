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
            <router-link to="/" class="brand">
                <img class="brand-logo" src="/images/mini-shop-logo.png" alt="Mini Shop" />
            </router-link>

            <div class="nav-links" role="group" aria-label="Main navigation">
                <template v-if="!auth.user">
                    <router-link to="/">Shops</router-link>
                    <router-link to="/products">All Products</router-link>
                </template>

                <template v-if="auth.user?.role === 'admin'">
                    <router-link to="/admin">Overview</router-link>
                    <router-link to="/admin/shops">Shops</router-link>
                    <router-link to="/admin/products">Products</router-link>
                    <router-link to="/admin/shop-owners">Shop Owners</router-link>
                </template>

                <template v-if="auth.user?.role === 'shop_owner'">
                    <router-link to="/owner">Overview</router-link>
                    <router-link to="/owner/catalog">Catalog</router-link>
                    <router-link to="/owner/products">My Products</router-link>
                    <router-link to="/owner/create">Add Product</router-link>
                </template>
            </div>

            <div class="nav-user">
                <template v-if="auth.user">
                    <span class="nav-user-name">{{ auth.user.name }}</span>
                    <button class="btn btn-secondary btn-sm" @click="handleLogout">Sign out</button>
                </template>
                <router-link v-else to="/login" class="btn btn-sm">Sign in</router-link>
            </div>
        </div>
    </nav>

    <main class="container">
        <router-view />
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-main">
                <div class="footer-about">
                    <router-link to="/" class="footer-brand">
                        <img class="footer-logo" src="/images/mini-shop-logo.png" alt="Mini Shop" />
                    </router-link>
                    <p class="footer-copy">Discover great products from shops you’ll love.</p>
                </div>
                <nav class="footer-links" aria-label="Footer navigation">
                    <span class="footer-heading">Explore</span>
                    <router-link to="/">Browse shops</router-link>
                    <router-link to="/products">All products</router-link>
                </nav>
            </div>
            <div class="footer-bottom">
                <p class="footer-legal">&copy; {{ new Date().getFullYear() }} Mini Shop. All rights reserved.</p>
                <p class="footer-credit">
                    <span>Designed &amp; developed by</span>
                    <strong>Sumaya Akter</strong>
                    <span class="footer-role">Full-Stack Software Engineer</span>
                </p>
            </div>
        </div>
    </footer>
</template>