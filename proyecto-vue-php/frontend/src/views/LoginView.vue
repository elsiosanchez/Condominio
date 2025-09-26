<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const email = ref('');
const password = ref('');
const error = ref<string | null>(null);
const isLoading = ref(false);
const showPassword = ref(false);

const handleLogin = async () => {
  error.value = null;
  isLoading.value = true;
  try {
    await authStore.login({ email: email.value, password: password.value });
  } catch (err: any) {
    error.value = err.message || 'Ocurrió un error inesperado.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <v-container class="fill-height">
    <v-row justify="center" align="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12">
          <v-toolbar color="primary">
            <v-toolbar-title>Iniciar Sesión</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-form @submit.prevent="handleLogin">
              <v-alert v-if="error" type="error" density="compact" class="mb-4">{{ error }}</v-alert>
              <v-text-field
                v-model="email"
                label="Email"
                prepend-inner-icon="mdi-email"
                type="email"
                required
              ></v-text-field>
              <v-text-field
                v-model="password"
                label="Contraseña"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPassword ? 'text' : 'password'"
                @click:append-inner="showPassword = !showPassword"
                required
              ></v-text-field>
              <v-btn 
                :loading="isLoading" 
                type="submit" 
                color="primary" 
                block
                class="mt-4"
              >
                Entrar
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>