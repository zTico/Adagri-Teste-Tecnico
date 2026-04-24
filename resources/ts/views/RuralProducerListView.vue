<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import PaginationBar from '@/components/PaginationBar.vue';
import { api, compactParams, downloadFile, extractApiError } from '@/lib/api';
import { formatCpfCnpj, formatPhone, formatPostalCode } from '@/lib/formatters';
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

async function removeProducer(id: number): Promise<void> {
    if (!window.confirm('Deseja excluir este produtor rural e todas as fazendas e rebanhos vinculados?')) {
        return;
    }

    await api.delete(`/rural-producers/${id}`);
    await fetchProducers();
}

async function exportProducerHerds(id: number): Promise<void> {
    await downloadFile(`/exports/rural-producers/${id}/herds-pdf`, `produtor-${id}-rebanhos.pdf`);
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
                            <th>Contato</th>
                            <th>Endereço</th>
                            <th>Fazendas</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="producer in producers.data" :key="producer.id">
                            <td>
                                <strong>{{ producer.name }}</strong>
                                <p class="table-subtitle">{{ producer.email ?? 'Sem email' }}</p>
                            </td>
                            <td>{{ formatCpfCnpj(producer.cpf_cnpj) }}</td>
                            <td>{{ formatPhone(producer.phone) }}</td>
                            <td>
                                {{ producer.address.city }}, {{ producer.address.state }}
                                <p class="table-subtitle">
                                    {{ producer.address.street }}, {{ producer.address.number }} ·
                                    {{ formatPostalCode(producer.address.postal_code) }}
                                </p>
                            </td>
                            <td>{{ producer.farms_count ?? 0 }}</td>
                            <td class="actions-cell">
                                <button class="ghost-button" @click="exportProducerHerds(producer.id)">
                                    PDF
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="ghost-button"
                                    @click="router.push({ name: 'rural-producers-edit', params: { id: producer.id } })"
                                >
                                    Editar
                                </button>
                                <button
                                    v-if="authStore.isAdmin"
                                    class="danger-button"
                                    @click="removeProducer(producer.id)"
                                >
                                    Excluir
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
