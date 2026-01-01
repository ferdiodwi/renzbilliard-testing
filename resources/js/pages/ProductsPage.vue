<template>
  <div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Manajemen Produk</h1>
      <button
        @click="showDialog = true; editingProduct = null"
        class="w-full sm:w-auto px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition text-sm"
      >
        + Tambah Produk
      </button>
    </div>

    <!-- Rows per page, Filter & Search -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between">
      <!-- Rows per page selector -->
      <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
        <span>Tampilkan</span>
        <select
          v-model="perPage"
          class="px-3 py-1.5 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>baris</span>
      </div>

      <!-- Right side: Filter & Search -->
      <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
        <!-- Category filter -->
        <select
          v-model="filterCategory"
          class="w-full md:w-auto px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        
        <!-- Search input -->
        <div class="relative w-full md:w-96">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama produk..."
            class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
          />
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[640px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Nama</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Kategori</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Harga</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Stok</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Status</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(product, index) in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ (pagination.meta.from || 1) + index }}
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ product.name }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span 
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :style="{ backgroundColor: product.category?.color + '20', color: product.category?.color }"
              >
                {{ product.category?.name || '-' }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">Rp {{ Number(product.price).toLocaleString() }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">
              {{ product.stock !== null ? product.stock : 'Unlimited' }}
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="product.is_available ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'"
              >
                {{ product.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <div class="flex gap-2">
                <button
                  @click="handleEdit(product)"
                  class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(product)"
                  class="px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination 
        v-if="pagination.meta.total > 0"
        :links="pagination.links"
        :meta="pagination.meta"
        @page-change="handlePageChange"
        class="mt-4"
    />

    <!-- Product Dialog -->
    <div
      v-if="showDialog"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="showDialog = false"
    >
      <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          {{ editingProduct ? 'Edit Produk' : 'Tambah Produk' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
            <select
              v-model="form.category_id"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            >
              <option :value="null" disabled>Pilih Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gambar Produk (opsional)</label>
            <input
              ref="imageInput"
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              @change="handleImageChange"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900 dark:file:text-brand-300"
            />
            <div v-if="imagePreview" class="mt-3">
              <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600" />
              <button type="button" @click="clearImage" class="mt-2 text-xs text-red-600 hover:text-red-700 dark:text-red-400">Hapus gambar</button>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: JPG, PNG, WebP. Max: 2MB</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Harga</label>
            <input
              v-model.number="form.price"
              type="number"
              min="0"
              step="100"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stok</label>
            <input
              v-model.number="form.stock"
              type="number"
              min="0"
              :disabled="unlimitedStock"
              :required="!unlimitedStock"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
              :placeholder="unlimitedStock ? 'Unlimited' : 'Masukkan jumlah stok'"
            />
            <div class="mt-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="unlimitedStock"
                  type="checkbox"
                  class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500"
                />
                <span class="text-sm text-gray-600 dark:text-gray-400">Stok Unlimited</span>
              </label>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="form.is_available"
              type="checkbox"
              id="available"
              class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500"
            />
            <label for="available" class="text-sm font-medium text-gray-700 dark:text-gray-300">Tersedia</label>
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
              @click="showDialog = false"
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'
import Pagination from '@/components/ui/Pagination.vue'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const products = ref([])
const categories = ref([])
const pagination = ref({ links: [], meta: {} })
const loading = ref(false)
const showDialog = ref(false)
const editingProduct = ref(null)
const filterCategory = ref('')
const searchQuery = ref('')
const perPage = ref(10)
const currentPage = ref(1)
const imageInput = ref(null)
const imagePreview = ref(null)
const imageFile = ref(null)
const removeImage = ref(false)
const unlimitedStock = ref(false)

const form = ref({
  name: '',
  category_id: null,
  price: 0,
  stock: null,
  is_available: true,
})

const filteredProducts = computed(() => {
  // If we are filtering, we might typically do it on backend or frontend. 
  // Since we paginate, backend filtering is better.
  // But for now, if the user requested simple pagination, let's assume backend returns paginated data.
  // If we filter locally on paginated data it will be weird (only filtering current page).
  // So ideally we pass filters to the API.
  return products.value
})

const fetchProducts = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const response = await axios.get('/api/products', {
        params: { 
            page,
            category: filterCategory.value || undefined,
            search: searchQuery.value || undefined,
            per_page: perPage.value
        }
    })
    products.value = response.data.data.data
    pagination.value = {
        links: response.data.data.links,
        meta: {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            from: response.data.data.from,
            to: response.data.data.to,
            total: response.data.data.total
        }
    }
  } catch (error) {
    console.error('Failed to fetch products:', error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories')
    categories.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

// Watch category change to refetch
import { watch } from 'vue'
watch(filterCategory, () => {
    fetchProducts(1)
})

// Watch search query with debounce
let searchTimeout = null
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchProducts(1) // Reset to page 1 on search
  }, 500) // 500ms debounce
})

// Watch perPage changes
watch(perPage, () => {
  fetchProducts(1) // Reset to page 1 when changing rows per page
})

const handlePageChange = (page) => {
    fetchProducts(page)
}

const handleEdit = (product) => {
  editingProduct.value = product
  form.value = {
    name: product.name,
    category_id: product.category_id,
    price: product.price, 
    stock: product.stock,
    is_available: product.is_available,
  }
  
  // Set unlimited stock checkbox
  unlimitedStock.value = product.stock === null
  
  // Show existing image if available
  if (product.image) {
    imagePreview.value = `/images/products/${product.image}`
  } else {
    imagePreview.value = null
  }
  imageFile.value = null
  removeImage.value = false
  
  showDialog.value = true
}

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
      notify.error('Ukuran gambar maksimal 2MB')
      return
    }
    
    imageFile.value = file
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const clearImage = () => {
  imageFile.value = null
  imagePreview.value = null
  removeImage.value = true  // Flag to remove image from database
  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('category_id', form.value.category_id)
    formData.append('price', form.value.price)
    
    // Handle stock: empty string if unlimited (backend will set to null), otherwise the number
    if (unlimitedStock.value) {
      formData.append('stock', '')  // Send empty string, backend will convert to null
    } else if (form.value.stock !== null && form.value.stock !== '') {
      formData.append('stock', form.value.stock)
    }
    
    formData.append('is_available', form.value.is_available ? '1' : '0')
    
    // Handle image
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    } else if (removeImage.value) {
      // User clicked "Hapus gambar" - send flag to delete image
      formData.append('remove_image', '1')
    }
    
    if (editingProduct.value) {
      formData.append('_method', 'PUT')
      await axios.post(`/api/products/${editingProduct.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      notify.success('Product updated successfully!')
    } else {
      await axios.post('/api/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      notify.success('Product created successfully!')
    }
    
    showDialog.value = false
    resetForm()
    fetchProducts(currentPage.value)
  } catch (error) {
    console.error('Failed to save product:', error)
    notify.error(error.response?.data?.message || 'Failed to save product')
  } finally {
    loading.value = false
  }
}

const handleDelete = async (product) => {
  const confirmed = await confirm.show({
    title: 'Delete Product',
    message: `Are you sure you want to delete ${product.name}?`,
    confirmText: 'Delete',
    type: 'danger'
  })

  if (!confirmed) return

  try {
    await axios.delete(`/api/products/${product.id}`)
    notify.success('Product deleted successfully!')
    fetchProducts()
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to delete product')
  }
}

const resetForm = () => {
  editingProduct.value = null
  form.value = {
    name: '',
    category_id: null,
    price: 0,
    stock: null,
    is_available: true,
  }
  unlimitedStock.value = false
  clearImage()
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>
