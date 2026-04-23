import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { api, extractApiError, getStoredToken, setStoredToken } from '@/lib/api';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(getStoredToken());
    const user = ref<User | null>(null);
    const bootstrapped = ref(false);

    const isAuthenticated = computed(() => Boolean(token.value && user.value));
    const isAdmin = computed(() => user.value?.role === 'admin');

    async function login(email: string, password: string): Promise<void> {
        const { data } = await api.post<{ token: string; user: User }>('/auth/login', {
            email,
            password,
        });

        token.value = data.token;
        user.value = data.user;
        setStoredToken(data.token);
    }

    async function fetchMe(): Promise<void> {
        if (!token.value) {
            throw new Error('Missing token.');
        }

        const { data } = await api.get<User>('/auth/me');
        user.value = data;
    }

    async function bootstrap(): Promise<void> {
        if (bootstrapped.value) {
            return;
        }

        token.value = getStoredToken();

        if (!token.value) {
            bootstrapped.value = true;
            return;
        }

        try {
            await fetchMe();
        } catch (error) {
            clear();
            throw extractApiError(error);
        } finally {
            bootstrapped.value = true;
        }
    }

    async function logout(): Promise<void> {
        if (token.value) {
            await api.post('/auth/logout');
        }

        clear();
    }

    function clear(): void {
        token.value = null;
        user.value = null;
        setStoredToken(null);
    }

    return {
        bootstrapped,
        isAdmin,
        isAuthenticated,
        token,
        user,
        bootstrap,
        clear,
        fetchMe,
        login,
        logout,
    };
});
