export interface User {
  id: number
  name: string
  email: string
}

export interface Tugas {
  id: number
  user_id: number
  title: string
  description?: string | null
  status: 'pending' | 'in_progress' | 'completed'
  due_date?: string | null
  created_at?: string
  updated_at?: string
}

export function formatStatusLabel(status: string): string {
  switch (status) {
    case 'in_progress':
      return 'Sedang Dikerjakan'
    case 'completed':
      return 'Selesai'
    case 'pending':
    default:
      return 'Menunggu'
  }
}

export function getStatusBadgeClass(status: string): string {
  switch (status) {
    case 'in_progress':
      return 'badge-warning'
    case 'completed':
      return 'badge-success'
    case 'pending':
    default:
      return 'badge-secondary'
  }
}

export function formatDate(dateString?: string | null): string {
  if (!dateString) return '-'
  const parts = dateString.split('-')
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`
  }
  return dateString
}
