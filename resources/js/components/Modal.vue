<script setup>
defineProps({ title: String });
defineEmits(['close']);
</script>

<template>
    <div class="modal-backdrop" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ title }}</h2>
                <button class="btn btn-secondary btn-sm" @click="$emit('close')">✕</button>
            </div>
            <slot />
        </div>
    </div>
</template>