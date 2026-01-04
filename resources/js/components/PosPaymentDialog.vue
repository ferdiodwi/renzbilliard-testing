<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div class="w-full sm:max-w-md max-h-[90vh] overflow-hidden bg-white shadow-2xl rounded-t-2xl sm:rounded-2xl dark:bg-gray-800 flex flex-col">
      <!-- Header -->
      <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            Pembayaran Pesanan
          </h3>
          <button
            @click="closeDialog"
            class="p-1 text-gray-400 transition rounded-lg hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="px-4 sm:px-6 py-3 sm:py-4 space-y-3 sm:space-y-4 overflow-y-auto flex-1">
        <!-- Order Summary -->
        <div v-if="cartData" class="space-y-4">
          <!-- Order Items Detail -->
          <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900">
            <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Pesanan</h4>
            <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
              <div v-for="(item, index) in cartData.items" :key="index" class="flex justify-between text-sm">
                <div class="flex-1">
                  <span class="text-gray-800 dark:text-white">{{ item.name }}</span>
                  <span class="ml-2 text-gray-500 dark:text-gray-400">×{{ item.quantity }}</span>
                </div>
                <span class="font-medium text-gray-800 dark:text-white">
                  Rp {{ formatCurrency(item.subtotal) }}
                </span>
              </div>
            </div>
            <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
              <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Customer</span>
                <span>{{ cartData.customer_name || 'Pelanggan Umum' }}</span>
              </div>
            </div>
          </div>

          <!-- Grand Total -->
          <div class="p-4 rounded-lg bg-brand-50 dark:bg-brand-500/10 border-2 border-brand-500">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-800 dark:text-white">TOTAL BAYAR</span>
              <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">
                Rp {{ formatCurrency(cartData.total) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div>
          <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
            Metode Pembayaran<span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="method in paymentMethods"
              :key="method.value"
              type="button"
              @click="selectedMethod = method.value"
              class="flex flex-col items-center gap-2 p-4 transition border-2 rounded-xl"
              :class="selectedMethod === method.value
                ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10'
                : 'border-gray-300 bg-white hover:border-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:border-gray-600'"
            >
              <span class="text-2xl">{{ method.icon }}</span>
              <span class="text-xs font-medium text-gray-800 dark:text-white">
                {{ method.label }}
              </span>
            </button>
          </div>
        </div>

        <!-- Cash Input (if payment method is cash) -->
        <div v-if="selectedMethod === 'cash'" class="space-y-3">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Uang Dibayar
            </label>
            <input
              :value="formatInputCurrency(cashPaid)"
              @input="handleCashInput"
              type="text"
              inputmode="numeric"
              placeholder="Masukkan jumlah uang"
              class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-left text-lg font-semibold"
            />
          </div>
          <div v-if="cashPaid >= (cartData?.total || 0)" class="p-3 rounded-lg bg-success-50 dark:bg-success-500/10">
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-700 dark:text-gray-300">Kembalian</span>
              <span class="font-bold text-success-600 dark:text-success-400">
                Rp {{ formatCurrency(cashPaid - (cartData?.total || 0)) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-2">
          <button
            type="button"
            @click="closeDialog"
            class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
          >
            Batal
          </button>
          <button
            @click="handlePayment"
            :disabled="loading || !selectedMethod || (selectedMethod === 'cash' && cashPaid < (cartData?.total || 0))"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-success-500 hover:bg-success-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Memproses...' : 'Bayar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useOrderStore } from '@/stores/order'

const notify = useNotificationStore()
const orderStore = useOrderStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  cartData: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const selectedMethod = ref('cash')
const cashPaid = ref(0)

// Format number with dots for display (e.g., 50000 -> 50.000)
const formatInputCurrency = (value) => {
  if (!value || value === 0) return ''
  return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

// Handle input and parse formatted string back to number
const handleCashInput = (event) => {
  const rawValue = event.target.value.replace(/\D/g, '')
  cashPaid.value = parseInt(rawValue) || 0
}

const paymentMethods = [
  { value: 'cash', label: 'Tunai', icon: '💵' },
  { value: 'qris', label: 'QRIS', icon: '📱' },
]

const handlePayment = async () => {
  if (!props.cartData || !selectedMethod.value) return

  loading.value = true
  try {
    // Step 1: Create order
    const orderData = {
      customer_name: props.cartData.customer_name,
      session_id: null,
      items: props.cartData.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
    }

    const orderResponse = await axios.post('/api/pos/orders', orderData)
    
    if (!orderResponse.data.success) {
      throw new Error('Gagal membuat order')
    }

    const createdOrder = orderResponse.data.data

    // Step 2: Pay the order immediately
    const payResponse = await axios.post(`/api/pos/orders/${createdOrder.id}/pay`, {
      payment_method: selectedMethod.value,
    })

    if (payResponse.data.success) {
      const change = selectedMethod.value === 'cash' ? cashPaid.value - props.cartData.total : 0
      
      notify.success(`Pembayaran berhasil! Invoice: ${payResponse.data.data.invoice_number}`)
      orderStore.fetchPendingCount()
      
      if (change > 0) {
        notify.info(`Kembalian: Rp ${formatCurrency(change)}`, 'Kembalian', 5000)
      }
      
      emit('success', payResponse.data.data)
      closeDialog()
    }
  } catch (error) {
    const message = error.response?.data?.message || error.message || 'Gagal memproses pembayaran'
    notify.error(message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  selectedMethod.value = 'cash'
  cashPaid.value = 0
  emit('close')
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

watch(() => props.cartData, (newCart) => {
  if (newCart) {
    cashPaid.value = 0
  }
}, { immediate: true })
</script>
