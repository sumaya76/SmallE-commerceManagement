import { createRouter, createWebHistory } from 'vue-router';
import { auth, homeFor } from './auth';

import Home from './pages/public/Home.vue';
import Login from './pages/Login.vue';
import AdminDashboard from './pages/admin/Dashboard.vue';
import OwnerDashboard from './pages/owner/Dashboard.vue';
import AdminShops from './pages/admin/Shops.vue';
import AdminProducts from './pages/admin/Products.vue';
import AdminShopOwners from './pages/admin/ShopOwners.vue';
import NotFound from './pages/NotFound.vue';

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },

    { path: '/admin', name: 'admin.dashboard', component: AdminDashboard, meta: { auth: true, role: 'admin' } },
    { path: '/admin/shops', name: 'admin.shops', component: AdminShops, meta: { auth: true, role: 'admin' } },
    { path: '/admin/products', name: 'admin.products', component: AdminProducts, meta: { auth: true, role: 'admin' } },
    { path: '/admin/shop-owners', name: 'admin.shop-owners', component: AdminShopOwners, meta: { auth: true, role: 'admin' } },

    { path: '/owner', name: 'owner.dashboard', component: OwnerDashboard, meta: { auth: true, role: 'shop_owner' } },
    // Phase 6C: owner catalog/listings route ekhane ashbe

    // Phase 6D: public shop page route ekhane ashbe

    // Shobar shesh-e thakbe
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    if (to.meta.guest && auth.token) {
        return homeFor(auth.user);
    }

    if (to.meta.auth && !auth.token) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.role && auth.user?.role !== to.meta.role) {
        return homeFor(auth.user);
    }
});

export default router;