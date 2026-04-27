<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import { api, extractApiError } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import type { ApiValidationError } from '@/types';

const authStore = useAuthStore();
const loadingProfile = ref(false);
const loadingPassword = ref(false);
const loadingPhoto = ref(false);
const profileMessage = ref('');
const passwordMessage = ref('');
const photoMessage = ref('');
const errorMessage = ref('');
const profileErrors = ref<Record<string, string[]>>({});
const passwordErrors = ref<Record<string, string[]>>({});
const photoErrors = ref<Record<string, string[]>>({});
const selectedPhoto = ref<File | null>(null);
const photoPreviewUrl = ref('');
const photoInput = ref<HTMLInputElement | null>(null);

const profileForm = reactive({
    name: '',
    email: '',
});

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const initials = computed(() => {
    const name = authStore.user?.name ?? '';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('') || 'US';
});

const photoPreview = computed(() => photoPreviewUrl.value || authStore.user?.profile_photo_url || '');

function syncProfileForm(): void {
    profileForm.name = authStore.user?.name ?? '';
    profileForm.email = authStore.user?.email ?? '';
}

function clearMessages(): void {
    errorMessage.value = '';
    profileMessage.value = '';
    passwordMessage.value = '';
    photoMessage.value = '';
}

async function submitProfile(): Promise<void> {
    loadingProfile.value = true;
    clearMessages();
    profileErrors.value = {};

    try {
        await api.put('/profile', profileForm);
        await authStore.fetchMe();
        syncProfileForm();
        profileMessage.value = 'Perfil atualizado com sucesso.';
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        profileErrors.value = payload.errors ?? {};
    } finally {
        loadingProfile.value = false;
    }
}

async function submitPassword(): Promise<void> {
    loadingPassword.value = true;
    clearMessages();
    passwordErrors.value = {};

    try {
        await api.put('/profile/password', passwordForm);
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        passwordMessage.value = 'Senha atualizada com sucesso.';
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        passwordErrors.value = payload.errors ?? {};
    } finally {
        loadingPassword.value = false;
    }
}

function handlePhotoChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    clearSelectedPhoto();
    selectedPhoto.value = file;
    photoPreviewUrl.value = file ? URL.createObjectURL(file) : '';
}

function clearSelectedPhoto(): void {
    if (photoPreviewUrl.value) {
        URL.revokeObjectURL(photoPreviewUrl.value);
    }

    selectedPhoto.value = null;
    photoPreviewUrl.value = '';

    if (photoInput.value) {
        photoInput.value.value = '';
    }
}

async function uploadPhoto(): Promise<void> {
    if (!selectedPhoto.value) {
        photoErrors.value = {
            photo: ['Selecione uma imagem antes de enviar.'],
        };
        return;
    }

    loadingPhoto.value = true;
    clearMessages();
    photoErrors.value = {};

    const formData = new FormData();
    formData.append('photo', selectedPhoto.value);

    try {
        await api.post('/profile/photo', formData);
        await authStore.fetchMe();
        clearSelectedPhoto();
        photoMessage.value = 'Foto de perfil atualizada com sucesso.';
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        photoErrors.value = payload.errors ?? {};
    } finally {
        loadingPhoto.value = false;
    }
}

async function deletePhoto(): Promise<void> {
    loadingPhoto.value = true;
    clearMessages();
    photoErrors.value = {};

    try {
        await api.delete('/profile/photo');
        await authStore.fetchMe();
        clearSelectedPhoto();
        photoMessage.value = 'Foto de perfil removida com sucesso.';
    } catch (error) {
        const payload = extractApiError(error) as ApiValidationError;
        errorMessage.value = payload.message;
        photoErrors.value = payload.errors ?? {};
    } finally {
        loadingPhoto.value = false;
    }
}

onMounted(syncProfileForm);
</script>

<template>
    <section class="page-section">
        <PageHeader
            title="Meu Perfil"
            description="Atualize seus dados de acesso, senha e foto de perfil."
        />

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>

        <section class="profile-grid">
            <article class="panel-card profile-card">
                <div class="profile-photo-frame">
                    <img v-if="photoPreview" :src="photoPreview" alt="Foto de perfil" />
                    <span v-else>{{ initials }}</span>
                </div>

                <div class="profile-card-copy">
                    <p class="page-eyebrow">{{ authStore.user?.role_label }}</p>
                    <h2>{{ authStore.user?.name }}</h2>
                    <p>{{ authStore.user?.email }}</p>
                </div>

                <label class="field">
                    <span>Foto de perfil</span>
                    <input
                        ref="photoInput"
                        accept="image/jpeg,image/png,image/webp"
                        type="file"
                        @change="handlePhotoChange"
                    />
                    <small>{{ photoErrors.photo?.[0] }}</small>
                </label>

                <p v-if="photoMessage" class="form-success">{{ photoMessage }}</p>

                <div class="form-actions">
                    <button class="ghost-button" :disabled="loadingPhoto" type="button" @click="deletePhoto">
                        Remover foto
                    </button>
                    <button class="primary-button" :disabled="loadingPhoto" type="button" @click="uploadPhoto">
                        {{ loadingPhoto ? 'Enviando...' : 'Enviar foto' }}
                    </button>
                </div>
            </article>

            <div class="profile-forms">
                <form class="panel-card form-grid-2" @submit.prevent="submitProfile">
                    <div class="full-span">
                        <p class="page-eyebrow">Dados pessoais</p>
                        <h2>Informações do perfil</h2>
                    </div>

                    <label class="field">
                        <span>Nome</span>
                        <input v-model="profileForm.name" required />
                        <small>{{ profileErrors.name?.[0] }}</small>
                    </label>

                    <label class="field">
                        <span>Email</span>
                        <input v-model="profileForm.email" type="email" required />
                        <small>{{ profileErrors.email?.[0] }}</small>
                    </label>

                    <p v-if="profileMessage" class="form-success full-span">{{ profileMessage }}</p>

                    <div class="form-actions full-span">
                        <button class="primary-button" :disabled="loadingProfile" type="submit">
                            {{ loadingProfile ? 'Salvando...' : 'Salvar perfil' }}
                        </button>
                    </div>
                </form>

                <form class="panel-card form-grid-2" @submit.prevent="submitPassword">
                    <div class="full-span">
                        <p class="page-eyebrow">Segurança</p>
                        <h2>Alterar senha</h2>
                    </div>

                    <label class="field">
                        <span>Senha atual</span>
                        <input v-model="passwordForm.current_password" autocomplete="current-password" required type="password" />
                        <small>{{ passwordErrors.current_password?.[0] }}</small>
                    </label>

                    <label class="field">
                        <span>Nova senha</span>
                        <input v-model="passwordForm.password" autocomplete="new-password" minlength="8" required type="password" />
                        <small>{{ passwordErrors.password?.[0] }}</small>
                    </label>

                    <label class="field">
                        <span>Confirmar nova senha</span>
                        <input
                            v-model="passwordForm.password_confirmation"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            type="password"
                        />
                        <small>{{ passwordErrors.password_confirmation?.[0] }}</small>
                    </label>

                    <p v-if="passwordMessage" class="form-success full-span">{{ passwordMessage }}</p>

                    <div class="form-actions full-span">
                        <button class="primary-button" :disabled="loadingPassword" type="submit">
                            {{ loadingPassword ? 'Alterando...' : 'Alterar senha' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </section>
</template>
