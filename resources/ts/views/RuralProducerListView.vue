<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import PaginationBar from '@/components/PaginationBar.vue';
import { api, compactParams, downloadFile, extractApiError } from '@/lib/api';
import { formatCpfCnpj, formatPhone, formatPostalCode } from '@/lib/formatters';
import { useAuthStore } from '@/stores/auth';
import type { PaginatedResponse, RuralProducer } from '@/types';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const errorMessage = ref('');
const producers = ref<PaginatedResponse<RuralProducer> | null>(null);
const filters = reactive({
    search: '',
    city: '',
    state: '',
    per_page: 10,
    page: 1,
});

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

function applyFilters(): void {
    filters.page = 1;
    void fetchProducers();
}

onMounted(fetchProducers);
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Produtores Rurais"
            description="Cadastre produtores com dados de contato e endereco estruturado."
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
                <span>Cidade</span>
                <input v-model="filters.city" placeholder="Cidade" />
            </label>
            <label class="field">
                <span>Estado</span>
                <input v-model="filters.state" maxlength="2" placeholder="UF" />
            </label>
            <button class="primary-button" @click="applyFilters">Aplicar filtros</button>
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
                            <th>Endereco</th>
                            <th>Fazendas</th>
                            <th>Acoes</th>
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
