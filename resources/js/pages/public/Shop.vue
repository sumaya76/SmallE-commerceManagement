<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api';
import Pagination from '../../components/Pagination.vue';
import { formatTaka, paginationMeta, parseError } from '../../utils';

const route = useRoute();

const shop = ref(null);
const products = ref([]);
const meta = ref({ current_page: 1, last_page: 1, from: null, to: null, total: 0, per_page: 10 });
const perPage = ref(10);
const search = ref('');
const loading = ref(false);
const notFound = ref(false);
const error = ref('');

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    notFound.value = false;
    try {
        const { data } = await api.get(`/shops/${route.params.slug}/products`, {
            params: { page, per_page: perPage.value, search: search.value },
        });
        shop.value = data.shop;
        products.value = data.products.data;
        meta.value = paginationMeta(data.products);
    } catch (e) {
        if (e.response?.status === 404) {
            notFound.value = true;
        } else {
            error.value = parseError(e).message;
        }
    } finally {
        loading.value = false;
    }
}

function changePerPage(size) {
    perPage.value = size;
    load(1);
}

// Ek shop theke onno shop-e gele abar load
watch(() => route.params.slug, () => {
    search.value = '';
    load();
});

onMounted(() => load());
</script>

<template>
    <div>
        <div v-if="notFound" class="center">
            <h1>Shop not found</h1>
            <router-link to="/">Back to shops</router-link>
        </div>

        <template v-else>
            <router-link to="/">&larr; All shops</router-link>

            <div class="page-header" style="margin-top: 12px">
                <div>
                    <h1>{{ shop ? shop.name : 'Loading...' }}</h1>
                    <p v-if="shop" class="muted" style="margin: 4px 0 0">{{ shop.description }}</p>
                </div>
            </div>

            <div v-if="error" class="alert alert-error">{{ error }}</div>

            <div class="toolbar">
                <input v-model="search" class="form-control" placeholder="Search products..." @keyup.enter="load(1)" />
                <button class="btn btn-secondary" @click="load(1)">Search</button>
            </div>

            <p v-if="loading" class="muted">Loading...</p>
            <p v-else-if="!products.length && !error" class="muted">No products available in this shop.</p>

            <div class="grid">
                <div v-for="p in products" :key="p.id" class="card product-card">
                    <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="thumb-lg" alt="" />
                    <div v-else class="thumb-lg"></div>

                    <h3>{{ p.name }}</h3>
                    <p class="desc">{{ p.description || 'No description' }}</p>
                    <div class="price">{{ formatTaka(p.price) }}</div>
                </div>
            </div>

            <Pagination :meta="meta" :loading="loading" @change="load" @per-page-change="changePerPage" />
        </template>
    </div>
</template>