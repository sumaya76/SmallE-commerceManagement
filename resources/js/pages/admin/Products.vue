<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';
import { parseError } from '../../utils';

const products = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const search = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');

const showForm = ref(false);
const editingId = ref(null);
const currentThumb = ref(null);
const file = ref(null);
const fileInput = ref(null);
const saving = ref(false);
const errors = ref({});
const formMessage = ref('');
const form = reactive({ name: '', description: '', base_price: '', remove_thumbnail: false });

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/products', { params: { page, search: search.value } });
        products.value = data.data;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch (e) {
        error.value = parseError(e).message;
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    form.name = '';
    form.description = '';
    form.base_price = '';
    form.remove_thumbnail = false;
    file.value = null;
    currentThumb.value = null;
    errors.value = {};
    formMessage.value = '';
}

function openCreate() {
    resetForm();
    editingId.value = null;
    showForm.value = true;
}

function openEdit(p) {
    resetForm();
    editingId.value = p.id;
    form.name = p.name;
    form.description = p.description || '';
    form.base_price = p.base_price;
    currentThumb.value = p.thumbnail_url;
    showForm.value = true;
}

function onFile(e) {
    file.value = e.target.files[0] || null;
}

async function save() {
    saving.value = true;
    errors.value = {};
    formMessage.value = '';

    // File upload-er jonno FormData; update-e POST + _method=PUT
    const fd = new FormData();
    fd.append('name', form.name);
    fd.append('description', form.description || '');
    fd.append('base_price', form.base_price);
    if (file.value) fd.append('thumbnail', file.value);
    if (editingId.value) {
        fd.append('_method', 'PUT');
        if (form.remove_thumbnail) fd.append('remove_thumbnail', '1');
    }

    try {
        const url = editingId.value ? `/admin/products/${editingId.value}` : '/admin/products';
        await api.post(url, fd);
        success.value = editingId.value ? 'Product updated.' : 'Product created.';
        const page = editingId.value ? meta.value.current_page : 1;
        showForm.value = false;
        load(page);
    } catch (e) {
        const p = parseError(e);
        errors.value = p.errors;
        formMessage.value = p.message;
    } finally {
        saving.value = false;
    }
}

async function remove(p) {
    const msg = p.listings_count > 0
        ? `"${p.name}" is listed in ${p.listings_count} shop(s). Deleting removes it from all of them. Continue?`
        : `Delete "${p.name}"?`;
    if (!confirm(msg)) return;

    try {
        await api.delete(`/admin/products/${p.id}`);
        success.value = 'Product deleted.';
        const page = products.value.length === 1 && meta.value.current_page > 1
            ? meta.value.current_page - 1
            : meta.value.current_page;
        load(page);
    } catch (e) {
        error.value = parseError(e).message;
    }
}

onMounted(() => load());
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Products (Shared Catalog)</h1>
            <button class="btn" @click="openCreate">+ New Product</button>
        </div>

        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search products..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Base price</th>
                        <th>Created by</th>
                        <th>In shops</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="6" class="center muted">Loading...</td>
                    </tr>
                    <tr v-else-if="!products.length">
                        <td colspan="6" class="center muted">No products found.</td>
                    </tr>
                    <tr v-for="p in products" :key="p.id">
                        <td>
                            <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="thumb" alt="" />
                            <div v-else class="thumb"></div>
                        </td>
                        <td>{{ p.name }}</td>
                        <td>{{ p.base_price }}</td>
                        <td>
                            {{ p.creator ? p.creator.name : '—' }}
                            <span v-if="p.creator" class="badge">{{ p.creator.role }}</span>
                        </td>
                        <td>
                            <span class="badge" :class="p.listings_count ? 'badge-green' : 'badge-red'">
                                {{ p.listings_count }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm" @click="openEdit(p)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="remove(p)">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" @change="load" />

        <Modal v-if="showForm" :title="editingId ? 'Edit Product' : 'New Product'" @close="showForm = false">
            <div v-if="formMessage" class="alert alert-error">{{ formMessage }}</div>

            <div class="form-group">
                <label>Name *</label>
                <input v-model="form.name" class="form-control" />
                <div v-if="errors.name" class="error-text">{{ errors.name[0] }}</div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                <div v-if="errors.description" class="error-text">{{ errors.description[0] }}</div>
            </div>

            <div class="form-group">
                <label>Base price *</label>
                <input v-model="form.base_price" type="number" step="0.01" min="0" class="form-control" />
                <div v-if="errors.base_price" class="error-text">{{ errors.base_price[0] }}</div>
            </div>

            <div class="form-group">
                <label>Thumbnail (jpg, png, webp, max 2MB)</label>
                <div v-if="currentThumb && !form.remove_thumbnail" style="margin-bottom: 8px">
                    <img :src="currentThumb" class="thumb" alt="" />
                </div>
                <input ref="fileInput" type="file" accept="image/*" class="form-control" @change="onFile" />
                <div v-if="errors.thumbnail" class="error-text">{{ errors.thumbnail[0] }}</div>
                <div v-if="editingId && currentThumb" class="checkbox-row" style="margin-top: 8px">
                    <input id="rm-thumb" v-model="form.remove_thumbnail" type="checkbox" />
                    <label for="rm-thumb" style="margin: 0">Remove current thumbnail</label>
                </div>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </Modal>
    </div>
</template>