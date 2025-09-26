<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useAuthStore } from './stores/auth';
import { computed } from 'vue';

const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const userName = computed(() => authStore.user?.name);

const handleLogout = () => {
  authStore.logout();
};

// Inicializa el estado de autenticación al cargar la app
authStore.checkAuth();
</script>

<template>
  <v-app>
    <v-app-bar v-if="isAuthenticated" color="primary" density="compact">
      <v-app-bar-title>Gestión de Usuarios</v-app-bar-title>
      <v-spacer></v-spacer>
      <span class="mr-4">Hola, {{ userName }}</span>
      <v-btn @click="handleLogout" variant="tonal">Cerrar Sesión</v-btn>
    </v-app-bar>

    <v-main>
      <RouterView />
    </v-main>
  </v-app>
</template>