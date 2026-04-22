import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useWalletStore = defineStore('walletStore', () => {

    const balance = ref(0)
    const isLoading = ref(false)

    // Refresca el balance desde la API (/api/user devuelve wallet actualizado)
    async function fetchBalance() {
        try {
            const res = await axios.get('/api/user')
            balance.value = res.data.data?.wallet ?? 0
        } catch {
            // Si falla el fetch no reseteamos el valor actual
        }
    }

    // Depositar euros → el backend convierte a fichas y actualiza users.wallet
    async function depositar(userId, cantidad) {
        isLoading.value = true
        try {
            await axios.post('/api/transacciones', {
                user_id: userId,
                tipo: 'deposito',
                cantidad,
            })
            await fetchBalance()
        } finally {
            isLoading.value = false
        }
    }

    // Retirar euros → el backend convierte a fichas y actualiza users.wallet
    async function retirar(userId, cantidad) {
        isLoading.value = true
        try {
            await axios.post('/api/transacciones', {
                user_id: userId,
                tipo: 'retirada',
                cantidad,
            })
            await fetchBalance()
        } finally {
            isLoading.value = false
        }
    }

    // Sincronización directa desde el polling del juego (evita un fetch extra)
    function syncFromGame(walletValue) {
        if (walletValue !== undefined && walletValue !== null) {
            balance.value = walletValue
        }
    }

    // Resetear al hacer logout
    function reset() {
        balance.value = 0
    }

    return { balance, isLoading, fetchBalance, depositar, retirar, syncFromGame, reset }

}, { persist: true })
