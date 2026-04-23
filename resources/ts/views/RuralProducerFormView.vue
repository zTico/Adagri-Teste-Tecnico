<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import { api, extractApiError } from '@/lib/api';
import type { ApiValidationError, RuralProducer } from '@/types';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => Boolean(route.params.id));
const loading = ref(false);
const lookupLoading = ref(false);
const errorMessage = ref('');
const formErrors = ref<Record<string, string[]>>({});
const form = reactive({
    name: '',
    cpf_cnpj: '',
    phone: '',
    email: '',
    postal_code: '',
    street: '',
    number: '',
    complement: '',
    district: '',
    city: '',
    state: '',
});

async function fetchProducer(): Promise<void> {
    if (!isEditing.value) {
        return;
    }

    loading.value = true;

    try {
        const { data } = await api.get<RuralProducer>(`/rural-producers/${route.params.id}`);
        form.name = data.name;
        form.cpf_cnpj = data.cpf_cnpj;
        form.phone = data.phone ?? '';
        form.email = data.email ?? '';
        form.postal_code = data.address.postal_code;
        form.street = data.address.street;
        form.number = data.address.number;
        form.complement = data.address.complement ?? '';
        form.district = data.address.district ?? '';
        form.city = data.address.city;
        form.state = data.address.state;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function lookupPostalCode(): Promise<void> {
    if (!form.postal_code) {
        return;
    }

    lookupLoading.value = true;

    try {
        const { data } = await api.get(`/lookups/postal-code/${form.postal_code}`);
        form.street = data.street ?? form.street;
        form.complement = data.complement ?? form.complement;
        form.district = data.district ?? form.district;
        form.city = data.city ?? form.city;
        form.state = data.state ?? form.state;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        lookupLoading.value = false;
    }
}

async function submit(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    formErrors.value = {};

    try {
        if (isEditing.value) {
            await api.put(`/rural-producers/${route.params.id}`, form);
        } else {
            await api.post('/rural-producers', form);
        }

        await router.push({ name: 'rural-producers' });
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        formErrors.value = payload.errors ?? {};
    } finally {
        loading.value = false;
    }
}

onMounted(fetchProducer);
</script>

<template>
    <section class="page-section">
        <PageHeader
            :title="isEditing ? 'Editar Produtor Rural' : 'Cadastrar Produtor Rural'"
            description="Informe documento, contato e endereco estruturado."
        />

        <form class="panel-card form-grid-2" @submit.prevent="submit">
            <label class="field">
                <span>Nome</span>
                <input v-model="form.name" required />
                <small>{{ formErrors.name?.[0] }}</small>
            </label>
            <label class="field">
                <span>CPF / CNPJ</span>
                <input v-model="form.cpf_cnpj" required />
                <small>{{ formErrors.cpf_cnpj?.[0] }}</small>
            </label>
            <label class="field">
                <span>Telefone</span>
                <input v-model="form.phone" />
                <small>{{ formErrors.phone?.[0] }}</small>
            </label>
            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" />
                <small>{{ formErrors.email?.[0] }}</small>
            </label>
            <label class="field">
                <span>CEP</span>
                <div class="inline-field">
                    <input v-model="form.postal_code" required />
                    <button class="ghost-button" type="button" @click="lookupPostalCode">
                        {{ lookupLoading ? 'Buscando...' : 'Buscar CEP' }}
                    </button>
                </div>
                <small>{{ formErrors.postal_code?.[0] }}</small>
            </label>
            <label class="field">
                <span>Logradouro</span>
                <input v-model="form.street" required />
                <small>{{ formErrors.street?.[0] }}</small>
            </label>
            <label class="field">
                <span>Numero</span>
                <input v-model="form.number" required />
                <small>{{ formErrors.number?.[0] }}</small>
            </label>
            <label class="field">
                <span>Complemento</span>
                <input v-model="form.complement" />
                <small>{{ formErrors.complement?.[0] }}</small>
            </label>
            <label class="field">
                <span>Bairro</span>
                <input v-model="form.district" />
                <small>{{ formErrors.district?.[0] }}</small>
            </label>
            <label class="field">
                <span>Cidade</span>
                <input v-model="form.city" required />
                <small>{{ formErrors.city?.[0] }}</small>
            </label>
            <label class="field">
                <span>Estado</span>
                <input v-model="form.state" maxlength="2" required />
                <small>{{ formErrors.state?.[0] }}</small>
            </label>

            <p v-if="errorMessage" class="form-error full-span">{{ errorMessage }}</p>

            <div class="form-actions full-span">
                <button class="ghost-button" type="button" @click="router.push({ name: 'rural-producers' })">
                    Cancelar
                </button>
                <button class="primary-button" :disabled="loading" type="submit">
                    {{ loading ? 'Salvando...' : 'Salvar produtor' }}
                </button>
            </div>
        </form>
    </section>
</template>
