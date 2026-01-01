import { defineStore } from 'pinia'

let nextId = 1

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: []
    }),

    actions: {
        success(message, title = 'Berhasil', duration = 4000) {
            this.add({ type: 'success', message, title, duration })
        },

        error(message, title = 'Gagal', duration = 5000) {
            this.add({ type: 'error', message, title, duration })
        },

        warning(message, title = 'Peringatan', duration = 4000) {
            this.add({ type: 'warning', message, title, duration })
        },

        info(message, title = 'Informasi', duration = 4000) {
            this.add({ type: 'info', message, title, duration })
        },

        add({ type, message, title, duration }) {
            const id = nextId++
            const notification = {
                id,
                type,
                message,
                title,
                duration
            }

            this.notifications.push(notification)

            // Auto-dismiss after duration
            if (duration > 0) {
                setTimeout(() => {
                    this.remove(id)
                }, duration)
            }
        },

        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id)
            if (index !== -1) {
                this.notifications.splice(index, 1)
            }
        },

        clear() {
            this.notifications = []
        }
    }
})
