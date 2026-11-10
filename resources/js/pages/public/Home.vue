<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Pagination from '../../components/Pagination.vue';
import { paginationMeta, parseError } from '../../utils';

const shops = ref([]);
const meta = ref({ current_page: 1, last_page: 1, from: null, to: null, total: 0, per_page: 10 });
const perPage = ref(10);
const search = ref('');
const loading = ref(false);
const error = ref('');

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/shops', { params: { page, per_page: perPage.value, search: search.value } });
        shops.value = data.data;
        meta.value = paginationMeta(data);
    } catch (e) {
        error.value = parseError(e).message;
    } finally {
        loading.value = false;
    }
}

function changePerPage(size) {
    perPage.value = size;
    load(1);
}

onMounted(() => load());
</script>

<template>
    <div>
        <div class="page-header hero-banner">
            <div>
                <h1>Find your next favorite</h1>
                <p class="page-intro">Explore our shops and discover products picked just for you.</p>
            </div>
            <router-link to="/products" class="btn btn-on-gradient">Browse all products</router-link>
        </div>

        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search shops..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <div v-if="loading" class="empty-state" role="status">
            <p>Loading shops...</p>
        </div>
        <div v-else-if="!shops.length && !error" class="empty-state">
            <h2>No shops found</h2>
            <p>Try a different search, or check back later.</p>
        </div>

        <div class="grid">
            <router-link v-for="s in shops" :key="s.id" :to="`/shops/${s.slug}`" class="card product-card"
                         style="color: inherit; text-decoration: none">
                <h3>{{ s.name }}</h3>
                <p class="desc">{{ s.description || 'No description' }}</p>
                <span class="badge">{{ s.products_count }} {{ s.products_count === 1 ? 'product' : 'products' }}</span>
            </router-link>
        </div>

        <Pagination :meta="meta" :loading="loading" @change="load" @per-page-change="changePerPage" />
    </div>
</template>