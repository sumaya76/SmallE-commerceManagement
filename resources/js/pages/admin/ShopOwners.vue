<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';
import { paginationMeta, parseError } from '../../utils';

const owners = ref([]);
const meta = ref({ current_page: 1, last_page: 1, from: null, to: null, total: 0, per_page: 10 });
const perPage = ref(10);
const shops = ref([]);
const search = ref('');
const loading = ref(false);
const success = ref('');
const error = ref('');

const showForm = ref(false);
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});
const formMessage = ref('');
const form = reactive({ name: '', email: '', password: '', shop_id: '' });

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/shop-owners', { params: { page, per_page: perPage.value, search: search.value } });
        owners.value = data.data;
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

async function loadShops() {
    const { data } = await api.get('/admin/shops', { params: { per_page: 100 } });
    shops.value = data.data;
}

function resetForm() {
    form.name = '';
    form.email = '';
    form.password = '';
    form.shop_id = '';
    errors.value = {};
    formMessage.value = '';
}

function openCreate() {
    resetForm();
    editingId.value = null;
    showForm.value = true;
    loadShops();
}

function openEdit(o) {
    resetForm();
    editingId.value = o.id;
    form.name = o.name;
    form.email = o.email;
    form.shop_id = o.shop ? o.shop.id : '';
    showForm.value = true;
    loadShops();
}

// Onno owner-er shop hole dropdown-e disable dekhabe
function shopTaken(shop) {
    return shop.owner_id && shop.owner_id !== editingId.value;
}

async function save() {
    saving.value = true;
    errors.value = {};
    formMessage.value = '';

    const payload = {
        name: form.name,
        email: form.email,
        shop_id: form.shop_id || null,
    };
    if (form.password) payload.password = form.password;

    try {
        if (editingId.value) {
            await api.put(`/admin/shop-owners/${editingId.value}`, payload);
            success.value = 'Shop owner updated.';
        } else {
            await api.post('/admin/shop-owners', payload);
            success.value = 'Shop owner created.';
        }
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

async function remove(o) {
    if (!confirm(`Delete owner "${o.name}"? Their shop will become unassigned.`)) return;

    try {
        await api.delete(`/admin/shop-owners/${o.id}`);
        success.value = 'Shop owner deleted.';
        const page = owners.value.length === 1 && meta.value.current_page > 1
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
            <h1>Shop Owners</h1>
            <button class="btn" @click="openCreate">+ New Shop Owner</button>
        </div>

        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="toolbar">
            <input v-model="search" class="form-control" placeholder="Search by name or email..." @keyup.enter="load(1)" />
            <button class="btn btn-secondary" @click="load(1)">Search</button>
        </div>

        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Shop</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="4" class="center muted">Loading...</td>
                    </tr>
                    <tr v-else-if="!owners.length">
                        <td colspan="4" class="center muted">No shop owners found.</td>
                    </tr>
                    <tr v-for="o in owners" :key="o.id">
                        <td>{{ o.name }}</td>
                        <td>{{ o.email }}</td>
                        <td>{{ o.shop ? o.shop.name : '—' }}</td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm" @click="openEdit(o)">Edit</button>
                                <button class="btn btn-danger btn-sm" @click="remove(o)">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="meta" :loading="loading" @change="load" @per-page-change="changePerPage" />

        <Modal v-if="showForm" :title="editingId ? 'Edit Shop Owner' : 'New Shop Owner'" @close="showForm = false">
            <div v-if="formMessage" class="alert alert-error">{{ formMessage }}</div>

            <div class="form-group">
                <label>Name *</label>
                <input v-model="form.name" class="form-control" />
                <div v-if="errors.name" class="error-text">{{ errors.name[0] }}</div>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input v-model="form.email" type="email" class="form-control" />
                <div v-if="errors.email" class="error-text">{{ errors.email[0] }}</div>
            </div>

            <div class="form-group">
                <label>Password {{ editingId ? '(leave blank to keep the current password)' : '*' }}</label>
                <input v-model="form.password" type="password" class="form-control" />
                <div v-if="errors.password" class="error-text">{{ errors.password[0] }}</div>
            </div>

            <div class="form-group">
                <label>Assign shop</label>
                <select v-model="form.shop_id" class="form-control">
                    <option value="">— No shop —</option>
                    <option v-for="s in shops" :key="s.id" :value="s.id" :disabled="shopTaken(s)">
                        {{ s.name }}{{ shopTaken(s) ? ' (already has owner)' : '' }}
                    </option>
                </select>
                <div v-if="errors.shop_id" class="error-text">{{ errors.shop_id[0] }}</div>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </Modal>
    </div>
</template>