<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="handleClose"
  >
    <div class="w-full max-w-sm overflow-hidden bg-white shadow-xl rounded-2xl dark:bg-gray-800">
      
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
          {{ step === 1 ? `Mulai Sesi - Meja ${table?.table_number || ''}` : 'Konfirmasi Pembayaran' }}
        </h3>
      </div>

      <!-- Step 1: Form -->
      <form v-if="step === 1" @submit.prevent="handleNextStep">
        <div class="px-6 py-4 space-y-4">
          <!-- Customer Name -->
          <div>
            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              Nama Pelanggan
            </label>
            <input
              v-model="formData.customer_name"
              type="text"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="Masukkan nama pelanggan"
            />
          </div>

          <!-- Billing Type -->
          <div>
            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              Jenis Billing
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="formData.is_open_billing = true"
                class="px-3 py-2 text-sm font-medium transition border rounded-lg"
                :class="formData.is_open_billing === true
                  ? 'bg-brand-50 border-brand-500 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                  : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300'"
              >
                Open Billing
              </button>
              <button
                type="button"
                @click="formData.is_open_billing = false"
                class="px-3 py-2 text-sm font-medium transition border rounded-lg"
                :class="formData.is_open_billing === false
                  ? 'bg-brand-50 border-brand-500 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                  : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300'"
              >
                Paket / Durasi
              </button>
            </div>
            <!-- Warning for Open Billing -->
            <p v-if="formData.is_open_billing === true" class="mt-2 text-xs text-amber-600 dark:text-amber-400 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              Open Billing dikenakan minimum charge 2 jam.
            </p>
          </div>

          <!-- Duration (if closed billing) -->
          <div v-if="!formData.is_open_billing">
            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              Durasi
            </label>
            <select
              v-model.number="formData.duration_minutes"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="" disabled>Pilih durasi...</option>
              <option v-for="hour in 12" :key="hour" :value="hour * 60">
                {{ hour }} Jam
              </option>
            </select>
          </div>

          <!-- Rate Selection -->
          <div>
            <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              Pilih Paket Rate
            </label>
            <select
              v-model="formData.rate_id"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="" disabled>Pilih paket rate...</option>
              <template v-if="rates && rates.length > 0">
                <option v-for="rate in rates" :key="rate?.id || 'unknown'" :value="rate?.id">
                   {{ rate?.name || 'Unknown' }} - Rp {{ formatCurrency(rate?.price_per_hour || 0) }}/jam
                </option>
              </template>
              <option v-else disabled>Memuat tarif...</option>
            </select>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl">
          <button
            type="button"
            @click="handleClose"
            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading || !formData.customer_name || !formData.rate_id || formData.is_open_billing === null || (formData.is_open_billing === false && !formData.duration_minutes)"
            class="flex-1 px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
             {{ formData.is_open_billing === true ? 'Mulai Sesi' : (formData.is_open_billing === false ? 'Lanjut' : 'Pilih Jenis Billing') }}
          </button>
        </div>
      </form>

      <!-- Step 2: Payment Choice -->
      <div v-if="step === 2" class="p-6 text-center space-y-6">
        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center dark:bg-green-500/20">
          <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        
        <div>
          <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Konfirmasi Sesi</h4>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Sesi belum dimulai. Kapan pelanggan akan membayar?
          </p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <button
            @click="handlePayLater"
            :disabled="loading"
            class="px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition"
          >
             {{ loading ? '...' : 'Bayar Nanti' }}
          </button>
          <button
            @click="handlePayNow"
            :disabled="loading"
            class="px-4 py-3 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-500/30"
          >
             {{ loading ? 'Memproses...' : 'Bayar Sekarang' }}
          </button>
        </div>
         <button
            @click="step = 1"
            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mt-4 underline"
          >
            Kembali
          </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const props = defineProps({
  show: Boolean,
  table: Object,
})

const emit = defineEmits(['close', 'success', 'start-payment'])

const notify = useNotificationStore()
const loading = ref(false)
const rates = ref([])
const step = ref(1)
const createdSession = ref(null)

const formData = ref({
  customer_name: '',
  rate_id: '',
  duration_minutes: null,
  is_open_billing: null, // No default selection
  auto_stop: true,
})

const fetchRates = async () => {
    try {
        const response = await axios.get('/api/rates', { params: { all: 1 } })
        if (response.data.success) {
            // Filter out nulls just in case
            rates.value = Array.isArray(response.data.data) 
                ? response.data.data.filter(r => r != null) 
                : []
            
            // Auto select first rate
            if (rates.value.length > 0 && rates.value[0]) {
                formData.value.rate_id = rates.value[0].id
            }
        }
    } catch (e) {
        console.error('Failed to fetch rates', e)
        notify.error('Gagal memuat paket rate')
    }
}

onMounted(() => {
    fetchRates()
})

watch(() => props.show, (val) => {
    if (val) {
        step.value = 1
        createdSession.value = null
        formData.value = {
            customer_name: '',
            rate_id: '',
            duration_minutes: null,
            is_open_billing: null,
            auto_stop: true,
        }
    }
})

const handleNextStep = async () => {
    // If Open Billing, call start session immediately (Step 1 -> End)
    if (formData.value.is_open_billing) {
        await executeStartSession()
    } else {
        // If Packet, go to Step 2
        step.value = 2
    }
}

const executeStartSession = async () => {
    loading.value = true
    try {
        const payload = {
            table_id: props.table.id,
            ...formData.value
        }
        
        const response = await axios.post('/api/sessions/start', payload)
        
        if (response.data.success) {
            createdSession.value = response.data.data
            
            if (formData.value.is_open_billing) {
                notify.success('Sesi berhasil dimulai')
                emit('success')
                handleClose()
            } else {
                return response.data.data // Return session data for Step 2 usage
            }
        }
    } catch (error) {
        notify.error(error.response?.data?.message || 'Gagal memulai sesi')
    } finally {
        loading.value = false
    }
}

const handlePayLater = async () => {
    loading.value = true
    try {
        const payload = {
            table_id: props.table.id,
            ...formData.value
        }
        const response = await axios.post('/api/sessions/start', payload)
        if (response.data.success) {
             notify.success('Sesi berhasil dimulai (Bayar Nanti)')
             emit('success')
             handleClose()
        }
    } catch (error) {
        notify.error(error.response?.data?.message || 'Gagal memulai sesi')
    } finally {
        loading.value = false
    }
}

const handlePayNow = () => {
    // Emit data for parent to handle (Open Payment Dialog -> Then Start Session)
    const selectedRate = rates.value.find(r => r.id === formData.value.rate_id)
    const price = selectedRate ? Math.ceil((formData.value.duration_minutes / 60) * selectedRate.price_per_hour) : 0

    emit('start-payment', {
        ...formData.value,
        total_price: price,
        table_number: props.table.table_number,
        is_create_session: true // Flag to tell parent this is a new session
    })
    handleClose()
}

const handleClose = () => {
  emit('close')
  step.value = 1
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID').format(val)
}
</script>
