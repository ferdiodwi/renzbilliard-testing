<template>
  <ThemeProvider>
    <SidebarProvider>
      <RouterView />
      <ConfirmDialog />
      <ToastNotification />
    </SidebarProvider>
  </ThemeProvider>
</template>

<script setup>
import ThemeProvider from '@/components/layout/ThemeProvider.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import ToastNotification from '@/components/ui/ToastNotification.vue'
</script>
