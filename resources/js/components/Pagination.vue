<script setup>
import { computed } from 'vue';

const props = defineProps({
    meta: { type: Object, required: true },
    loading: { type: Boolean, default: false },
});
defineEmits(['change', 'per-page-change']);

const pages = computed(() => {
    const current = Number(props.meta.current_page) || 1;
    const last = Number(props.meta.last_page) || 1;
    const start = Math.max(1, Math.min(current - 2, last - 4));
    const end = Math.min(last, start + 4);

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
});
</script>

<template>
    <div v-if="meta.total > 0" class="pagination-wrap">
        <div class="pagination-info">
            <p class="pagination-summary muted" aria-live="polite">
                Showing <strong>{{ meta.from }}</strong>–<strong>{{ meta.to }}</strong> of
                <strong>{{ meta.total }}</strong> results
            </p>
            <label class="pagination-size">
                Per page
                <select class="form-control" :value="meta.per_page" :disabled="loading"
                        aria-label="Results per page"
                        @change="$emit('per-page-change', Number($event.target.value))">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="20">20</option>
                    <option :value="50">50</option>
                </select>
            </label>
        </div>
        <nav v-if="meta.last_page > 1" class="pagination" aria-label="Pagination">
            <button class="btn btn-secondary btn-sm" aria-label="Go to first page"
                    :disabled="loading || meta.current_page <= 1" @click="$emit('change', 1)">First</button>
            <button class="btn btn-secondary btn-sm" aria-label="Go to previous page"
                    :disabled="loading || meta.current_page <= 1" @click="$emit('change', meta.current_page - 1)">Previous</button>
            <button v-for="page in pages" :key="page" class="btn btn-secondary btn-sm pagination-page"
                    :class="{ 'pagination-page-active': page === meta.current_page }"
                    :aria-label="`Go to page ${page}`" :aria-current="page === meta.current_page ? 'page' : null"
                    :disabled="loading || page === meta.current_page" @click="$emit('change', page)">
                {{ page }}
            </button>
            <button class="btn btn-secondary btn-sm" aria-label="Go to next page"
                    :disabled="loading || meta.current_page >= meta.last_page"
                    @click="$emit('change', meta.current_page + 1)">Next</button>
            <button class="btn btn-secondary btn-sm" aria-label="Go to last page"
                    :disabled="loading || meta.current_page >= meta.last_page"
                    @click="$emit('change', meta.last_page)">Last</button>
        </nav>
    </div>
</template>