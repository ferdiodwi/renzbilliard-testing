<template>
  <div>
    <!-- Page Header -->
    <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Laporan Keuangan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Ringkasan keuangan bisnis
        </p>
      </div>
      
      <!-- Export Buttons -->
      <div class="flex items-center gap-2 mt-4 md:mt-0">
        <button 
          @click="downloadExport('pdf')"
          :disabled="isExporting"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 transition bg-white border border-red-200 rounded-lg hover:bg-red-50 dark:bg-gray-800 dark:border-red-900/30 dark:text-red-400 disabled:opacity-50"
        >
          <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          PDF
        </button>
        <button 
          @click="downloadExport('excel')"
          :disabled="isExporting"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-green-600 transition bg-white border border-green-200 rounded-lg hover:bg-green-50 dark:bg-gray-800 dark:border-green-900/30 dark:text-green-400 disabled:opacity-50"
        >
          <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          Excel
        </button>
      </div>
    </div>

    <!-- Period Selector -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
      <div class="flex flex-wrap items-center gap-3">
        <!-- Preset Buttons -->
        <button
          v-for="preset in presets"
          :key="preset.key"
          @click="selectPreset(preset.key)"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition',
            activePreset === preset.key
              ? 'bg-brand-500 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
          ]"
        >
          {{ preset.label }}
        </button>

        <!-- Custom Date Range Toggle -->
        <button
          @click="showCustom = !showCustom"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition flex items-center gap-2',
            activePreset === 'custom'
              ? 'bg-brand-500 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
          ]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Custom
        </button>

        <!-- Custom Date Inputs -->
        <div v-if="showCustom" class="flex items-center gap-2 ml-auto">
          <input 
            type="date" 
            v-model="filters.start_date"
            class="px-3 py-2 text-sm border rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
          <span class="text-gray-400">-</span>
          <input 
            type="date" 
            v-model="filters.end_date"
            class="px-3 py-2 text-sm border rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
          <button 
            @click="applyCustomDate"
            class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 hover:bg-brand-600"
          >
            Terapkan
          </button>
        </div>
      </div>

      <!-- Active Period Display -->
      <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
        📅 Periode: <span class="font-medium text-gray-700 dark:text-gray-300">{{ formatPeriod }}</span>
      </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid gap-4 mb-6 sm:grid-cols-3">
      <!-- Total Pemasukan -->
      <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pemasukan</p>
          <span class="p-2 text-green-600 rounded-lg bg-green-50 dark:bg-green-500/10">
            💰
          </span>
        </div>
        <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
          Rp {{ formatCurrency(stats.revenue) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">{{ stats.transactions }} transaksi</p>
      </div>

      <!-- Total Pengeluaran -->
      <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
          <span class="p-2 text-red-600 rounded-lg bg-red-50 dark:bg-red-500/10">
            💸
          </span>
        </div>
        <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
          Rp {{ formatCurrency(stats.expenses) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">{{ stats.expense_count }} pengeluaran</p>
      </div>

      <!-- Laba/Rugi Bersih -->
      <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laba Bersih</p>
          <span class="p-2 rounded-lg" :class="stats.profit >= 0 ? 'text-brand-600 bg-brand-50 dark:bg-brand-500/10' : 'text-red-600 bg-red-50 dark:bg-red-500/10'">
            📈
          </span>
        </div>
        <p class="mt-2 text-2xl font-bold" :class="stats.profit >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-red-600 dark:text-red-400'">
          Rp {{ formatCurrency(stats.profit) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">{{ stats.sessions }} sesi billiard</p>
      </div>
    </div>

    <!-- Breakdown Cards -->
    <div class="grid gap-4 mb-6 md:grid-cols-2">
      <!-- Revenue Breakdown -->
      <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Sumber Pendapatan</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              🎱 Billiard
            </span>
            <span class="font-semibold text-gray-800 dark:text-white">Rp {{ formatCurrency(breakdown.billiard) }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
            <div class="bg-brand-500 h-2 rounded-full" :style="{ width: getPercentage(breakdown.billiard, stats.revenue) + '%' }"></div>
          </div>
          <div class="flex items-center justify-between">
            <span class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              🍔 F&B
            </span>
            <span class="font-semibold text-gray-800 dark:text-white">Rp {{ formatCurrency(breakdown.fnb) }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
            <div class="bg-orange-500 h-2 rounded-full" :style="{ width: getPercentage(breakdown.fnb, stats.revenue) + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Expense Breakdown -->
      <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Kategori Pengeluaran</h3>
        <div class="space-y-2">
          <div v-for="(amount, category) in expenseBreakdown" :key="category" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ getCategoryLabel(category) }}</span>
            <span class="font-semibold text-red-600 dark:text-red-400">Rp {{ formatCurrency(amount) }}</span>
          </div>
          <div v-if="Object.keys(expenseBreakdown).length === 0" class="text-sm text-center text-gray-500 py-4">
            Belum ada pengeluaran
          </div>
        </div>
      </div>
    </div>

    <!-- Billiard & F&B Tables -->
    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Billiard Transactions Table -->
      <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            🎱 Transaksi Billiard
          </h2>
          <span class="px-2 py-1 text-xs font-medium bg-brand-100 text-brand-700 rounded-full dark:bg-brand-500/20 dark:text-brand-400">
            {{ billiardItems.length }} item
          </span>
        </div>
        <div class="overflow-x-auto max-h-96">
          <table class="w-full text-left">
            <thead class="bg-gray-200 dark:bg-gray-900 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Tanggal</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Invoice</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Meja</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">Jumlah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                  <svg class="inline w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                </td>
              </tr>
              <tr v-else-if="billiardItems.length === 0">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                  Tidak ada transaksi billiard
                </td>
              </tr>
              <tr v-else v-for="(item, index) in billiardItems" :key="'b-' + item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">{{ formatDateTime(item.date) }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white">{{ item.invoice }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ item.description }}</td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm font-bold text-green-600 dark:text-green-400">Rp {{ formatCurrency(item.amount) }}</span>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="billiardItems.length > 0" class="bg-gray-100 dark:bg-gray-900">
              <tr>
                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-right">Total:</td>
                <td class="px-4 py-3 text-right">
                  <span class="font-bold text-brand-600 dark:text-brand-400">Rp {{ formatCurrency(breakdown.billiard) }}</span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- F&B Transactions Table -->
      <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            🍔 Transaksi F&B
          </h2>
          <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full dark:bg-orange-500/20 dark:text-orange-400">
            {{ fnbItems.length }} item
          </span>
        </div>
        <div class="overflow-x-auto max-h-96">
          <table class="w-full text-left">
            <thead class="bg-gray-200 dark:bg-gray-900 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Tanggal</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Invoice</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Item</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">Jumlah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                  <svg class="inline w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                </td>
              </tr>
              <tr v-else-if="fnbItems.length === 0">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                  Tidak ada transaksi F&B
                </td>
              </tr>
              <tr v-else v-for="(item, index) in fnbItems" :key="'f-' + item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">{{ formatDateTime(item.date) }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white">{{ item.invoice }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ item.description }}</td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm font-bold text-green-600 dark:text-green-400">Rp {{ formatCurrency(item.amount) }}</span>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="fnbItems.length > 0" class="bg-gray-100 dark:bg-gray-900">
              <tr>
                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-right">Total:</td>
                <td class="px-4 py-3 text-right">
                  <span class="font-bold text-orange-600 dark:text-orange-400">Rp {{ formatCurrency(breakdown.fnb) }}</span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const notify = useNotificationStore()

// State
const loading = ref(false)
const isExporting = ref(false)
const showCustom = ref(false)
const activePreset = ref('today')

// Helper to format date as YYYY-MM-DD in local timezone
const formatDateLocal = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const today = new Date()
const filters = ref({
  start_date: formatDateLocal(today),
  end_date: formatDateLocal(today)
})

const stats = ref({
  revenue: 0,
  expenses: 0,
  profit: 0,
  sessions: 0,
  transactions: 0,
  expense_count: 0
})

const breakdown = ref({ billiard: 0, fnb: 0 })
const expenseBreakdown = ref({})
const billiardItems = ref([])
const fnbItems = ref([])

// Presets
const presets = [
  { key: 'today', label: 'Hari Ini' },
  { key: 'yesterday', label: 'Kemarin' },
  { key: 'week', label: '7 Hari' },
  { key: 'month', label: 'Bulan Ini' },
]

const selectPreset = (key) => {
  activePreset.value = key
  showCustom.value = false
  
  const now = new Date()
  let start, end
  
  switch (key) {
    case 'today':
      start = end = formatDateLocal(now)
      break
    case 'yesterday':
      const yesterday = new Date(now)
      yesterday.setDate(yesterday.getDate() - 1)
      start = end = formatDateLocal(yesterday)
      break
    case 'week':
      const weekAgo = new Date(now)
      weekAgo.setDate(weekAgo.getDate() - 6)
      start = formatDateLocal(weekAgo)
      end = formatDateLocal(now)
      break
    case 'month':
      const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)
      start = formatDateLocal(monthStart)
      end = formatDateLocal(now)
      break
  }
  
  filters.value.start_date = start
  filters.value.end_date = end
  fetchReports()
}

const applyCustomDate = () => {
  activePreset.value = 'custom'
  fetchReports()
}

// Computed
const formatPeriod = computed(() => {
  const start = new Date(filters.value.start_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
  const end = new Date(filters.value.end_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
  return filters.value.start_date === filters.value.end_date ? start : `${start} - ${end}`
})

// Methods
const fetchReports = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/reports', {
      params: {
        start_date: filters.value.start_date,
        end_date: filters.value.end_date
      }
    })

    if (response.data.success) {
      const data = response.data.data
      stats.value = data.summary
      breakdown.value = data.breakdown || { billiard: 0, fnb: 0 }
      expenseBreakdown.value = data.expense_breakdown || {}
      billiardItems.value = data.billiard_items || []
      fnbItems.value = data.fnb_items || []
    }
  } catch (error) {
    console.error('Failed to fetch reports:', error)
  } finally {
    loading.value = false
  }
}

const downloadExport = async (type) => {
  isExporting.value = true
  try {
    const response = await axios.get('/api/reports/export', {
      params: {
        start_date: filters.value.start_date,
        end_date: filters.value.end_date,
        type
      },
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let fileName = `Laporan.${type === 'excel' ? 'csv' : 'pdf'}`
    if (contentDisposition) {
      const match = contentDisposition.match(/filename="?([^"]+)"?/)
      if (match?.[1]) fileName = match[1]
    }
    
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    notify.error('Gagal mengunduh laporan')
  } finally {
    isExporting.value = false
  }
}

const formatCurrency = (val) => new Intl.NumberFormat('id-ID').format(val || 0)

const formatDateTime = (isoString) => {
  if (!isoString) return '-'
  return new Date(isoString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPercentage = (value, total) => {
  if (!total || total === 0) return 0
  return Math.min(100, Math.round((value / total) * 100))
}

const getCategoryLabel = (category) => {
  const labels = {
    operasional: 'Operasional',
    gaji: 'Gaji',
    pembelian_stok: 'Pembelian Stok',
    lainnya: 'Lainnya'
  }
  return labels[category] || category
}

onMounted(() => {
  fetchReports()
})
</script>
