<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <header class="page-header">
        <div>
            <p class="page-eyebrow">Sistema de Gestao Agropecuaria</p>
            <h1>{{ title }}</h1>
            <p v-if="description" class="page-description">{{ description }}</p>
        </div>

        <div class="page-header-actions">
            <slot />
        </div>
    </header>
</template>
