<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import PaginationBar from '@/components/PaginationBar.vue';
import { api, compactParams, extractApiError } from '@/lib/api';
import { formatCpfCnpj } from '@/lib/formatters';
import { useAuthStore } from '@/stores/auth';
import type { LookupPayload, PaginatedResponse, RuralProducer } from '@/types';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const errorMessage = ref('');
const producers = ref<PaginatedResponse<RuralProducer> | null>(null);
const lookups = ref<LookupPayload | null>(null);
const filters = reactive({
    search: '',
    city: '',
    state: '',
    per_page: 10,
    page: 1,
});
let filterTimeout: ReturnType<typeof setTimeout> | undefined;

const stateOptions = computed(() => lookups.value?.producer_locations.map((location) => location.state) ?? []);
const cityOptions = computed(() => {
    if (!filters.state) {
        return [];
    }

    return lookups.value?.producer_locations.find((location) => location.state === filters.state)?.cities ?? [];
});

async function fetchLookups(): Promise<void> {
    const { data } = await api.get<LookupPayload>('/lookups/options');
    lookups.value = data;
}

async function fetchProducers(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<PaginatedResponse<RuralProducer>>('/rural-producers', {
            params: compactParams(filters),
        });

        producers.value = data;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function removeProducer(id: string): Promise<void> {
    if (!window.confirm('Deseja excluir este produtor rural e todas as fazendas e rebanhos vinculados?')) {
        return;
    }

    await api.delete(`/rural-producers/${id}`);
    await fetchProducers();
}

function scheduleFilter(): void {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    filters.page = 1;
    filterTimeout = setTimeout(() => {
        void fetchProducers();
    }, 300);
}

watch(
    () => filters.state,
    () => {
        filters.city = '';
    },
);

watch(
    () => [filters.search, filters.state, filters.city],
    scheduleFilter,
);

onMounted(async () => {
    await Promise.all([fetchLookups(), fetchProducers()]);
});
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Produtores Rurais"
            description="Cadastre produtores com dados de contato e endereço estruturado."
        >
            <button
                v-if="authStore.isAdmin"
                class="primary-button"
                @click="router.push({ name: 'rural-producers-create' })"
            >
                Novo produtor
            </button>
        </PageHeader>

        <section class="panel-card filter-grid">
            <label class="field">
                <span>Busca</span>
                <input v-model="filters.search" placeholder="Nome, email ou documento" />
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
        </section>

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>
        <p v-else-if="loading" class="panel-card muted-card">Carregando produtores...</p>

        <article v-else-if="producers" class="panel-card table-card">
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Documento</th>
                            <th>Fazendas</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="producer in producers.data" :key="producer.id">
                            <td>
                                <strong>{{ producer.name }}</strong>
                            </td>
                            <td>{{ formatCpfCnpj(producer.cpf_cnpj) }}</td>
                            <td>{{ producer.farms_count ?? 0 }}</td>
                            <td class="actions-cell">
                                <button
                                    class="icon-button"
                                    title="Ver detalhes"
                                    aria-label="Ver detalhes"
                                    @click="router.push({ name: 'rural-producers-show', params: { id: producer.id } })"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24">
                                        <path d="M12 5c5 0 8.5 4.1 9.7 6.4.2.4.2.8 0 1.2C20.5 14.9 17 19 12 19s-8.5-4.1-9.7-6.4a1.3 1.3 0 0 1 0-1.2C3.5 9.1 7 5 12 5Zm0 2C8.2 7 5.4 10 4.4 12c1 2 3.8 5 7.6 5s6.6-3 7.6-5C18.6 10 15.8 7 12 7Zm0 2.2a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6Z" />
                                    </svg>
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="icon-button"
                                    title="Editar"
                                    aria-label="Editar"
                                    @click="router.push({ name: 'rural-producers-edit', params: { id: producer.id } })"
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
                                    @click="removeProducer(producer.id)"
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
                :current-page="producers.meta.current_page"
                :last-page="producers.meta.last_page"
                @change="(page) => { filters.page = page; fetchProducers(); }"
            />
        </article>
    </section>
</template>
