<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import PaginationBar from '@/components/PaginationBar.vue';
import { api, compactParams, downloadFile, extractApiError } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import type { Farm, LookupPayload, PaginatedResponse } from '@/types';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const errorMessage = ref('');
const farms = ref<PaginatedResponse<Farm> | null>(null);
const lookups = ref<LookupPayload | null>(null);
const filters = reactive({
    search: '',
    city: '',
    state: '',
    rural_producer_id: '',
    per_page: 10,
    page: 1,
});
let filterTimeout: ReturnType<typeof setTimeout> | undefined;

const stateOptions = computed(() => lookups.value?.farm_locations.map((location) => location.state) ?? []);
const cityOptions = computed(() => {
    if (!filters.state) {
        return [];
    }

    return lookups.value?.farm_locations.find((location) => location.state === filters.state)?.cities ?? [];
});

async function fetchLookups(): Promise<void> {
    const { data } = await api.get<LookupPayload>('/lookups/options');
    lookups.value = data;
}

async function fetchFarms(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<PaginatedResponse<Farm>>('/farms', {
            params: compactParams(filters),
        });

        farms.value = data;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function removeFarm(id: number): Promise<void> {
    if (!window.confirm('Deseja excluir esta fazenda e seus rebanhos?')) {
        return;
    }

    await api.delete(`/farms/${id}`);
    await fetchFarms();
}

async function exportFarms(): Promise<void> {
    await downloadFile('/exports/farms', 'fazendas.xlsx', filters);
}

function scheduleFilter(): void {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    filters.page = 1;
    filterTimeout = setTimeout(() => {
        void fetchFarms();
    }, 300);
}

watch(
    () => filters.state,
    () => {
        filters.city = '';
    },
);

watch(
    () => [filters.search, filters.state, filters.city, filters.rural_producer_id],
    scheduleFilter,
);

onMounted(async () => {
    await Promise.all([fetchLookups(), fetchFarms()]);
});
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Fazendas"
            description="Acompanhe unidades operacionais, inscrições e área total por produtor."
        >
            <button class="ghost-button" @click="exportFarms">Exportar .xlsx</button>
            <button
                v-if="authStore.isAdmin"
                class="primary-button"
                @click="router.push({ name: 'farms-create' })"
            >
                Nova fazenda
            </button>
        </PageHeader>

        <section class="panel-card filter-grid">
            <label class="field">
                <span>Busca</span>
                <input v-model="filters.search" placeholder="Nome ou inscricao" />
            </label>
            <label class="field">
                <span>Estado</span>
                <select v-model="filters.state">
                    <option value="">Todos os estados</option>
                    <option v-for="state in stateOptions" :key="state" :value="state">
                        {{ state }}
                    </option>
                </select>
            </label>
            <label class="field">
                <span>Cidade</span>
                <select v-model="filters.city" :disabled="!filters.state">
                    <option value="">{{ filters.state ? 'Todas as cidades' : 'Selecione um estado' }}</option>
                    <option v-for="city in cityOptions" :key="city" :value="city">
                        {{ city }}
                    </option>
                </select>
            </label>
            <label class="field">
                <span>Produtor</span>
                <select v-model="filters.rural_producer_id">
                    <option value="">Todos os produtores</option>
                    <option
                        v-for="producer in lookups?.rural_producers ?? []"
                        :key="producer.id"
                        :value="producer.id"
                    >
                        {{ producer.name }}
                    </option>
                </select>
            </label>
        </section>

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>
        <p v-else-if="loading" class="panel-card muted-card">Carregando fazendas...</p>

        <article v-else-if="farms" class="panel-card table-card">
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Localização</th>
                            <th>Produtor</th>
                            <th>Área</th>
                            <th>Animais</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="farm in farms.data" :key="farm.id">
                            <td>
                                <strong>{{ farm.name }}</strong>
                                <p class="table-subtitle">{{ farm.state_registration ?? 'Sem inscrição' }}</p>
                            </td>
                            <td>{{ farm.city }}, {{ farm.state }}</td>
                            <td>{{ farm.rural_producer?.name ?? '-' }}</td>
                            <td>{{ farm.total_area.toFixed(2) }} ha</td>
                            <td>{{ farm.total_animals ?? 0 }}</td>
                            <td class="actions-cell">
                                <button
                                    v-if="authStore.isAdmin"
                                    class="icon-button"
                                    title="Editar"
                                    aria-label="Editar"
                                    @click="router.push({ name: 'farms-edit', params: { id: farm.id } })"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24">
                                        <path d="M4 17.3V20h2.7L17.9 8.8l-2.7-2.7L4 17.3ZM19.7 7a1 1 0 0 0 0-1.4l-1.3-1.3a1 1 0 0 0-1.4 0l-1 1L18.7 8l1-1Z" />
                                    </svg>
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="icon-button danger-icon-button"
                                    title="Excluir"
                                    aria-label="Excluir"
                                    @click="removeFarm(farm.id)"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24">
                                        <path d="M8 4h8l1 2h4v2H3V6h4l1-2Zm1 6h2v8H9v-8Zm4 0h2v8h-2v-8Zm4 0h2v8h-2v-8ZM6 10h2l1 10h6l1-10h2l-1.2 11.1A1 1 0 0 1 15.8 22H8.2a1 1 0 0 1-1-.9L6 10Z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationBar
                :current-page="farms.meta.current_page"
                :last-page="farms.meta.last_page"
                @change="(page) => { filters.page = page; fetchFarms(); }"
            />
        </article>
    </section>
</template>
