<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { extractApiError } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const errorMessage = ref('');
const logoUrl = `${import.meta.env.BASE_URL}adagri-logo.png`;
const form = reactive({
    email: 'admin@agro.test',
    password: 'password',
});

async function submit(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        await authStore.login(form.email, form.password);
        await router.push({ name: 'dashboard' });
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="login-shell">
        <section class="login-panel panel-card">
            <div class="login-brand">
                <img :src="logoUrl" alt="Logo da Adagri" class="login-logo" />
                <p class="page-eyebrow">Acesso ao sistema</p>
                <h1>Gestão Agropecuária</h1>
                <p class="login-copy">
                    Ambiente interno para acompanhamento de produtores, fazendas, rebanhos e relatórios.
                </p>
            </div>

            <form class="login-form" @submit.prevent="submit">
                <h2>Entrar</h2>

                <label class="field">
                    <span>Email</span>
                    <input v-model="form.email" type="email" required />
                </label>

                <label class="field">
                    <span>Senha</span>
                    <input v-model="form.password" type="password" required />
                </label>

                <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>

                <button class="primary-button" :disabled="loading" type="submit">
                    {{ loading ? 'Entrando...' : 'Entrar' }}
                </button>

                <div class="credential-hint">
                    <span><strong>Administrador:</strong> admin@agro.test / password</span>
                    <span><strong>Usuário:</strong> viewer@agro.test / password</span>
                </div>
            </form>
        </section>
    </div>
</template>
