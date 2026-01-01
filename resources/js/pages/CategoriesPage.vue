<template>
  <div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Kelola Kategori</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola kategori produk F&B</p>
      </div>
      <button
        @click="showDialog = true; editingCategory = null"
        class="w-full sm:w-auto px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition text-sm"
      >
        + Tambah Kategori
      </button>
    </div>

    <!-- Categories Table -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[640px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Nama</th>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Deskripsi</th>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Warna</th>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Produk</th>
            <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(cat, index) in categories" :key="cat.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ index + 1 }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-medium">{{ cat.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ cat.description || '-' }}</td>
            <td class="px-6 py-4">
              <span 
                class="px-3 py-1.5 text-xs font-semibold rounded-full"
                :style="{ backgroundColor: cat.color + '20', color: cat.color }"
              >
                {{ cat.name }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ cat.products_count || 0 }} produk
            </td>
            <td class="px-6 py-4">
              <div class="flex gap-2">
                <button
                  @click="handleEdit(cat)"
                  class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(cat)"
                  class="px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="categories.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
              Belum ada kategori
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Category Dialog -->
    <CategoryDialog
      :is-open="showDialog"
      :category="editingCategory"
      @close="showDialog = false; editingCategory = null"
      @saved="fetchCategories"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'
import CategoryDialog from '@/components/CategoryDialog.vue'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const categories = ref([])
const showDialog = ref(false)
const editingCategory = ref(null)

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories')
    console.log('Categories response:', response.data)
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch categories:', error)
    console.error('Error response:', error.response)
    notify.error('Gagal memuat kategori: ' + (error.message || 'Unknown error'))
    categories.value = []
  }
}

const handleEdit = (category) => {
  editingCategory.value = category
  showDialog.value = true
}

const handleDelete = async (category) => {
  const confirmed = await confirm.show({
    title: 'Hapus Kategori',
    message: `Apakah Anda yakin ingin menghapus kategori "${category.name}"? ${category.products_count > 0 ? `Kategori ini memiliki ${category.products_count} produk.` : ''}`,
    confirmText: 'Hapus',
    cancelText: 'Batal',
    type: 'danger'
  })
  
  if (!confirmed) return

  try {
    await axios.delete(`/api/categories/${category.id}`)
    notify.success('Kategori berhasil dihapus')
    fetchCategories()
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menghapus kategori'
    notify.error(message)
  }
}

onMounted(() => {
  fetchCategories()
})
</script>
