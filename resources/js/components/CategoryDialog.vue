<template>
  <div>
    <!-- Dialog overlay -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="emit('close')"
    >
      <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          {{ category ? 'Edit Kategori' : 'Tambah Kategori' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Kategori</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
              placeholder="Contoh: Makanan, Minuman"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
              placeholder="Deskripsi kategori (opsional)"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warna Badge</label>
            <div class="flex gap-3 items-center">
              <input
                v-model="form.color"
                type="color"
                class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
              />
              <div 
                class="px-3 py-1.5 text-xs font-semibold rounded-full"
                :style="{ backgroundColor: form.color + '20', color: form.color }"
              >
                Preview Badge
              </div>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg disabled:opacity-50"
            >
              {{ loading ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button
              type="button"
              @click="emit('close')"
              class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const props = defineProps({
  isOpen: Boolean,
  category: Object
})

const emit = defineEmits(['close', 'saved'])
const notify = useNotificationStore()
const loading = ref(false)

const form = ref({
  name: '',
  description: '',
  color: '#3b82f6'
})

watch(() => props.category, (newCategory) => {
  if (newCategory) {
    form.value = {
      name: newCategory.name,
      description: newCategory.description || '',
      color: newCategory.color || '#3b82f6'
    }
  } else {
    form.value = {
      name: '',
      description: '',
      color: '#3b82f6'
    }
  }
}, { immediate: true })

const handleSubmit = async () => {
  loading.value = true
  try {
    if (props.category) {
      await axios.put(`/api/categories/${props.category.id}`, form.value)
      notify.success('Kategori berhasil diperbarui')
    } else {
      await axios.post('/api/categories', form.value)
      notify.success('Kategori berhasil ditambahkan')
    }
    emit('saved')
    emit('close')
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menyimpan kategori'
    notify.error(message)
  } finally {
    loading.value = false
  }
}
</script>
