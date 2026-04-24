<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import { api, extractApiError } from '@/lib/api';
import { maskCpfCnpj, maskPhone, maskPostalCode } from '@/lib/formatters';
import type { ApiValidationError, PostalCodeLookupPayload, ResourceResponse, RuralProducer } from '@/types';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => Boolean(route.params.id));
const loading = ref(false);
const lookupLoading = ref(false);
const errorMessage = ref('');
const formErrors = ref<Record<string, string[]>>({});
const lastLookedUpPostalCode = ref('');
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
        const response = await api.get<ResourceResponse<RuralProducer>>(`/rural-producers/${route.params.id}`);
        const producer = response.data.data;

        form.name = producer.name;
        form.cpf_cnpj = maskCpfCnpj(producer.cpf_cnpj);
        form.phone = maskPhone(producer.phone);
        form.email = producer.email ?? '';
        form.postal_code = maskPostalCode(producer.address.postal_code);
        form.street = producer.address.street;
        form.number = producer.address.number;
        form.complement = producer.address.complement ?? '';
        form.district = producer.address.district ?? '';
        form.city = producer.address.city;
        form.state = producer.address.state;
        lastLookedUpPostalCode.value = producer.address.postal_code;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function lookupPostalCode(): Promise<void> {
    const postalCode = form.postal_code.replace(/\D/g, '');

    if (postalCode.length !== 8 || postalCode === lastLookedUpPostalCode.value) {
        return;
    }

    lookupLoading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<PostalCodeLookupPayload>(`/lookups/postal-code/${postalCode}`);
        form.street = data.street ?? form.street;
        form.district = data.district ?? form.district;
        form.city = data.city ?? form.city;
        form.state = data.state ?? form.state;
        lastLookedUpPostalCode.value = postalCode;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        lookupLoading.value = false;
    }
}

function handleCpfCnpjInput(event: Event): void {
    const input = event.target as HTMLInputElement;

    form.cpf_cnpj = maskCpfCnpj(input.value);
}

function handlePostalCodeInput(event: Event): void {
    const input = event.target as HTMLInputElement;

    form.postal_code = maskPostalCode(input.value);

    if (form.postal_code.replace(/\D/g, '').length === 8) {
        void lookupPostalCode();
    }
}

function handlePhoneInput(event: Event): void {
    const input = event.target as HTMLInputElement;

    form.phone = maskPhone(input.value);
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
            description="Informe documento, contato e endereço estruturado."
        />

        <form class="panel-card form-grid-2" @submit.prevent="submit">
            <label class="field">
                <span>Nome</span>
                <input v-model="form.name" required />
                <small>{{ formErrors.name?.[0] }}</small>
            </label>
            <label class="field">
                <span>CPF / CNPJ</span>
                <input
                    :value="form.cpf_cnpj"
                    inputmode="numeric"
                    maxlength="18"
                    required
                    @input="handleCpfCnpjInput"
                />
                <small>{{ formErrors.cpf_cnpj?.[0] }}</small>
            </label>
            <label class="field">
                <span>Telefone</span>
                <input
                    :value="form.phone"
                    inputmode="numeric"
                    maxlength="15"
                    @input="handlePhoneInput"
                />
                <small>{{ formErrors.phone?.[0] }}</small>
            </label>
            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" />
                <small>{{ formErrors.email?.[0] }}</small>
            </label>
            <label class="field">
                <span>CEP</span>
                <input
                    :value="form.postal_code"
                    inputmode="numeric"
                    maxlength="9"
                    required
                    @input="handlePostalCodeInput"
                />
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
            <p v-else-if="lookupLoading" class="muted-card full-span">Buscando endereço pelo CEP...</p>

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
