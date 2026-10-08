import { describe, it, expect } from 'vitest'
import {
  formatStatusLabel,
  getStatusBadgeClass,
  formatDate
} from '../formatTugas'

describe('formatTugas utils', () => {
  describe('formatStatusLabel', () => {
    it('returns Indonesian translation for pending status', () => {
      expect(formatStatusLabel('pending')).toBe('Menunggu')
    })

    it('returns Indonesian translation for in_progress status', () => {
      expect(formatStatusLabel('in_progress')).toBe('Sedang Dikerjakan')
    })

    it('returns Indonesian translation for completed status', () => {
      expect(formatStatusLabel('completed')).toBe('Selesai')
    })

    it('falls back to Menunggu for unknown status', () => {
      expect(formatStatusLabel('other')).toBe('Menunggu')
    })
  })

  describe('getStatusBadgeClass', () => {
    it('returns correct CSS badge class for status', () => {
      expect(getStatusBadgeClass('pending')).toBe('badge-secondary')
      expect(getStatusBadgeClass('in_progress')).toBe('badge-warning')
      expect(getStatusBadgeClass('completed')).toBe('badge-success')
      expect(getStatusBadgeClass('unknown')).toBe('badge-secondary')
    })
  })

  describe('formatDate', () => {
    it('formats ISO YYYY-MM-DD date to DD/MM/YYYY', () => {
      expect(formatDate('2026-10-15')).toBe('15/10/2026')
    })

    it('handles empty or null date gracefully', () => {
      expect(formatDate(null)).toBe('-')
      expect(formatDate(undefined)).toBe('-')
      expect(formatDate('')).toBe('-')
    })
  })
})
