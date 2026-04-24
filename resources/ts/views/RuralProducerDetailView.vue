<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageHeader from '@/components/PageHeader.vue';
import StatsCard from '@/components/StatsCard.vue';
import { api, downloadFile, extractApiError } from '@/lib/api';
import { formatCpfCnpj, formatEnumLabel, formatPhone, formatPostalCode } from '@/lib/formatters';
import { useAuthStore } from '@/stores/auth';
import type { Herd, ResourceResponse, RuralProducer } from '@/types';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const errorMessage = ref('');
const producer = ref<RuralProducer | null>(null);

const farms = computed(() => producer.value?.farms ?? []);
const herds = computed<Herd[]>(() => farms.value.flatMap((farm) => farm.herds ?? []));
const totalAnimals = computed(() => herds.value.reduce((total, herd) => total + herd.quantity, 0));
const totalArea = computed(() => farms.value.reduce((total, farm) => total + farm.total_area, 0));
const speciesSummary = computed(() => {
    const totals = new Map<string, number>();

    for (const herd of herds.value) {
        totals.set(herd.species, (totals.get(herd.species) ?? 0) + herd.quantity);
    }

    return Array.from(totals.entries()).map(([species, quantity]) => ({
        species,
        quantity,
    }));
});

async function fetchProducer(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<ResourceResponse<RuralProducer>>(`/rural-producers/${route.params.id}`);
        producer.value = data.data;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

async function removeProducer(): Promise<void> {
    if (!producer.value || !window.confirm('Deseja excluir este produtor rural e todas as fazendas e rebanhos vinculados?')) {
        return;
    }

    await api.delete(`/rural-producers/${producer.value.id}`);
    await router.push({ name: 'rural-producers' });
}

async function exportProducerHerds(id: number): Promise<void> {
    await downloadFile(`/exports/rural-producers/${id}/herds-pdf`, `produtor-${id}-rebanhos.pdf`);
}

onMounted(fetchProducer);
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Detalhes do Produtor"
            description="Informações cadastrais, fazendas vinculadas e resumo do rebanho."
        >
            <button class="ghost-button" @click="router.push({ name: 'rural-producers' })">
                Voltar
            </button>
            <button v-if="producer" class="ghost-button" @click="exportProducerHerds(producer.id)">
                Exportar PDF
            </button>
            <button
                v-if="producer && authStore.isAdmin"
                class="primary-button"
                @click="router.push({ name: 'rural-producers-edit', params: { id: producer.id } })"
            >
                Editar
            </button>
            <button v-if="producer && authStore.isAdmin" class="danger-button" @click="removeProducer">
                Excluir
            </button>
        </PageHeader>

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>
        <p v-else-if="loading" class="panel-card muted-card">Carregando produtor...</p>

        <template v-else-if="producer">
            <div class="stats-grid">
                <StatsCard label="Fazendas" :value="farms.length" tone="earth" />
                <StatsCard label="Animais" :value="totalAnimals" tone="forest" />
                <StatsCard label="Área total" :value="`${totalArea.toFixed(2)} ha`" tone="gold" />
            </div>

            <div class="detail-grid">
                <article class="panel-card detail-card">
                    <h2>Dados cadastrais</h2>
                    <dl class="detail-list">
                        <div>
                            <dt>Nome</dt>
                            <dd>{{ producer.name }}</dd>
                        </div>
                        <div>
                            <dt>Documento</dt>
                            <dd>{{ formatCpfCnpj(producer.cpf_cnpj) }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ producer.email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Telefone</dt>
                            <dd>{{ formatPhone(producer.phone) }}</dd>
                        </div>
                    </dl>
                </article>

                <article class="panel-card detail-card">
                    <h2>Endereço</h2>
                    <dl class="detail-list">
                        <div>
                            <dt>Logradouro</dt>
                            <dd>{{ producer.address.street }}, {{ producer.address.number }}</dd>
                        </div>
                        <div>
                            <dt>Complemento</dt>
                            <dd>{{ producer.address.complement ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Bairro</dt>
                            <dd>{{ producer.address.district ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Cidade / UF</dt>
                            <dd>{{ producer.address.city }} / {{ producer.address.state }}</dd>
                        </div>
                        <div>
                            <dt>CEP</dt>
                            <dd>{{ formatPostalCode(producer.address.postal_code) }}</dd>
                        </div>
                    </dl>
                </article>
            </div>

            <div class="detail-grid">
                <article class="panel-card detail-card">
                    <h2>Fazendas vinculadas</h2>
                    <ul class="detail-list compact-list">
                        <li v-for="farm in farms" :key="farm.id">
                            <strong>{{ farm.name }}</strong>
                            <span>{{ farm.city }}/{{ farm.state }} · {{ farm.total_area.toFixed(2) }} ha</span>
                        </li>
                        <li v-if="farms.length === 0">Nenhuma fazenda vinculada.</li>
                    </ul>
                </article>

                <article class="panel-card detail-card">
                    <h2>Animais por espécie</h2>
                    <ul class="metric-list">
                        <li v-for="item in speciesSummary" :key="item.species">
                            <span>{{ formatEnumLabel(item.species) }}</span>
                            <strong>{{ item.quantity }}</strong>
                        </li>
                        <li v-if="speciesSummary.length === 0">
                            <span>Nenhum animal cadastrado</span>
                            <strong>0</strong>
                        </li>
                    </ul>
                </article>
            </div>
        </template>
    </section>
</template>
