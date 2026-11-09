<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { login, homeFor } from '../auth';

const router = useRouter();
const route = useRoute();

const form = reactive({ email: '', password: '' });
const errors = ref({});
const message = ref('');
const loading = ref(false);

async function submit() {
    loading.value = true;
    errors.value = {};
    message.value = '';

    try {
        const user = await login(form.email, form.password);
        router.push(route.query.redirect || homeFor(user));
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
            message.value = e.response.data.message || 'Validation failed.';
        } else {
            message.value = 'Something went wrong. Please try again.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div style="max-width: 400px; margin: 40px auto">
        <div class="card">
            <h1 style="margin-top: 0">Login</h1>

            <div v-if="message" class="alert alert-error">{{ message }}</div>

            <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" type="email" class="form-control" @keyup.enter="submit" />
                <div v-if="errors.email" class="error-text">{{ errors.email[0] }}</div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input v-model="form.password" type="password" class="form-control" @keyup.enter="submit" />
                <div v-if="errors.password" class="error-text">{{ errors.password[0] }}</div>
            </div>

            <button class="btn" style="width: 100%" :disabled="loading" @click="submit">
                {{ loading ? 'Logging in...' : 'Login' }}
            </button>

            <p class="muted" style="font-size: 13px">
                Demo: admin@gmail.com / password<br />
                owner1@gmail.com / password
            </p>
        </div>
    </div>
</template>