<script setup lang="ts">
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const logoUrl = `${import.meta.env.BASE_URL}adagri-logo.png`;

const title = computed(() => (route.meta.title as string | undefined) ?? 'Visão Geral');
const roleLabel = computed(() => {
    if (authStore.user?.role === 'admin') {
        return 'Administrador';
    }

    if (authStore.user?.role === 'viewer') {
        return 'Usuário';
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
        <header class="site-utility-bar">
            <div class="site-utility-content">
                <span>Agência de Defesa Agropecuária do Estado do Ceará</span>
                <span>{{ authStore.user?.name }}</span>
            </div>
        </header>

        <div class="content-shell">
            <header class="site-header panel-card">
                <RouterLink to="/" class="brand-block">
                    <img :src="logoUrl" alt="Logo da Adagri" class="brand-logo" />
                    <div class="brand-copy">
                        <p class="page-eyebrow">Sistema Interno</p>
                        <h2>Gestão Agropecuária</h2>
                    </div>
                </RouterLink>

                <div class="topbar-actions">
                    <div class="user-summary">
                        <p class="page-eyebrow">Perfil ativo</p>
                        <strong>{{ roleLabel }}</strong>
                    </div>
                    <button class="ghost-button" @click="handleLogout">Sair</button>
                </div>
            </header>

            <nav class="site-nav panel-card">
                <RouterLink to="/">Relatorios</RouterLink>
                <RouterLink to="/rural-producers">Produtores</RouterLink>
                <RouterLink to="/farms">Fazendas</RouterLink>
                <RouterLink to="/herds">Rebanhos</RouterLink>
                <span class="badge">{{ title }}</span>
            </nav>

            <main class="page-content">
                <RouterView />
            </main>
        </div>
    </div>
</template>
