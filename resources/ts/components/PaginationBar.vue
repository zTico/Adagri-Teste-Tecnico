<script setup lang="ts">
const props = defineProps<{
    currentPage: number;
    lastPage: number;
}>();

const emit = defineEmits<{
    (event: 'change', page: number): void;
}>();

function goTo(page: number): void {
    if (page < 1 || page > props.lastPage || page === props.currentPage) {
        return;
    }

    emit('change', page);
}
</script>

<template>
    <nav v-if="lastPage > 1" class="pagination-bar">
        <button class="ghost-button" @click="goTo(currentPage - 1)">Anterior</button>
        <span>Pagina {{ currentPage }} de {{ lastPage }}</span>
        <button class="ghost-button" @click="goTo(currentPage + 1)">Proxima</button>
    </nav>
</template>
