import { createRouter, createWebHistory } from 'vue-router';
import { auth, homeFor } from './auth';

import Home from './pages/public/Home.vue';
import Shop from './pages/public/Shop.vue';
import PublicProducts from './pages/public/Products.vue';
import Login from './pages/Login.vue';
import AdminDashboard from './pages/admin/Dashboard.vue';
import AdminShops from './pages/admin/Shops.vue';
import AdminProducts from './pages/admin/Products.vue';
import AdminShopOwners from './pages/admin/ShopOwners.vue';
import OwnerDashboard from './pages/owner/Dashboard.vue';
import OwnerCatalog from './pages/owner/Catalog.vue';
import OwnerProducts from './pages/owner/MyProducts.vue';
import OwnerCreateProduct from './pages/owner/CreateProduct.vue';
import NotFound from './pages/NotFound.vue';

const routes = [
    // ---------- Public (customer) ----------
    { path: '/', name: 'home', component: Home },
    { path: '/shops/:slug', name: 'shop', component: Shop },
    { path: '/products', name: 'products', component: PublicProducts },
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },

    // ---------- Admin ----------
    { path: '/admin', name: 'admin.dashboard', component: AdminDashboard, meta: { auth: true, role: 'admin' } },
    { path: '/admin/shops', name: 'admin.shops', component: AdminShops, meta: { auth: true, role: 'admin' } },
    { path: '/admin/products', name: 'admin.products', component: AdminProducts, meta: { auth: true, role: 'admin' } },
    { path: '/admin/shop-owners', name: 'admin.shop-owners', component: AdminShopOwners, meta: { auth: true, role: 'admin' } },

    // ---------- Shop owner ----------
    { path: '/owner', name: 'owner.dashboard', component: OwnerDashboard, meta: { auth: true, role: 'shop_owner' } },
    { path: '/owner/catalog', name: 'owner.catalog', component: OwnerCatalog, meta: { auth: true, role: 'shop_owner' } },
    { path: '/owner/products', name: 'owner.products', component: OwnerProducts, meta: { auth: true, role: 'shop_owner' } },
    { path: '/owner/create', name: 'owner.create', component: OwnerCreateProduct, meta: { auth: true, role: 'shop_owner' } },

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