<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Pagination from '../../components/Pagination.vue';
import { formatTaka, parseError } from '../../utils';

const products = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const search = ref('');
const loading = ref(false);
const error = ref('');

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/products', { params: { page, search: search.value } });
        products.value = data.data;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch (e) {
        error.value = parseError(e).message;
    } finally {
        loading.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div>
        <div class="page-header">
            <h1>All Products</h1>
        </div>

        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search products..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <p v-if="loading" class="muted">Loading...</p>
        <p v-else-if="!products.length && !error" class="muted">No products found.</p>

        <div class="grid">
            <div v-for="p in products" :key="p.id" class="card product-card">
                <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="thumb-lg" alt="" />
                <div v-else class="thumb-lg"></div>

                <h3>{{ p.name }}</h3>
                <p class="desc">{{ p.description || 'No description' }}</p>
                <div class="price">{{ formatTaka(p.price) }}</div>
                <div style="font-size: 13px">
                    Sold by <router-link :to="`/shops/${p.shop.slug}`">{{ p.shop.name }}</router-link>
                </div>
            </div>
        </div>

        <Pagination :meta="meta" @change="load" />
    </div>
</template>