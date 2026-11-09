<script setup>
defineProps({
    meta: { type: Object, required: true },
});
defineEmits(['change']);
</script>

<template>
    <div v-if="meta.last_page > 1" class="pagination">
        <button class="btn btn-secondary btn-sm" :disabled="meta.current_page <= 1"
                @click="$emit('change', meta.current_page - 1)">Prev</button>
        <span class="muted">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <button class="btn btn-secondary btn-sm" :disabled="meta.current_page >= meta.last_page"
                @click="$emit('change', meta.current_page + 1)">Next</button>
    </div>
</template>