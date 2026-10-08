<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '../api/client'

const router = useRouter()
const isLoginMode = ref(true)

const name = ref('')
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  loading.value = true

  try {
    if (isLoginMode.value) {
      const response = await apiClient.post('/login', {
        email: email.value,
        password: password.value,
      })
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      router.push('/')
    } else {
      const response = await apiClient.post('/register', {
        name: name.value,
        email: email.value,
        password: password.value,
      })
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      router.push('/')
    }
  } catch (err: any) {
    if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message
    } else {
      errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h2>{{ isLoginMode ? 'Masuk ke Aplikasi Tugas' : 'Daftar Akun Baru' }}</h2>
        <p class="subtitle">Evolusi Perangkat Lunak 2026</p>
      </div>

      <div class="auth-tabs">
        <button
          type="button"
          :class="['tab-btn', { active: isLoginMode }]"
          @click="isLoginMode = true; errorMessage = ''"
        >
          Masuk
        </button>
        <button
          type="button"
          :class="['tab-btn', { active: !isLoginMode }]"
          @click="isLoginMode = false; errorMessage = ''"
        >
          Daftar
        </button>
      </div>

      <div v-if="errorMessage" class="alert-error">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div v-if="!isLoginMode" class="form-group">
          <label for="name">Nama Lengkap</label>
          <input
            id="name"
            v-model="name"
            type="text"
            required
            placeholder="Afif"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="email">Alamat Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            placeholder="user@example.com"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            minlength="8"
            placeholder="Minimal 8 karakter"
            class="form-input"
          />
        </div>

        <button type="submit" :disabled="loading" class="btn btn-primary submit-btn">
          {{ loading ? 'Memproses...' : (isLoginMode ? 'Masuk' : 'Daftar') }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: 1rem;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
}

.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.auth-tabs {
  display: flex;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.tab-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  background: none;
  font-size: 0.95rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab-btn.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
}

.alert-error {
  background-color: #fee2e2;
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  text-align: left;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

.form-input {
  padding: 0.65rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-input:focus {
  border-color: #2563eb;
}

.submit-btn {
  margin-top: 0.5rem;
  width: 100%;
}
</style>
