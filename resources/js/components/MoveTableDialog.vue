<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl dark:bg-gray-800">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pindah Meja</h3>
        <button
          @click="$emit('close')"
          class="p-1 text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-4">
        <!-- Current Session Info -->
        <div class="p-3 mb-4 rounded-lg bg-gray-50 dark:bg-gray-900">
          <p class="text-sm text-gray-600 dark:text-gray-400">Sesi Saat Ini</p>
          <p class="font-bold text-gray-900 dark:text-white">Meja {{ session?.table?.table_number }}</p>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ session?.customer_name }}</p>
          <p class="text-sm text-brand-600 dark:text-brand-400">Tarif: {{ session?.rate?.name }} (Rp {{ formatCurrency(session?.rate?.price_per_hour) }}/jam)</p>
        </div>

        <!-- Select New Table -->
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Pilih Meja Tujuan
          </label>
          <select
            v-model="selectedTableId"
            class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          >
            <option value="">-- Pilih Meja --</option>
            <option
              v-for="table in availableTables"
              :key="table.id"
              :value="table.id"
            >
              Meja {{ table.table_number }}
            </option>
          </select>
          <p v-if="availableTables.length === 0" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
            Tidak ada meja tersedia saat ini
          </p>
        </div>

        <!-- Select New Rate (Optional) -->
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Ganti Tarif? <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <select
            v-model="selectedRateId"
            class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
          >
            <option value="">-- Tetap Tarif Saat Ini --</option>
            <option
              v-for="rate in rates"
              :key="rate.id"
              :value="rate.id"
              :disabled="rate.id === session?.rate?.id"
            >
              {{ rate.name }} - Rp {{ formatCurrency(rate.price_per_hour) }}/jam
              {{ rate.price_per_hour > session?.rate?.price_per_hour ? '↑' : rate.price_per_hour < session?.rate?.price_per_hour ? '↓' : '' }}
            </option>
          </select>
          <p v-if="selectedRateId && selectedRatePrice > currentRatePrice" class="mt-2 text-sm text-green-600 dark:text-green-400">
            ✓ Tarif naik: harga baru = Rp {{ formatCurrency(newTotalPrice) }}
          </p>
          <p v-else-if="selectedRateId && selectedRatePrice < currentRatePrice" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
            ⚠️ Tarif lebih murah - akan tetap pakai tarif lama (Rp {{ formatCurrency(session?.rate?.price_per_hour) }}/jam)
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="$emit('close')"
          class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          Batal
        </button>
        <button
          @click="confirmMove"
          :disabled="!selectedTableId || loading"
          class="flex-1 px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Pindah</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  session: {
    type: Object,
    default: null,
  },
  tables: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'success'])

const notify = useNotificationStore()
const selectedTableId = ref('')
const selectedRateId = ref('')
const loading = ref(false)
const rates = ref([])

// Filter only available tables (excluding current session's table)
const availableTables = computed(() => {
  return props.tables.filter(t => 
    t.status === 'available' && 
    t.id !== props.session?.table?.id
  )
})

// Get current rate price
const currentRatePrice = computed(() => {
  return props.session?.rate?.price_per_hour || 0
})

// Get selected rate price
const selectedRatePrice = computed(() => {
  if (!selectedRateId.value) return 0
  const rate = rates.value.find(r => r.id === parseInt(selectedRateId.value))
  return rate?.price_per_hour || 0
})

// Calculate new total price if rate changes
const newTotalPrice = computed(() => {
  if (!selectedRateId.value || !props.session?.duration_minutes) return 0
  const rate = rates.value.find(r => r.id === parseInt(selectedRateId.value))
  if (!rate) return 0
  return Math.round((rate.price_per_hour / 60) * props.session.duration_minutes)
})

// Fetch rates when component mounts
const fetchRates = async () => {
  try {
    const response = await axios.get('/api/rates?all=1')
    if (response.data.success) {
      rates.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch rates:', error)
  }
}

// Reset selection when dialog opens
watch(() => props.show, (newVal) => {
  if (newVal) {
    selectedTableId.value = ''
    selectedRateId.value = ''
    fetchRates()
  }
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const confirmMove = async () => {
  if (!selectedTableId.value) return

  loading.value = true
  try {
    const payload = {
      new_table_id: selectedTableId.value,
    }
    
    // Only include rate if changed
    if (selectedRateId.value) {
      payload.new_rate_id = selectedRateId.value
    }

    const response = await axios.post(`/api/sessions/${props.session.id}/move`, payload)

    if (response.data.success) {
      notify.success(response.data.message)
      emit('success')
      emit('close')
    } else {
      notify.error(response.data.message || 'Gagal pindah meja')
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal pindah meja')
  } finally {
    loading.value = false
  }
}
</script>
