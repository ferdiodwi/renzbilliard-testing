<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
    </div>

    <!-- Preferences Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
      <!-- Theme Setting -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tampilan</h3>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Mode Gelap</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Aktifkan tema gelap</p>
          </div>
          <button
            @click="toggleTheme"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
            :class="isDark ? 'bg-brand-500' : 'bg-gray-300'"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="isDark ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
      </div>


      <!-- Save Button -->
      <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="saveSettings"
          :disabled="loading"
          class="px-6 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition disabled:opacity-50"
        >
          {{ loading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'

const notify = useNotificationStore()

const isDark = ref(false)
const loading = ref(false)

const settings = ref({
  // Future settings can be added here
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const loadSettings = () => {
  // Load from localStorage
  const savedSettings = localStorage.getItem('userSettings')
  if (savedSettings) {
    settings.value = JSON.parse(savedSettings)
  }

  // Check theme
  isDark.value = localStorage.getItem('theme') === 'dark' || 
    (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
}

const saveSettings = () => {
  loading.value = true
  localStorage.setItem('userSettings', JSON.stringify(settings.value))
  
  setTimeout(() => {
    loading.value = false
    notify.success('Settings saved successfully!')
  }, 500)
}

onMounted(() => {
  loadSettings()
})
</script>
