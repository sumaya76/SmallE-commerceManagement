<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <h1>My Dashboard</h1>
        <p>Welcome, {{ auth.user.name }}.</p>
        <p v-if="auth.user.shop">Your shop: <strong>{{ auth.user.shop.name }}</strong></p>
        <p v-else class="muted">No shop assigned yet. Please contact the admin.</p>
    </div>
</template>