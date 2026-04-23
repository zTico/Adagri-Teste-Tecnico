<script setup lang="ts">
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const title = computed(() => (route.meta.title as string | undefined) ?? 'Visao Geral');
const roleLabel = computed(() => {
    if (authStore.user?.role === 'admin') {
        return 'Administrador';
    }

    if (authStore.user?.role === 'viewer') {
        return 'Visualizador';
    }

    return '';
});

async function handleLogout(): Promise<void> {
    await authStore.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-card">
                <p>Painel Operacional</p>
                <h2>Gestao Agropecuaria</h2>
                <span>{{ roleLabel }}</span>
            </div>

            <nav class="sidebar-nav">
                <RouterLink to="/">Relatorios</RouterLink>
                <RouterLink to="/rural-producers">Produtores</RouterLink>
                <RouterLink to="/farms">Fazendas</RouterLink>
                <RouterLink to="/herds">Rebanhos</RouterLink>
            </nav>
        </aside>

        <main class="content-shell">
            <header class="topbar">
                <div>
                    <p class="page-eyebrow">Sessao ativa</p>
                    <strong>{{ authStore.user?.name }}</strong>
                </div>

                <div class="topbar-actions">
                    <span class="badge">{{ title }}</span>
                    <button class="ghost-button" @click="handleLogout">Sair</button>
                </div>
            </header>

            <RouterView />
        </main>
    </div>
</template>
