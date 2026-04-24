<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
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

function applyFilters(): void {
    filters.page = 1;
    void fetchFarms();
}

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
                <span>Cidade</span>
                <input v-model="filters.city" />
            </label>
            <label class="field">
                <span>Estado</span>
                <input v-model="filters.state" maxlength="2" />
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
            <button class="primary-button" @click="applyFilters">Aplicar filtros</button>
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
                                    class="ghost-button"
                                    @click="router.push({ name: 'farms-edit', params: { id: farm.id } })"
                                >
                                    Editar
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="danger-button"
                                    @click="removeFarm(farm.id)"
                                >
                                    Excluir
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
