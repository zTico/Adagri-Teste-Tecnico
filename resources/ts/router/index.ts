import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/views/DashboardView.vue'),
                meta: { title: 'Relatórios' },
            },
            {
                path: 'rural-producers',
                name: 'rural-producers',
                component: () => import('@/views/RuralProducerListView.vue'),
                meta: { title: 'Produtores Rurais' },
            },
            {
                path: 'rural-producers/create',
                name: 'rural-producers-create',
                component: () => import('@/views/RuralProducerFormView.vue'),
                meta: { title: 'Novo Produtor' },
            },
            {
                path: 'rural-producers/:id/edit',
                name: 'rural-producers-edit',
                component: () => import('@/views/RuralProducerFormView.vue'),
                meta: { title: 'Editar Produtor' },
            },
            {
                path: 'farms',
                name: 'farms',
                component: () => import('@/views/FarmListView.vue'),
                meta: { title: 'Fazendas' },
            },
            {
                path: 'farms/create',
                name: 'farms-create',
                component: () => import('@/views/FarmFormView.vue'),
                meta: { title: 'Nova Fazenda' },
            },
            {
                path: 'farms/:id/edit',
                name: 'farms-edit',
                component: () => import('@/views/FarmFormView.vue'),
                meta: { title: 'Editar Fazenda' },
            },
            {
                path: 'herds',
                name: 'herds',
                component: () => import('@/views/HerdListView.vue'),
                meta: { title: 'Rebanhos' },
            },
            {
                path: 'herds/create',
                name: 'herds-create',
                component: () => import('@/views/HerdFormView.vue'),
                meta: { title: 'Novo Rebanho' },
            },
            {
                path: 'herds/:id/edit',
                name: 'herds-edit',
                component: () => import('@/views/HerdFormView.vue'),
                meta: { title: 'Editar Rebanho' },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.bootstrapped) {
        try {
            await authStore.bootstrap();
        } catch {
            if (to.meta.requiresAuth) {
                return { name: 'login' };
            }
        }
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
