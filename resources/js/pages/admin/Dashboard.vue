<script setup>
import { auth } from '../../auth';
</script>

<template>
    <div>
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth.user.name }}.</p>
    </div>
</template>