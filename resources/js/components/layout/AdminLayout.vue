<template>
  <div class="min-h-screen xl:flex bg-white dark:bg-gray-950">
    <app-sidebar />
    <Backdrop />
    <div
      class="flex-1 transition-all duration-300 ease-in-out bg-gray-50 dark:bg-gray-950"
      :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
    >
      <app-header ref="appHeader" />
      <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <slot></slot>
      </div>
    </div>
    <ConfirmDialog />
    <ToastNotification />
  </div>
</template>

<script setup>
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import ToastNotification from '@/components/ui/ToastNotification.vue'
import { useSidebar } from '@/composables/useSidebar'
import Backdrop from './Backdrop.vue'
import { ref } from 'vue'

const { isExpanded, isHovered } = useSidebar()
const appHeader = ref(null)
</script>
