import { defineStore } from 'pinia'
import axios from 'axios'

export const useOrderStore = defineStore('order', {
    state: () => ({
        pendingCount: 0,
        isFetching: false
    }),

    actions: {
        async fetchPendingCount() {
            // Prevent duplicate concurrent requests
            if (this.isFetching) return

            this.isFetching = true
            try {
                const response = await axios.get('/api/pos/orders', {
                    params: {
                        status: 'pending',
                        per_page: 1
                    }
                })
                this.pendingCount = response.data.meta?.total || 0
            } catch (error) {
                console.error('Failed to fetch pending orders count', error)
            } finally {
                this.isFetching = false
            }
        },

        incrementPendingCount() {
            this.pendingCount++
        },

        decrementPendingCount() {
            if (this.pendingCount > 0) this.pendingCount--
        }
    }
})
