<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { extractApiError } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const errorMessage = ref('');
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
        <section class="login-panel">
            <div class="hero-copy">
                <p class="page-eyebrow">Teste Tecnico</p>
                <h1>Gestao agropecuaria com operacao organizada, relatorios e exportacoes.</h1>
                <p>
                    Gerencie produtores rurais, fazendas e rebanhos em um unico fluxo pensado
                    para equipes administrativas.
                </p>
                <div class="credential-hint">
                    <span><strong>Administrador:</strong> admin@agro.test / password</span>
                    <span><strong>Visualizador:</strong> viewer@agro.test / password</span>
                </div>
            </div>

            <form class="panel-card login-form" @submit.prevent="submit">
                <h2>Acessar plataforma</h2>

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
            </form>
        </section>
    </div>
</template>
