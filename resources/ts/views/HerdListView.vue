<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import PaginationBar from '@/components/PaginationBar.vue';
import { api, compactParams, extractApiError } from '@/lib/api';
import { formatEnumLabel } from '@/lib/formatters';
import { useAuthStore } from '@/stores/auth';
import type { Herd, LookupPayload, PaginatedResponse } from '@/types';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const errorMessage = ref('');
const herds = ref<PaginatedResponse<Herd> | null>(null);
const lookups = ref<LookupPayload | null>(null);
const filters = reactive({
    search: '',
    species: '',
    purpose: '',
    farm_id: '',
    rural_producer_id: '',
    per_page: 10,
    page: 1,
});

async function fetchLookups(): Promise<void> {
    const { data } = await api.get<LookupPayload>('/lookups/options');
    lookups.value = data;
}

async function fetchHerds(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<PaginatedResponse<Herd>>('/herds', {
            params: compactParams(filters),
        });
        herds.value = data;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function removeHerd(id: number): Promise<void> {
    if (!window.confirm('Deseja excluir este registro de rebanho?')) {
        return;
    }

    await api.delete(`/herds/${id}`);
    await fetchHerds();
}

function applyFilters(): void {
    filters.page = 1;
    void fetchHerds();
}

onMounted(async () => {
    await Promise.all([fetchLookups(), fetchHerds()]);
});
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Rebanhos"
            description="Acompanhe especies, quantidades e finalidades produtivas em cada fazenda."
        >
            <button
                v-if="authStore.isAdmin"
                class="primary-button"
                @click="router.push({ name: 'herds-create' })"
            >
                Novo rebanho
            </button>
        </PageHeader>

        <section class="panel-card filter-grid">
            <label class="field">
                <span>Busca por fazenda</span>
                <input v-model="filters.search" placeholder="Nome da fazenda" />
            </label>
            <label class="field">
                <span>Especie</span>
                <select v-model="filters.species">
                    <option value="">Todas as especies</option>
                    <option v-for="species in lookups?.species ?? []" :key="species.value" :value="species.value">
                        {{ species.label }}
                    </option>
                </select>
            </label>
            <label class="field">
                <span>Finalidade</span>
                <select v-model="filters.purpose">
                    <option value="">Todas as finalidades</option>
                    <option v-for="purpose in lookups?.purposes ?? []" :key="purpose.value" :value="purpose.value">
                        {{ purpose.label }}
                    </option>
                </select>
            </label>
            <label class="field">
                <span>Fazenda</span>
                <select v-model="filters.farm_id">
                    <option value="">Todas as fazendas</option>
                    <option v-for="farm in lookups?.farms ?? []" :key="farm.id" :value="farm.id">
                        {{ farm.name }}
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
            <button class="primary-button" @click="applyFilters">Aplicar filtros</button>
        </section>

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>
        <p v-else-if="loading" class="panel-card muted-card">Carregando rebanhos...</p>

        <article v-else-if="herds" class="panel-card table-card">
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Especie</th>
                            <th>Finalidade</th>
                            <th>Quantidade</th>
                            <th>Fazenda</th>
                            <th>Produtor</th>
                            <th>Atualizado em</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="herd in herds.data" :key="herd.id">
                            <td>{{ formatEnumLabel(herd.species) }}</td>
                            <td>{{ formatEnumLabel(herd.purpose) }}</td>
                            <td>{{ herd.quantity }}</td>
                            <td>{{ herd.farm?.name ?? '-' }}</td>
                            <td>{{ herd.rural_producer?.name ?? '-' }}</td>
                            <td>{{ new Date(herd.updated_at).toLocaleString() }}</td>
                            <td class="actions-cell">
                                <button
                                    v-if="authStore.isAdmin"
                                    class="ghost-button"
                                    @click="router.push({ name: 'herds-edit', params: { id: herd.id } })"
                                >
                                    Editar
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="danger-button"
                                    @click="removeHerd(herd.id)"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationBar
                :current-page="herds.meta.current_page"
                :last-page="herds.meta.last_page"
                @change="(page) => { filters.page = page; fetchHerds(); }"
            />
        </article>
    </section>
</template>
