<script setup>
defineProps({ title: String });
defineEmits(['close']);
</script>

<template>
    <div class="modal-backdrop" @click.self="$emit('close')" @keydown.esc="$emit('close')">
        <div class="modal" role="dialog" aria-modal="true" :aria-label="title">
            <div class="modal-header">
                <h2>{{ title }}</h2>
                <button class="btn btn-secondary btn-sm" aria-label="Close dialog" @click="$emit('close')">✕</button>
            </div>
            <slot />
        </div>
    </div>
</template>