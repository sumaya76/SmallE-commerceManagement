<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';
import { paginationMeta, parseError } from '../../utils';

const shops = ref([]);
const meta = ref({ current_page: 1, last_page: 1, from: null, to: null, total: 0, per_page: 10 });
const perPage = ref(10);
const owners = ref([]);
const search = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');

const showForm = ref(false);
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});
const formMessage = ref('');
const form = reactive({ name: '', description: '', owner_id: '', is_active: true });

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/shops', { params: { page, per_page: perPage.value, search: search.value } });
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

async function loadOwners() {
    const { data } = await api.get('/admin/shop-owners', { params: { per_page: 100 } });
    owners.value = data.data;
}

function resetForm() {
    form.name = '';
    form.description = '';
    form.owner_id = '';
    form.is_active = true;
    errors.value = {};
    formMessage.value = '';
}

function openCreate() {
    resetForm();
    editingId.value = null;
    showForm.value = true;
    loadOwners();
}

function openEdit(shop) {
    resetForm();
    editingId.value = shop.id;
    form.name = shop.name;
    form.description = shop.description || '';
    form.owner_id = shop.owner_id || '';
    form.is_active = shop.is_active;
    showForm.value = true;
    loadOwners();
}

async function save() {
    saving.value = true;
    errors.value = {};
    formMessage.value = '';

    try {
        if (editingId.value) {
            await api.put(`/admin/shops/${editingId.value}`, form);
            success.value = 'Shop updated.';
        } else {
            await api.post('/admin/shops', form);
            success.value = 'Shop created.';
        }
        showForm.value = false;
        load(editingId.value ? meta.value.current_page : 1);
    } catch (e) {
        const p = parseError(e);
        errors.value = p.errors;
        formMessage.value = p.message;
    } finally {
        saving.value = false;
    }
}

async function remove(shop) {
    if (!confirm(`Delete "${shop.name}"? Its product listings will also be removed.`)) return;

    try {
        await api.delete(`/admin/shops/${shop.id}`);
        success.value = 'Shop deleted.';
        // Last page-er last item delete hole ager page-e jao
        const page = shops.value.length === 1 && meta.value.current_page > 1
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
            <h1>Shops</h1>
            <button class="btn" @click="openCreate">+ New Shop</button>
        </div>

        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search shops..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Owner</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="6" class="center muted">Loading...</td>
                    </tr>
                    <tr v-else-if="!shops.length">
                        <td colspan="6" class="center muted">No shops found.</td>
                    </tr>
                    <tr v-for="shop in shops" :key="shop.id">
                        <td>{{ shop.name }}</td>
                        <td class="muted">{{ shop.slug }}</td>
                        <td>{{ shop.owner ? shop.owner.name : '—' }}</td>
                        <td>{{ shop.listings_count }}</td>
                        <td>
                            <span class="badge" :class="shop.is_active ? 'badge-green' : 'badge-red'">
                                {{ shop.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm" @click="openEdit(shop)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="remove(shop)">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" :loading="loading" @change="load" @per-page-change="changePerPage" />

        <Modal v-if="showForm" :title="editingId ? 'Edit Shop' : 'New Shop'" @close="showForm = false">
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
                <label>Owner</label>
                <select v-model="form.owner_id" class="form-control">
                    <option value="">— No owner —</option>
                    <option v-for="o in owners" :key="o.id" :value="o.id">
                        {{ o.name }} ({{ o.email }})
                    </option>
                </select>
                <div v-if="errors.owner_id" class="error-text">{{ errors.owner_id[0] }}</div>
            </div>

            <div class="form-group checkbox-row">
                <input id="shop-active" v-model="form.is_active" type="checkbox" />
                <label for="shop-active" style="margin: 0">Active (visible to customers)</label>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </Modal>
    </div>
</template>