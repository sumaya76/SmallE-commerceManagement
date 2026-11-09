<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';
import { parseError } from '../../utils';

const router = useRouter();

const form = reactive({ name: '', description: '', base_price: '' });
const file = ref(null);
const saving = ref(false);
const errors = ref({});
const message = ref('');

function onFile(e) {
    file.value = e.target.files[0] || null;
}

async function save() {
    saving.value = true;
    errors.value = {};
    message.value = '';

    const fd = new FormData();
    fd.append('name', form.name);
    fd.append('description', form.description || '');
    fd.append('base_price', form.base_price);
    if (file.value) fd.append('thumbnail', file.value);

    try {
        await api.post('/owner/catalog', fd);
        router.push('/owner/products');
    } catch (e) {
        const p = parseError(e);
        errors.value = p.errors;
        message.value = p.message;
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div style="max-width: 560px">
        <div class="page-header">
            <h1>Create Product</h1>
        </div>

        <div class="card">
            <p class="muted" style="margin-top: 0">
                Notun product shared catalog-e jabe (onno owner-ra dekhte pabe) ar automatically tomar shop-eo add hobe.
            </p>

            <div v-if="message" class="alert alert-error">{{ message }}</div>

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
                <label>Price *</label>
                <input v-model="form.base_price" type="number" step="0.01" min="0" class="form-control" />
                <div v-if="errors.base_price" class="error-text">{{ errors.base_price[0] }}</div>
            </div>

            <div class="form-group">
                <label>Thumbnail (jpg/png/webp, max 2MB)</label>
                <input type="file" accept="image/*" class="form-control" @change="onFile" />
                <div v-if="errors.thumbnail" class="error-text">{{ errors.thumbnail[0] }}</div>
            </div>

            <button class="btn" :disabled="saving" @click="save">
                {{ saving ? 'Saving...' : 'Create & add to my shop' }}
            </button>
        </div>
    </div>
</template>