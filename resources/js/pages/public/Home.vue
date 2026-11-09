<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Pagination from '../../components/Pagination.vue';
import { parseError } from '../../utils';

const shops = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const search = ref('');
const loading = ref(false);
const error = ref('');

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/shops', { params: { page, search: search.value } });
        shops.value = data.data;
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
            <h1>Shops</h1>
            <router-link to="/products" class="btn">Browse all products</router-link>
        </div>

        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search shops..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <p v-if="loading" class="muted">Loading...</p>
        <p v-else-if="!shops.length && !error" class="muted">No shops found.</p>

        <div class="grid">
            <router-link v-for="s in shops" :key="s.id" :to="`/shops/${s.slug}`" class="card product-card"
                         style="color: inherit; text-decoration: none">
                <h3>{{ s.name }}</h3>
                <p class="desc">{{ s.description || 'No description' }}</p>
                <div class="muted" style="font-size: 13px">{{ s.products_count }} product(s)</div>
            </router-link>
        </div>

        <Pagination :meta="meta" @change="load" />
    </div>
</template>