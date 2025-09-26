<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import apiClient from '@/services/api';

interface User {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  isOpen: boolean;
  user: User | null;
}>();

const emit = defineEmits(['close', 'save']);

const form = ref({
  name: '',
  email: '',
  password: ''
});
const error = ref<string | null>(null);
const isLoading = ref(false);

const isEditing = ref(false);

watch(() => props.user, (newUser) => {
  if (newUser) {
    isEditing.value = true;
    form.value = { name: newUser.name, email: newUser.email, password: '' };
  } else {
    isEditing.value = false;
    form.value = { name: '', email: '', password: '' };
  }
});

const closeModal = () => {
  emit('close');
  error.value = null;
};

const handleSubmit = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    let response;
    const payload = { ...form.value };
    // No enviar la contraseña si está vacía en modo edición
    if (isEditing.value && !payload.password) {
      delete (payload as any).password;
    }

    if (isEditing.value && props.user) {
      response = await apiClient.put(`/users/update.php?id=${props.user.id}`, payload);
    } else {
      response = await apiClient.post('/users/create.php', payload);
    }

    if (response.data.success) {
      emit('save', response.data.data);
      closeModal();
    } else {
      throw new Error(response.data.error);
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || err.message || 'Ocurrió un error.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" @click.self="closeModal">
    <div class="relative mx-auto p-8 border w-full max-w-md shadow-lg rounded-md bg-white">
      <h3 class="text-2xl font-bold mb-6">{{ isEditing ? 'Editar Usuario' : 'Crear Usuario' }}</h3>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input v-model="form.password" type="password" :placeholder="isEditing ? 'Dejar en blanco para no cambiar' : ''" :required="!isEditing" class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div v-if="error" class="p-3 text-sm text-red-700 bg-red-100 rounded-md">
          {{ error }}
        </div>
        <div class="flex justify-end space-x-4 pt-4">
          <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancelar</button>
          <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:bg-indigo-300">
            <span v-if="isLoading">Guardando...</span>
            <span v-else>Guardar</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
