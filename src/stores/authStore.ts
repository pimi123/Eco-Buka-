import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { AUTH_TOKEN_KEY, apiGet, apiPost } from '../lib/api';

const USER_KEY = 'eco-buka-auth-user';

export interface CustomerUser {
  id: number;
  first_name: string | null;
  last_name: string | null;
  name: string;
  email: string;
  phone: string | null;
  email_verified: boolean;
}

interface AuthResponse {
  message: string;
  user: CustomerUser;
  token: string;
}

interface MessageResponse {
  message: string;
  user?: CustomerUser;
}

function loadUser(): CustomerUser | null {
  if (typeof localStorage === 'undefined') return null;

  try {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? JSON.parse(raw) as CustomerUser : null;
  } catch {
    return null;
  }
}

function loadToken(): string {
  if (typeof localStorage === 'undefined') return '';
  return localStorage.getItem(AUTH_TOKEN_KEY) || '';
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<CustomerUser | null>(loadUser());
  const token = ref(loadToken());
  const loading = ref(false);

  const isAuthenticated = computed(() => Boolean(token.value && user.value));
  const isEmailVerified = computed(() => Boolean(user.value?.email_verified));
  const displayName = computed(() => user.value?.first_name || user.value?.name || 'Klient');

  function persist(nextToken: string, nextUser: CustomerUser) {
    token.value = nextToken;
    user.value = nextUser;
    localStorage.setItem(AUTH_TOKEN_KEY, nextToken);
    localStorage.setItem(USER_KEY, JSON.stringify(nextUser));
  }

  function setUser(nextUser: CustomerUser) {
    user.value = nextUser;
    localStorage.setItem(USER_KEY, JSON.stringify(nextUser));
  }

  function clearLocalSession() {
    token.value = '';
    user.value = null;
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  }

  async function register(payload: {
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    password: string;
    password_confirmation: string;
  }) {
    loading.value = true;
    try {
      const response = await apiPost<AuthResponse>('/auth/register', payload);
      persist(response.token, response.user);
      return response;
    } finally {
      loading.value = false;
    }
  }

  async function login(payload: { email: string; password: string }) {
    loading.value = true;
    try {
      const response = await apiPost<AuthResponse>('/auth/login', payload);
      persist(response.token, response.user);
      return response;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUser() {
    if (!token.value) return null;

    try {
      const response = await apiGet<{ user: CustomerUser }>('/auth/user');
      setUser(response.user);
      return response.user;
    } catch (error) {
      clearLocalSession();
      throw error;
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await apiPost<MessageResponse>('/auth/logout', {});
      }
    } finally {
      clearLocalSession();
    }
  }

  async function resendVerification() {
    const response = await apiPost<MessageResponse>('/auth/email/verification-notification', {});
    if (response.user) setUser(response.user);
    return response;
  }

  async function forgotPassword(email: string) {
    return apiPost<MessageResponse>('/auth/forgot-password', { email });
  }

  async function resetPassword(payload: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    return apiPost<MessageResponse>('/auth/reset-password', payload);
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isEmailVerified,
    displayName,
    register,
    login,
    fetchUser,
    logout,
    resendVerification,
    forgotPassword,
    resetPassword,
    clearLocalSession,
  };
});
