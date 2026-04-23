<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import { api, extractApiError } from '@/lib/api';
import type { ApiValidationError, Farm, LookupPayload } from '@/types';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => Boolean(route.params.id));
const loading = ref(false);
const errorMessage = ref('');
const formErrors = ref<Record<string, string[]>>({});
const lookups = ref<LookupPayload | null>(null);
const form = reactive({
    name: '',
    city: '',
    state: '',
    state_registration: '',
    total_area: 0,
    rural_producer_id: '',
});

async function fetchLookups(): Promise<void> {
    const { data } = await api.get<LookupPayload>('/lookups/options');
    lookups.value = data;
}

async function fetchFarm(): Promise<void> {
    if (!isEditing.value) {
        return;
    }

    loading.value = true;

    try {
        const { data } = await api.get<Farm>(`/farms/${route.params.id}`);
        form.name = data.name;
        form.city = data.city;
        form.state = data.state;
        form.state_registration = data.state_registration ?? '';
        form.total_area = data.total_area;
        form.rural_producer_id = String(data.rural_producer?.id ?? '');
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
            await api.put(`/farms/${route.params.id}`, form);
        } else {
            await api.post('/farms', form);
        }

        await router.push({ name: 'farms' });
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        formErrors.value = payload.errors ?? {};
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchLookups(), fetchFarm()]);
});
</script>

<template>
    <section class="page-section">
        <PageHeader
            :title="isEditing ? 'Editar Fazenda' : 'Cadastrar Fazenda'"
            description="Vincule fazendas a produtores e mantenha o cadastro de area consistente."
        />

        <form class="panel-card form-grid-2" @submit.prevent="submit">
            <label class="field">
                <span>Nome</span>
                <input v-model="form.name" required />
                <small>{{ formErrors.name?.[0] }}</small>
            </label>
            <label class="field">
                <span>Produtor</span>
                <select v-model="form.rural_producer_id" required>
                    <option value="">Selecione um produtor</option>
                    <option
                        v-for="producer in lookups?.rural_producers ?? []"
                        :key="producer.id"
                        :value="producer.id"
                    >
                        {{ producer.name }}
                    </option>
                </select>
                <small>{{ formErrors.rural_producer_id?.[0] }}</small>
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
            <label class="field">
                <span>Inscricao estadual</span>
                <input v-model="form.state_registration" />
                <small>{{ formErrors.state_registration?.[0] }}</small>
            </label>
            <label class="field">
                <span>Area total (ha)</span>
                <input v-model.number="form.total_area" min="0.01" step="0.01" type="number" required />
                <small>{{ formErrors.total_area?.[0] }}</small>
            </label>

            <p v-if="errorMessage" class="form-error full-span">{{ errorMessage }}</p>

            <div class="form-actions full-span">
                <button class="ghost-button" type="button" @click="router.push({ name: 'farms' })">
                    Cancelar
                </button>
                <button class="primary-button" :disabled="loading" type="submit">
                    {{ loading ? 'Salvando...' : 'Salvar fazenda' }}
                </button>
            </div>
        </form>
    </section>
</template>
