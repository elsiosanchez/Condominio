<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import apiClient from '@/services/api';
import UserModal from '@/components/UserModal.vue';

interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
}

const users = ref<User[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);
const search = ref('');

const isModalOpen = ref(false);
const selectedUser = ref<User | null>(null);

const headers = [
  { title: 'ID', key: 'id', align: 'start' },
  { title: 'Nombre', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
];

const fetchUsers = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await apiClient.get('/users/index.php');
    if (response.data.success) {
      users.value = response.data.data;
    } else {
      throw new Error(response.data.error);
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || err.message || 'Error al cargar usuarios.';
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  selectedUser.value = null;
  isModalOpen.value = true;
};

const openEditModal = (user: User) => {
  selectedUser.value = { ...user };
  isModalOpen.value = true;
};

const handleDelete = async (userId: number) => {
  if (!confirm('¿Estás seguro de que quieres eliminar este usuario?')) return;
  try {
    await apiClient.delete(`/users/delete.php?id=${userId}`);
    users.value = users.value.filter(u => u.id !== userId);
  } catch (err: any) {
    alert(err.response?.data?.error || 'Error al eliminar el usuario.');
  }
};

const handleSaveUser = (savedUser: User) => {
  if (selectedUser.value) { // Es una actualización
    const index = users.value.findIndex(u => u.id === savedUser.id);
    if (index !== -1) {
      users.value[index] = savedUser;
    }
  } else { // Es una creación
    users.value.push(savedUser);
  }
  isModalOpen.value = false;
};

onMounted(fetchUsers);
</script>

<template>
  <v-container fluid>
    <v-card>
      <v-card-title>
        <span class="text-h5">Lista de Usuarios</span>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="openCreateModal">Crear Usuario</v-btn>
      </v-card-title>
      <v-card-text>
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Buscar"
          single-line
          hide-details
          class="mb-4"
        ></v-text-field>
        <v-alert v-if="error" type="error" density="compact" class="mb-4">{{ error }}</v-alert>
        <v-data-table
          :headers="headers"
          :items="users"
          :search="search"
          :loading="isLoading"
          class="elevation-1"
          item-value="id"
        >
          <template v-slot:loading>
            <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-icon size="small" class="me-2" @click="openEditModal(item)">mdi-pencil</v-icon>
            <v-icon size="small" @click="handleDelete(item.id)">mdi-delete</v-icon>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <UserModal 
      :is-open="isModalOpen" 
      :user="selectedUser" 
      @close="isModalOpen = false"
      @save="handleSaveUser"
    />
  </v-container>
</template>