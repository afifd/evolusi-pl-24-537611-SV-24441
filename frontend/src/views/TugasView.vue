<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '../api/client'
import {
  formatStatusLabel,
  getStatusBadgeClass,
  formatDate,
  type Tugas,
  type User
} from '../utils/formatTugas'
import { LogOut, Plus, Trash2, CheckCircle2, Clock } from 'lucide-vue-next'

const router = useRouter()
const currentUser = ref<User | null>(null)
const tugasList = ref<Tugas[]>([])
const loading = ref(false)
const errorMessage = ref('')

// Form state
const isModalOpen = ref(false)
const formTitle = ref('')
const formDescription = ref('')
const formDueDate = ref('')
const formStatus = ref<'pending' | 'in_progress' | 'completed'>('pending')
const formSubmitting = ref(false)

onMounted(() => {
  const savedUser = localStorage.getItem('user')
  if (savedUser) {
    try {
      currentUser.value = JSON.parse(savedUser)
    } catch {
      // ignore parse error
    }
  }
  fetchTugas()
})

async function fetchTugas() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await apiClient.get('/tugas')
    tugasList.value = response.data.data
  } catch (err: any) {
    errorMessage.value = 'Gagal memuat daftar tugas.'
  } finally {
    loading.value = false
  }
}

async function handleCreateTugas() {
  if (!formTitle.value.trim()) return

  formSubmitting.value = true
  try {
    await apiClient.post('/tugas', {
      title: formTitle.value,
      description: formDescription.value || null,
      status: formStatus.value,
      due_date: formDueDate.value || null,
    })

    formTitle.value = ''
    formDescription.value = ''
    formDueDate.value = ''
    formStatus.value = 'pending'
    isModalOpen.value = false
    await fetchTugas()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menambahkan tugas.')
  } finally {
    formSubmitting.value = false
  }
}

async function toggleStatus(tugas: Tugas) {
  const nextStatus: Record<string, 'pending' | 'in_progress' | 'completed'> = {
    pending: 'in_progress',
    in_progress: 'completed',
    completed: 'pending',
  }

  const updatedStatus = nextStatus[tugas.status] || 'pending'
  try {
    await apiClient.put(`/tugas/${tugas.id}`, {
      status: updatedStatus,
    })
    tugas.status = updatedStatus
  } catch (err) {
    alert('Gagal memperbarui status tugas.')
  }
}

async function handleDeleteTugas(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus tugas ini?')) return

  try {
    await apiClient.delete(`/tugas/${id}`)
    tugasList.value = tugasList.value.filter((t) => t.id !== id)
  } catch (err) {
    alert('Gagal menghapus tugas.')
  }
}

