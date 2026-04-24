<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import { api, extractApiError } from '@/lib/api';
import type { ApiValidationError, Herd, LookupPayload, ResourceResponse } from '@/types';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => Boolean(route.params.id));
const loading = ref(false);
const errorMessage = ref('');
const formErrors = ref<Record<string, string[]>>({});
const lookups = ref<LookupPayload | null>(null);
const form = reactive({
    species: '',
    quantity: 1,
    purpose: '',
    farm_id: '',
});

async function fetchLookups(): Promise<void> {
    const { data } = await api.get<LookupPayload>('/lookups/options');
    lookups.value = data;
}

async function fetchHerd(): Promise<void> {
    if (!isEditing.value) {
        return;
    }

    loading.value = true;

    try {
        const response = await api.get<ResourceResponse<Herd>>(`/herds/${route.params.id}`);
        const herd = response.data.data;

        form.species = herd.species;
        form.quantity = herd.quantity;
        form.purpose = herd.purpose;
        form.farm_id = String(herd.farm?.id ?? '');
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function submit(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    formErrors.value = {};

    try {
        if (isEditing.value) {
            await api.put(`/herds/${route.params.id}`, form);
        } else {
            await api.post('/herds', form);
        }

        await router.push({ name: 'herds' });
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        formErrors.value = payload.errors ?? {};
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchLookups(), fetchHerd()]);
});
</script>

<template>
    <section class="page-section">
        <PageHeader
            :title="isEditing ? 'Editar Rebanho' : 'Cadastrar Rebanho'"
            description="Informe finalidade produtiva e quantidade com espécies padronizadas."
        />

        <form class="panel-card form-grid-2" @submit.prevent="submit">
            <label class="field">
                <span>Fazenda</span>
                <select v-model="form.farm_id" required>
                    <option value="">Selecione uma fazenda</option>
                    <option v-for="farm in lookups?.farms ?? []" :key="farm.id" :value="farm.id">
                        {{ farm.name }}
                    </option>
                </select>
                <small>{{ formErrors.farm_id?.[0] }}</small>
            </label>
            <label class="field">
                <span>Espécie</span>
                <select v-model="form.species" required>
                    <option value="">Selecione uma espécie</option>
                    <option v-for="species in lookups?.species ?? []" :key="species.value" :value="species.value">
                        {{ species.label }}
                    </option>
                </select>
                <small>{{ formErrors.species?.[0] }}</small>
            </label>
            <label class="field">
                <span>Finalidade</span>
                <select v-model="form.purpose" required>
                    <option value="">Selecione uma finalidade</option>
                    <option v-for="purpose in lookups?.purposes ?? []" :key="purpose.value" :value="purpose.value">
                        {{ purpose.label }}
                    </option>
                </select>
                <small>{{ formErrors.purpose?.[0] }}</small>
            </label>
            <label class="field">
                <span>Quantidade</span>
                <input v-model.number="form.quantity" min="1" type="number" required />
                <small>{{ formErrors.quantity?.[0] }}</small>
            </label>

            <p v-if="errorMessage" class="form-error full-span">{{ errorMessage }}</p>

            <div class="form-actions full-span">
                <button class="ghost-button" type="button" @click="router.push({ name: 'herds' })">
                    Cancelar
                </button>
                <button class="primary-button" :disabled="loading" type="submit">
                    {{ loading ? 'Salvando...' : 'Salvar rebanho' }}
                </button>
            </div>
        </form>
    </section>
</template>
