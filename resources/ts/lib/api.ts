import axios from 'axios';
import type { ApiValidationError } from '@/types';

const STORAGE_KEY = 'agro-management-token';

export const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = getStoredToken();

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

export function getStoredToken(): string | null {
    return localStorage.getItem(STORAGE_KEY);
}

export function setStoredToken(token: string | null): void {
    if (token) {
        localStorage.setItem(STORAGE_KEY, token);
        return;
    }

    localStorage.removeItem(STORAGE_KEY);
}

export function extractApiError(error: unknown): ApiValidationError {
    if (axios.isAxiosError(error)) {
        const payload = error.response?.data as ApiValidationError | undefined;

        if (payload?.message) {
            return payload;
        }
    }

    return {
        message: 'Ocorreu um erro inesperado. Tente novamente.',
    };
}

export function compactParams(
    params?: Record<string, string | number | null | undefined>,
): Record<string, string | number> | undefined {
    if (!params) {
        return undefined;
    }

    const entries = Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined);

    return Object.fromEntries(entries) as Record<string, string | number>;
}

export async function downloadFile(
    url: string,
    filename: string,
    params?: Record<string, string | number | null | undefined>,
): Promise<void> {
    const response = await api.get<Blob>(url, {
        params: compactParams(params),
        responseType: 'blob',
    });

    const blobUrl = window.URL.createObjectURL(response.data);
    const link = document.createElement('a');

    link.href = blobUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(blobUrl);
}