async function handleLogout() {
  try {
    await apiClient.post('/logout')
  } catch {
    // continue cleanup
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}
</script>

<template>
  <div class="dashboard-container">
    <!-- Navbar -->
    <header class="navbar">
      <div class="nav-brand">
        <h1>Daftar Tugas</h1>
        <span class="user-greeting" v-if="currentUser">
          Halo, <strong>{{ currentUser.name }}</strong>
        </span>
      </div>
      <div class="nav-actions">
        <button class="btn btn-primary" @click="isModalOpen = true">
          <Plus class="icon" /> Tambah Tugas
        </button>
        <button class="btn btn-outline" @click="handleLogout">
          <LogOut class="icon" /> Keluar
        </button>
      </div>
    </header>

    <!-- Content -->
    <main class="main-content">
      <div v-if="errorMessage" class="alert-error">
        {{ errorMessage }}
      </div>

      <div v-if="loading" class="loading-state">
        Memuat data tugas...
      </div>

      <div v-else-if="tugasList.length === 0" class="empty-state">
        <Clock class="empty-icon" />
        <p>Belum ada tugas yang tercatat.</p>
        <button class="btn btn-primary" @click="isModalOpen = true">
          Buat Tugas Pertama
        </button>
      </div>

      <div v-else class="tugas-grid">
        <div
          v-for="tugas in tugasList"
          :key="tugas.id"
          :class="['tugas-card', { completed: tugas.status === 'completed' }]"
        >
          <div class="card-header">
            <span :class="['badge', getStatusBadgeClass(tugas.status)]">
              {{ formatStatusLabel(tugas.status) }}
            </span>
            <div class="card-actions">
              <button
                class="icon-btn action-toggle"
                :title="'Ubah status'"
                @click="toggleStatus(tugas)"
              >
                <CheckCircle2 class="icon" />
              </button>
              <button
                class="icon-btn action-delete"
                title="Hapus tugas"
                @click="handleDeleteTugas(tugas.id)"
              >
                <Trash2 class="icon" />
              </button>
            </div>
          </div>

          <h3 class="card-title">{{ tugas.title }}</h3>
          <p class="card-desc">{{ tugas.description || 'Tidak ada deskripsi' }}</p>

          <div class="card-footer">
            <span class="due-date">
              Batas Waktu: {{ formatDate(tugas.due_date) }}
            </span>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Form Tambah Tugas -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="isModalOpen = false">
      <div class="modal-card">
        <h3>Tambah Tugas Baru</h3>
        <form @submit.prevent="handleCreateTugas" class="modal-form">
          <div class="form-group">
            <label for="m-title">Judul Tugas *</label>
            <input
              id="m-title"
              v-model="formTitle"
              type="text"
              required
              class="form-input"
              placeholder="Contoh: Implementasi Vitest"
            />
          </div>

          <div class="form-group">
            <label for="m-desc">Deskripsi</label>
            <textarea
              id="m-desc"
              v-model="formDescription"
              rows="3"
              class="form-input"
              placeholder="Detail tugas atau catatan tambahan"
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group flex-1">
              <label for="m-date">Batas Waktu</label>
              <input
                id="m-date"
                v-model="formDueDate"
                type="date"
                class="form-input"
              />
            </div>
            <div class="form-group flex-1">
              <label for="m-status">Status</label>
              <select id="m-status" v-model="formStatus" class="form-input">
                <option value="pending">Menunggu</option>
                <option value="in_progress">Sedang Dikerjakan</option>
                <option value="completed">Selesai</option>
              </select>
            </div>
          </div>

          <div class="modal-actions">
            <button
              type="button"
              class="btn btn-outline"
              @click="isModalOpen = false"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="formSubmitting"
              class="btn btn-primary"
            >
              {{ formSubmitting ? 'Menyimpan...' : 'Simpan Tugas' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 2rem;
}

.nav-brand h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.user-greeting {
  font-size: 0.9rem;
  color: #64748b;
}

.nav-actions {
  display: flex;
  gap: 0.75rem;
}

.tugas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap: 1.25rem;
}

.tugas-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.tugas-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.tugas-card.completed {
  background-color: #f8fafc;
  border-color: #cbd5e1;
  opacity: 0.85;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  text-align: left;
}

.card-desc {
  color: #64748b;
  font-size: 0.9rem;
  flex-grow: 1;
  margin: 0 0 1rem 0;
  text-align: left;
  white-space: pre-line;
}

.card-footer {
  border-top: 1px solid #f1f5f9;
  padding-top: 0.75rem;
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #94a3b8;
}

.badge {
  padding: 0.25rem 0.65rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-secondary {
  background-color: #f1f5f9;
  color: #475569;
}

.badge-warning {
  background-color: #fef3c7;
  color: #d97706;
}

.badge-success {
  background-color: #dcfce7;
  color: #16a34a;
}

.card-actions {
  display: flex;
  gap: 0.35rem;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.35rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.2s;
}

.icon-btn:hover {
  background-color: #f1f5f9;
}

.action-toggle:hover {
  color: #16a34a;
}

.action-delete:hover {
  color: #dc2626;
}

.icon {
  width: 1.1rem;
  height: 1.1rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 1rem;
  color: #64748b;
}

.empty-icon {
  width: 3rem;
  height: 3rem;
  color: #94a3b8;
  margin-bottom: 1rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 50;
}

.modal-card {
  background: white;
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  padding: 1.75rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-card h3 {
  margin: 0 0 1.25rem 0;
  font-size: 1.25rem;
  color: #1e293b;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  text-align: left;
}

.form-row {
  display: flex;
  gap: 1rem;
}

.flex-1 {
  flex: 1;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}
</style>
