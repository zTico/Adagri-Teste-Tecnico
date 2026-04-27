<script setup lang="ts">
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const logoUrl = `${import.meta.env.BASE_URL}adagri-logo.png`;

const title = computed(() => (route.meta.title as string | undefined) ?? 'Visão Geral');
const userInitials = computed(() => {
    const name = authStore.user?.name ?? '';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('') || 'US';
});
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
                <RouterLink to="/profile">{{ authStore.user?.name }}</RouterLink>
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
                    <RouterLink to="/profile" class="profile-chip">
                        <span class="profile-chip-avatar">
                            <img
                                v-if="authStore.user?.profile_photo_url"
                                :src="authStore.user.profile_photo_url"
                                alt="Foto de perfil"
                            />
                            <span v-else>{{ userInitials }}</span>
                        </span>
                        <span>
                            <strong>{{ authStore.user?.name }}</strong>
                            <small>{{ roleLabel }}</small>
                        </span>
                    </RouterLink>
                    <button class="ghost-button" @click="handleLogout">Sair</button>
                </div>
            </header>

            <nav class="site-nav panel-card">
                <RouterLink to="/">Relatórios</RouterLink>
                <RouterLink to="/rural-producers">Produtores</RouterLink>
                <RouterLink to="/farms">Fazendas</RouterLink>
                <RouterLink to="/herds">Rebanhos</RouterLink>
                <RouterLink to="/profile">Meu Perfil</RouterLink>
                <span class="badge">{{ title }}</span>
            </nav>

            <main class="page-content">
                <RouterView />
            </main>
        </div>
    </div>
</template>
