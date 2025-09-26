<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const isModalOpen = ref(false);
const selectedUser = ref<User | null>(null);

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
  <div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Lista de Usuarios</h1>
      <button @click="openCreateModal" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
        Crear Usuario
      </button>
    </div>

    <div v-if="isLoading" class="text-center">Cargando...</div>
    <div v-if="error" class="p-4 text-red-700 bg-red-100 rounded-md">{{ error }}</div>

    <div v-if="!isLoading && !error" class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
              <button @click="handleDelete(user.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <UserModal 
      :is-open="isModalOpen" 
      :user="selectedUser" 
      @close="isModalOpen = false"
      @save="handleSaveUser"
    />
  </div>
</template>
