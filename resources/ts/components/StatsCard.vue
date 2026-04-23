<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    tone?: 'earth' | 'gold' | 'forest';
}>();
</script>

<template>
    <article class="stat-card" :data-tone="tone ?? 'earth'">
        <p>{{ label }}</p>
        <strong>{{ value }}</strong>
    </article>
</template>
