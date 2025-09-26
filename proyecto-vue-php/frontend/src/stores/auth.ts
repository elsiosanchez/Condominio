import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '@/services/api';
import router from '@/router';

interface User {
  id: number;
  name: string;
  email: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token'));

  const isAuthenticated = computed(() => !!token.value);

  function setCredentials(data: { user: User; token: string }) {
    user.value = data.user;
    token.value = data.token;
    localStorage.setItem('token', data.token);
    // Opcional: guardar también el usuario en localStorage
    localStorage.setItem('user', JSON.stringify(data.user));
  }

  function clearCredentials() {
    user.value = null;
    token.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }

  async function login(credentials: { email: string; password: string }) {
    const response = await apiClient.post('/auth/login.php', credentials);
    if (response.data.success) {
      setCredentials(response.data.data);
      await router.push('/users');
    } else {
      throw new Error(response.data.error || 'Error en el login');
    }
  }

  function logout() {
    clearCredentials();
    router.push('/login');
  }
  
  // Función para inicializar el estado desde localStorage al cargar la app
  function checkAuth() {
    const storedToken = localStorage.getItem('token');
    const storedUser = localStorage.getItem('user');
    if (storedToken && storedUser) {
        token.value = storedToken;
        user.value = JSON.parse(storedUser);
    } else {
        clearCredentials();
    }
  }

  return { user, token, isAuthenticated, login, logout, checkAuth };
});
