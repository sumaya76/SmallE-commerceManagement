<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';
import { formatTaka, parseError } from '../../utils';

const listings = ref([]);
const meta = ref({ current_page: 1, last_page: 1 });
const search = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');

const showForm = ref(false);
const editing = ref(null);
const currentThumb = ref(null);
const file = ref(null);
const saving = ref(false);
const errors = ref({});
const formMessage = ref('');
const form = reactive({ price: '', name: '', description: '', is_active: true, remove_thumbnail: false });

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/owner/listings', { params: { page, search: search.value } });
        listings.value = data.data;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch (e) {
        error.value = parseError(e).message;
    } finally {
        loading.value = false;
    }
}

function openEdit(l) {
    editing.value = l;
    form.price = l.price;
    form.name = l.name || '';
    form.description = l.description || '';
    form.is_active = l.is_active;
    form.remove_thumbnail = false;
    currentThumb.value = l.thumbnail_url;
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
    fd.append('_method', 'PUT');
    fd.append('price', form.price);
    fd.append('name', form.name || '');
    fd.append('description', form.description || '');
    fd.append('is_active', form.is_active ? '1' : '0');
    if (file.value) fd.append('thumbnail', file.value);
    if (form.remove_thumbnail) fd.append('remove_thumbnail', '1');

    try {
        await api.post(`/owner/listings/${editing.value.id}`, fd);
        success.value = 'Product updated.';
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

async function remove(l) {
    if (!confirm(`Remove "${l.display_name}" from your shop? It will stay in the shared catalog.`)) return;

    try {
        await api.delete(`/owner/listings/${l.id}`);
        success.value = 'Product removed from your shop.';
        const page = listings.value.length === 1 && meta.value.current_page > 1
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
            <h1>My Products</h1>
            <router-link to="/owner/catalog" class="btn">Browse Catalog</router-link>
        </div>

        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search my products..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="5" class="center muted">Loading...</td>
                    </tr>
                    <tr v-else-if="!listings.length">
                        <td colspan="5" class="center muted">
                            No products in your shop yet. Add some from the catalog.
                        </td>
                    </tr>
                    <tr v-for="l in listings" :key="l.id">
                        <td>
                            <img v-if="l.display_thumbnail_url" :src="l.display_thumbnail_url" class="thumb" alt="" />
                            <div v-else class="thumb"></div>
                        </td>
                        <td>
                            {{ l.display_name }}
                            <div v-if="l.name" class="muted" style="font-size: 12px">
                                Catalog: {{ l.product?.name }}
                            </div>
                        </td>
                        <td>{{ formatTaka(l.price) }}</td>
                        <td>
                            <span class="badge" :class="l.is_active ? 'badge-green' : 'badge-red'">
                                {{ l.is_active ? 'Visible' : 'Hidden' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm" @click="openEdit(l)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="remove(l)">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" @change="load" />

        <Modal v-if="showForm" :title="`Edit: ${editing.display_name}`" @close="showForm = false">
            <div v-if="formMessage" class="alert alert-error">{{ formMessage }}</div>

            <div class="form-group">
                <label>Price (৳) *</label>
                <input v-model="form.price" type="number" step="0.01" min="0" class="form-control" />
                <div v-if="errors.price" class="error-text">{{ errors.price[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom name (khali rakhle catalog-er name)</label>
                <input v-model="form.name" class="form-control" :placeholder="editing.product?.name" />
                <div v-if="errors.name" class="error-text">{{ errors.name[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom description (khali rakhle catalog-er description)</label>
                <textarea v-model="form.description" class="form-control" rows="3"
                          :placeholder="editing.product?.description || ''"></textarea>
                <div v-if="errors.description" class="error-text">{{ errors.description[0] }}</div>
            </div>

            <div class="form-group">
                <label>Custom thumbnail (jpg/png/webp, max 2MB)</label>
                <div v-if="currentThumb && !form.remove_thumbnail" style="margin-bottom: 8px">
                    <img :src="currentThumb" class="thumb" alt="" />
                </div>
                <input type="file" accept="image/*" class="form-control" @change="onFile" />
                <div v-if="errors.thumbnail" class="error-text">{{ errors.thumbnail[0] }}</div>
                <div v-if="currentThumb" class="checkbox-row" style="margin-top: 8px">
                    <input id="rm-l-thumb" v-model="form.remove_thumbnail" type="checkbox" />
                    <label for="rm-l-thumb" style="margin: 0">Remove my thumbnail (catalog-er ta use hobe)</label>
                </div>
            </div>

            <div class="form-group checkbox-row">
                <input id="edit-active" v-model="form.is_active" type="checkbox" />
                <label for="edit-active" style="margin: 0">Visible to customers</label>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </Modal>
    </div>
</template>