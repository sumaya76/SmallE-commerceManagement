<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';
import { formatTaka, parseError } from '../../utils';

const products = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const search = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');

const showForm = ref(false);
const selected = ref(null);
const file = ref(null);
const saving = ref(false);
const errors = ref({});
const formMessage = ref('');
const form = reactive({ price: '', name: '', description: '', is_active: true });

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/owner/catalog', { params: { page, search: search.value } });
        products.value = data.data;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch (e) {
        error.value = parseError(e).message;
    } finally {
        loading.value = false;
    }
}

function openAdd(p) {
    selected.value = p;
    form.price = p.base_price;
    form.name = '';
    form.description = '';
    form.is_active = true;
    file.value = null;
    errors.value = {};
    formMessage.value = '';
    showForm.value = true;
}

function onFile(e) {
    file.value = e.target.files[0] || null;
}

async function save() {
    saving.value = true;
    errors.value = {};
    formMessage.value = '';

    const fd = new FormData();
    fd.append('product_id', selected.value.id);
    fd.append('price', form.price);
    fd.append('name', form.name || '');
    fd.append('description', form.description || '');
    fd.append('is_active', form.is_active ? '1' : '0');
    if (file.value) fd.append('thumbnail', file.value);

    try {
        await api.post('/owner/listings', fd);
        success.value = `"${selected.value.name}" added to your shop.`;
        showForm.value = false;
        load(meta.value.current_page);
    } catch (e) {
        const p = parseError(e);
        errors.value = p.errors;
        formMessage.value = p.message;
    } finally {
        saving.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Shared Catalog</h1>
            <router-link to="/owner/create" class="btn">+ Create Product</router-link>
        </div>

        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search catalog..." @keyup.enter="load(1)" />
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
                <div class="price">Base price: {{ formatTaka(p.base_price) }}</div>
                <div class="muted" style="font-size: 12px">
                    By {{ p.creator ? p.creator.name : 'Unknown' }}
                </div>

                <div class="card-actions">
                    <span v-if="p.in_my_shop" class="badge badge-green">In your shop</span>
                    <button v-else class="btn btn-sm" @click="openAdd(p)">Add to my shop</button>
                </div>
            </div>
        </div>

        <Pagination :meta="meta" @change="load" />

        <Modal v-if="showForm" :title="`Add: ${selected.name}`" @close="showForm = false">
            <div v-if="formMessage" class="alert alert-error">{{ formMessage }}</div>
            <div v-if="errors.product_id" class="alert alert-error">{{ errors.product_id[0] }}</div>

            <div class="form-group">
                <label>Your price (৳) *</label>
                <input v-model="form.price" type="number" step="0.01" min="0" class="form-control" />
                <div v-if="errors.price" class="error-text">{{ errors.price[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom name (optional)</label>
                <input v-model="form.name" class="form-control" :placeholder="selected.name" />
                <div v-if="errors.name" class="error-text">{{ errors.name[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom description (optional)</label>
                <textarea v-model="form.description" class="form-control" rows="3"
                          :placeholder="selected.description || ''"></textarea>
                <div v-if="errors.description" class="error-text">{{ errors.description[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom thumbnail (optional, jpg/png/webp, max 2MB)</label>
                <input type="file" accept="image/*" class="form-control" @change="onFile" />
                <div v-if="errors.thumbnail" class="error-text">{{ errors.thumbnail[0] }}</div>
            </div>

            <div class="form-group checkbox-row">
                <input id="add-active" v-model="form.is_active" type="checkbox" />
                <label for="add-active" style="margin: 0">Visible to customers</label>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Adding...' : 'Add to my shop' }}
            </button>
        </Modal>
    </div>
</template>