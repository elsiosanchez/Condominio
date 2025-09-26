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
const valid = ref(false);

const isEditing = ref(false);

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    error.value = null; // Limpia errores al abrir
    if (props.user) {
      isEditing.value = true;
      form.value = { name: props.user.name, email: props.user.email, password: '' };
    } else {
      isEditing.value = false;
      form.value = { name: '', email: '', password: '' };
    }
  }
});

const closeModal = () => {
  emit('close');
};

const handleSubmit = async () => {
  if (!valid.value) return;

  isLoading.value = true;
  error.value = null;
  try {
    let response;
    const payload = { ...form.value };
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

// Reglas de validación
const nameRules = [(v: string) => !!v || 'El nombre es requerido'];
const emailRules = [
  (v: string) => !!v || 'El email es requerido',
  (v: string) => /.+@.+\..+/.test(v) || 'El email debe ser válido',
];
const passwordRules = [
    (v: string) => !isEditing.value ? !!v || 'La contraseña es requerida' : true,
    (v: string) => (v.length >= 6 || !v) || 'La contraseña debe tener al menos 6 caracteres'
];

</script>

<template>
  <v-dialog :model-value="isOpen" @update:model-value="closeModal" persistent max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isEditing ? 'Editar Usuario' : 'Crear Usuario' }}</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-form v-model="valid" @submit.prevent="handleSubmit">
            <v-alert v-if="error" type="error" density="compact" class="mb-4">{{ error }}</v-alert>
            <v-text-field
              v-model="form.name"
              :rules="nameRules"
              label="Nombre"
              required
            ></v-text-field>
            <v-text-field
              v-model="form.email"
              :rules="emailRules"
              label="Email"
              type="email"
              required
            ></v-text-field>
            <v-text-field
              v-model="form.password"
              :rules="passwordRules"
              label="Contraseña"
              :placeholder="isEditing ? 'Dejar en blanco para no cambiar' : ''"
              type="password"
            ></v-text-field>
          </v-form>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue-darken-1" variant="text" @click="closeModal">Cancelar</v-btn>
        <v-btn color="blue-darken-1" variant="tonal" @click="handleSubmit" :loading="isLoading" :disabled="!valid">Guardar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>