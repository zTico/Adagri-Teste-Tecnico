<script setup lang="ts">
import { onMounted, ref } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import StatsCard from '@/components/StatsCard.vue';
import { api, extractApiError } from '@/lib/api';
import { formatEnumLabel } from '@/lib/formatters';
import type { ReportsPayload } from '@/types';

const loading = ref(true);
const errorMessage = ref('');
const reports = ref<ReportsPayload | null>(null);

async function fetchReports(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get<ReportsPayload>('/reports');
        reports.value = data;
    } catch (error) {
        errorMessage.value = extractApiError(error).message;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchReports);
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Visão Geral dos Relatórios"
            description="Um resumo operacional rápido sobre produtores, fazendas e composição dos rebanhos."
        />

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>
        <p v-else-if="loading" class="panel-card muted-card">Carregando relatórios...</p>

        <template v-else-if="reports">
            <div class="stats-grid">
                <StatsCard label="Produtores Rurais" :value="reports.totals.rural_producers" tone="earth" />
                <StatsCard label="Fazendas" :value="reports.totals.farms" tone="gold" />
                <StatsCard label="Animais" :value="reports.totals.animals" tone="forest" />
            </div>

            <div class="split-grid">
                <article class="panel-card">
                    <h2>Fazendas por cidade</h2>
                    <ul class="metric-list">
                        <li v-for="item in reports.farms_by_city" :key="`${item.city}-${item.state}`">
                            <span>{{ item.city }}, {{ item.state }}</span>
                            <strong>{{ item.total }}</strong>
                        </li>
                    </ul>
                </article>

                <article class="panel-card">
                    <h2>Animais por espécie</h2>
                    <ul class="metric-list">
                        <li v-for="item in reports.animals_by_species" :key="item.species">
                            <span>{{ formatEnumLabel(item.species) }}</span>
                            <strong>{{ item.total }}</strong>
                        </li>
                    </ul>
                </article>
            </div>
        </template>
    </section>
</template>
