<template>
    <div class="transactions-container">

        <!-- Cabecera con saldo y botones -->
        <div class="datos-card mb-4">
            <h3 class="card-title"><span>TRANSACCIONES</span></h3>
            <div class="wallet-header">
                <div class="balance-info">
                    <span class="balance-label">SALDO ACTUAL</span>
                    <span class="balance-amount">
                        <img src="/img/ficha.png" class="chip-icon" alt="ficha" />
                        {{ balance.toLocaleString('es-ES') }} fichas
                        <span class="balance-euros">({{ conversionRate > 0 ? (balance / conversionRate).toFixed(2) : '0.00' }}€)</span>
                    </span>
                </div>
                <div class="action-buttons">
                    <button class="btn-deposit" @click="openDeposit">
                        <i class="pi pi-plus-circle mr-2"></i> Añadir Fondos
                    </button>
                    <button class="btn-withdraw" @click="openWithdraw">
                        <i class="pi pi-minus-circle mr-2"></i> Retirar Fondos
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerta de pago Redsys -->
        <div v-if="paymentStatus" class="payment-alert" :class="paymentStatus === 'ok' ? 'alert-success' : 'alert-error'">
            <i :class="paymentStatus === 'ok' ? 'pi pi-check-circle' : 'pi pi-times-circle'" class="mr-2"></i>
            <span v-if="paymentStatus === 'ok'">¡Pago completado correctamente! Tu saldo ha sido actualizado.</span>
            <span v-else-if="paymentStatus === 'ko'">El pago fue cancelado.</span>
            <span v-else>El pago no se pudo completar. Inténtalo de nuevo.</span>
        </div>

        <!-- Tabla de transacciones -->
        <div class="datos-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title mb-0"><span>HISTORIAL</span></h3>
                <button class="btn-refresh" @click="loadTransacciones" :disabled="loading">
                    <i class="pi pi-refresh" :class="{ 'spin': loading }"></i>
                </button>
            </div>

            <!-- Skeleton -->
            <div v-if="loading && !transacciones.length" class="skeleton-list">
                <div v-for="i in 5" :key="i" class="skeleton-row">
                    <div class="sk sk-sm"></div>
                    <div class="sk sk-md"></div>
                    <div class="sk sk-lg"></div>
                    <div class="sk sk-sm"></div>
                </div>
            </div>

            <!-- Tabla -->
            <div v-else-if="transacciones.length" class="table-wrapper">
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Importe (€)</th>
                            <th>Fichas</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tx in transacciones" :key="tx.id">
                            <td class="id-col">#{{ tx.id }}</td>
                            <td>
                                <span class="badge" :class="tx.tipo === 'deposito' ? 'badge-green' : 'badge-red'">
                                    {{ tx.tipo === 'deposito' ? 'Depósito' : 'Retirada' }}
                                </span>
                            </td>
                            <td :class="tx.tipo === 'deposito' ? 'amount-pos' : 'amount-neg'">
                                {{ tx.tipo === 'deposito' ? '+' : '-' }}{{ Number(tx.cantidad).toFixed(2) }}€
                            </td>
                            <td class="chips-col">
                                {{ tx.tipo === 'deposito' ? '+' : '-' }}{{ Math.round(tx.cantidad * conversionRate).toLocaleString('es-ES') }}
                            </td>
                            <td class="date-col">{{ formatDate(tx.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Vacío -->
            <div v-else class="empty-state">
                <i class="pi pi-wallet"></i>
                <p>No hay transacciones registradas todavía.</p>
            </div>
        </div>

        <!-- ============================================================
             MODAL: DEPOSITAR (Redsys)
        ============================================================ -->
        <Teleport to="body">
            <div v-if="showDeposit" class="modal-overlay" @click.self="showDeposit = false">
                <div class="modal-box">
                    <button class="modal-close" @click="showDeposit = false">
                        <i class="pi pi-times"></i>
                    </button>
                    <h2 class="modal-title"><span>Añadir Fondos</span></h2>

                    <div v-if="!redsysForm" class="modal-body">
                        <p class="modal-subtitle">
                            Serás redirigido a la pasarela segura de Redsys para completar el pago.
                        </p>

                        <div class="field-box">
                            <label>Importe a depositar (€)</label>
                            <input
                                v-model.number="depositAmount"
                                type="number"
                                min="1"
                                max="10000"
                                step="1"
                                class="input"
                                placeholder="Ej: 50"
                            />
                            <span class="hint" v-if="depositAmount > 0">
                                = {{ Math.round(depositAmount * conversionRate).toLocaleString('es-ES') }} fichas
                                <span class="hint-rate">(1€ = {{ conversionRate }} fichas)</span>
                            </span>
                            <span v-if="depositError" class="error-msg">{{ depositError }}</span>
                        </div>

                        <!-- Test card info -->
                        <div class="test-card-info">
                            <i class="pi pi-info-circle"></i>
                            <div>
                                <strong>Modo prueba Redsys</strong><br/>
                                Tarjeta: <code>4548 8120 4940 0004</code><br/>
                                Caducidad: cualquier fecha futura · CVV: <code>123</code>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn-cancel" @click="showDeposit = false">Cancelar</button>
                            <button class="btn-primary" @click="startDeposit" :disabled="depositLoading">
                                <i v-if="depositLoading" class="pi pi-spin pi-spinner mr-2"></i>
                                Pagar con Redsys
                            </button>
                        </div>
                    </div>

                    <!-- Hidden form that auto-submits to Redsys -->
                    <div v-if="redsysForm" class="modal-body text-center">
                        <i class="pi pi-spin pi-spinner text-3xl mb-3" style="color:#9C5CCB"></i>
                        <p>Redirigiendo a Redsys…</p>
                        <form :id="'redsys-form'" :action="redsysForm.tpv_url" method="POST" ref="redsysFormEl">
                            <input type="hidden" name="Ds_SignatureVersion"   :value="redsysForm.version" />
                            <input type="hidden" name="Ds_MerchantParameters" :value="redsysForm.params" />
                            <input type="hidden" name="Ds_Signature"          :value="redsysForm.signature" />
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ============================================================
             MODAL: RETIRAR
        ============================================================ -->
        <Teleport to="body">
            <div v-if="showWithdraw" class="modal-overlay" @click.self="showWithdraw = false">
                <div class="modal-box">
                    <button class="modal-close" @click="showWithdraw = false">
                        <i class="pi pi-times"></i>
                    </button>
                    <h2 class="modal-title"><span>Retirar Fondos</span></h2>

                    <div class="modal-body">
                        <p class="modal-subtitle">
                            Indica la cantidad y los datos bancarios a los que se realizará la transferencia.
                        </p>

                        <div class="field-box">
                            <label>Fichas a retirar</label>
                            <input
                                v-model.number="withdrawChips"
                                type="number"
                                min="1"
                                :max="balance"
                                class="input"
                                placeholder="Ej: 500"
                            />
                            <span class="hint" v-if="withdrawChips > 0">
                                = {{ (withdrawChips / conversionRate).toFixed(2) }}€
                                <span class="hint-rate">({{ conversionRate }} fichas = 1€)</span>
                            </span>
                            <span v-if="withdrawChips > balance" class="error-msg">
                                Saldo insuficiente (tienes {{ balance.toLocaleString('es-ES') }} fichas)
                            </span>
                        </div>

                        <hr class="divider" />
                        <p class="section-label">Datos de la tarjeta / cuenta</p>

                        <div class="grid-2">
                            <div class="field-box col-span-2">
                                <label>Titular</label>
                                <input v-model="withdraw.titular" class="input" placeholder="Nombre completo" />
                            </div>
                            <div class="field-box col-span-2">
                                <label>IBAN / Número de cuenta</label>
                                <input
                                    v-model="withdraw.iban"
                                    class="input"
                                    placeholder="ES00 0000 0000 0000 0000 0000"
                                    maxlength="34"
                                />
                            </div>
                            <div class="field-box">
                                <label>Número de tarjeta</label>
                                <input
                                    v-model="withdraw.cardNumber"
                                    class="input"
                                    placeholder="•••• •••• •••• ••••"
                                    maxlength="19"
                                    @input="formatCardNumber"
                                />
                            </div>
                            <div class="field-box">
                                <label>Caducidad (MM/AA)</label>
                                <input
                                    v-model="withdraw.expiry"
                                    class="input"
                                    placeholder="MM/AA"
                                    maxlength="5"
                                    @input="formatExpiry"
                                />
                            </div>
                        </div>

                        <span v-if="withdrawError" class="error-msg mt-2 block">{{ withdrawError }}</span>

                        <div class="modal-footer">
                            <button class="btn-cancel" @click="showWithdraw = false">Cancelar</button>
                            <button
                                class="btn-primary"
                                @click="submitWithdraw"
                                :disabled="withdrawLoading || withdrawChips > balance || withdrawChips <= 0"
                            >
                                <i v-if="withdrawLoading" class="pi pi-spin pi-spinner mr-2"></i>
                                Solicitar retirada
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { authStore } from '@/store/auth'
import { useWalletStore } from '@/store/wallet'

const auth        = authStore()
const walletStore = useWalletStore()
const route       = useRoute()

const balance        = computed(() => walletStore.balance)
const transacciones  = ref([])
const loading        = ref(false)
const conversionRate = ref(100)   // fallback; se carga desde API

// Estado de pago Redsys en URL (?payment=ok|ko|error)
const paymentStatus = ref(null)

// ---- Depósito ----
const showDeposit    = ref(false)
const depositAmount  = ref(50)
const depositError   = ref('')
const depositLoading = ref(false)
const redsysForm     = ref(null)
const redsysFormEl   = ref(null)

// ---- Retirada ----
const showWithdraw    = ref(false)
const withdrawChips   = ref(0)
const withdrawError   = ref('')
const withdrawLoading = ref(false)
const withdraw = reactive({
    titular: '',
    iban: '',
    cardNumber: '',
    expiry: '',
})

// -----------------------------------------------------------------------

async function loadConversionRate() {
    try {
        const res = await axios.get('/api/ajustes')
        const list = Array.isArray(res.data) ? res.data : (res.data.data ?? [])
        const entry = list.find(a => a.clave === '1 euro')
        if (entry && Number(entry.valor) > 0) conversionRate.value = Number(entry.valor)
    } catch {}
}

async function loadTransacciones() {
    loading.value = true
    try {
        const res = await axios.get('/api/mis-transacciones')
        transacciones.value = Array.isArray(res.data) ? res.data : (res.data.data ?? [])
    } catch {
        transacciones.value = []
    } finally {
        loading.value = false
    }
}

function formatDate(dateString) {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

// -----------------------------------------------------------------------
// Depósito: Redsys

function openDeposit() {
    depositAmount.value = 50
    depositError.value  = ''
    redsysForm.value    = null
    showDeposit.value   = true
}

async function startDeposit() {
    depositError.value = ''
    if (!depositAmount.value || depositAmount.value < 1) {
        depositError.value = 'Introduce un importe válido (mínimo 1€).'
        return
    }
    depositLoading.value = true
    try {
        const res = await axios.post('/api/redsys/create-payment', {
            cantidad: depositAmount.value,
        })
        redsysForm.value = res.data
        await nextTick()
        redsysFormEl.value?.submit()
    } catch (e) {
        depositError.value = e.response?.data?.message ?? 'Error al iniciar el pago.'
        depositLoading.value = false
    }
}

// -----------------------------------------------------------------------
// Retirada

function openWithdraw() {
    withdrawChips.value  = 0
    withdrawError.value  = ''
    Object.assign(withdraw, { titular: '', iban: '', cardNumber: '', expiry: '' })
    showWithdraw.value   = true
}

function formatCardNumber(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 16)
    withdraw.cardNumber = v.replace(/(.{4})/g, '$1 ').trim()
}

function formatExpiry(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 4)
    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2)
    withdraw.expiry = v
}

async function submitWithdraw() {
    withdrawError.value = ''

    if (withdrawChips.value <= 0) {
        withdrawError.value = 'Introduce una cantidad de fichas mayor a 0.'
        return
    }
    if (withdrawChips.value > balance.value) {
        withdrawError.value = 'Saldo insuficiente.'
        return
    }
    if (!withdraw.titular.trim()) {
        withdrawError.value = 'El titular es obligatorio.'
        return
    }
    if (!withdraw.iban.trim() && !withdraw.cardNumber.trim()) {
        withdrawError.value = 'Indica un IBAN o número de tarjeta.'
        return
    }

    withdrawLoading.value = true
    const euros = withdrawChips.value / conversionRate.value

    try {
        await axios.post('/api/transacciones', {
            user_id:  auth.user.id,
            tipo:     'retirada',
            cantidad: Number(euros.toFixed(2)),
        })
        await walletStore.fetchBalance()
        await loadTransacciones()
        showWithdraw.value = false
    } catch (e) {
        withdrawError.value = e.response?.data?.message ?? 'Error al procesar la retirada.'
    } finally {
        withdrawLoading.value = false
    }
}

// -----------------------------------------------------------------------

onMounted(async () => {
    // Detectar resultado de pago Redsys en URL
    const p = route.query.payment
    if (p === 'ok' || p === 'ko' || p === 'error') {
        paymentStatus.value = p
        setTimeout(() => { paymentStatus.value = null }, 6000)
    }

    await Promise.all([loadConversionRate(), loadTransacciones(), walletStore.fetchBalance()])
})
</script>

<style scoped>
/* ---- Layout ---- */
.transactions-container {
    padding-left: 50px;
    padding-right: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-height: 820px;
}

.datos-card {
    background: #12122a;
    border-radius: 10px;
    padding: 24px;
    border: 1px solid rgba(156, 92, 203, 0.25);
}

.card-title {
    display: block;
    margin-bottom: 20px;
    padding-bottom: 12px;
    position: relative;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-size: 13px;
    font-weight: 600;
}
.card-title.mb-0 { margin-bottom: 0; }
.card-title span {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.card-title::after {
    content: "";
    position: absolute;
    left: 0; bottom: 0;
    width: 100%; height: 1px;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
}

/* ---- Wallet header ---- */
.wallet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.balance-info { display: flex; flex-direction: column; gap: 4px; }
.balance-label {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    letter-spacing: 1px;
    text-transform: uppercase;
}
.balance-amount {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chip-icon { width: 22px; height: 22px; }
.balance-euros {
    font-size: 14px;
    color: rgba(255,255,255,0.4);
    font-weight: 400;
}
.action-buttons { display: flex; gap: 12px; flex-wrap: wrap; }

.btn-deposit, .btn-withdraw {
    display: inline-flex;
    align-items: center;
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-deposit {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    color: #fff;
}
.btn-withdraw {
    background: transparent;
    border: 1px solid rgba(156,92,203,0.5);
    color: rgba(255,255,255,0.7);
}
.btn-deposit:hover, .btn-withdraw:hover { opacity: 0.85; }

.btn-refresh {
    background: transparent;
    border: 1px solid rgba(129,138,200,0.3);
    color: rgba(255,255,255,0.5);
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    transition: color 0.2s;
}
.btn-refresh:hover { color: #fff; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ---- Payment alert ---- */
.payment-alert {
    border-radius: 8px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
}
.alert-success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: #6ee7b7; }
.alert-error   { background: rgba(239,68,68,0.15);  border: 1px solid rgba(239,68,68,0.4);  color: #fca5a5; }

/* ---- Table ---- */
.table-wrapper { overflow-x: auto; }
.tx-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.tx-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.35);
    border-bottom: 1px solid rgba(129,138,200,0.15);
}
.tx-table td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(129,138,200,0.08);
    color: rgba(255,255,255,0.75);
}
.tx-table tr:last-child td { border-bottom: none; }
.tx-table tr:hover td { background: rgba(156,92,203,0.04); }
.id-col  { font-family: monospace; font-size: 12px; color: rgba(255,255,255,0.3); }
.date-col { font-size: 12px; color: rgba(255,255,255,0.4); }
.chips-col { font-family: monospace; }
.amount-pos { color: #6ee7b7; font-weight: 700; }
.amount-neg { color: #fca5a5; font-weight: 700; }

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.badge-green { background: rgba(16,185,129,0.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
.badge-red   { background: rgba(239,68,68,0.12);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }

/* ---- Empty / skeleton ---- */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 0;
    color: rgba(255,255,255,0.2);
    font-size: 14px;
    gap: 10px;
}
.empty-state i { font-size: 36px; }

.skeleton-list { display: flex; flex-direction: column; gap: 10px; }
.skeleton-row  { display: flex; gap: 12px; align-items: center; }
.sk { background: rgba(129,138,200,0.1); border-radius: 4px; height: 18px; animation: pulse 1.5s ease infinite; }
.sk-sm  { width: 60px; }
.sk-md  { width: 120px; }
.sk-lg  { width: 180px; }
@keyframes pulse { 0%,100%{opacity:0.5} 50%{opacity:1} }

/* ---- Modals ---- */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,8,0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
}
.modal-box {
    background: #12122a;
    border: 1px solid rgba(156,92,203,0.35);
    border-radius: 14px;
    width: 100%;
    max-width: 480px;
    position: relative;
    padding: 32px 28px 24px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6);
}
.modal-close {
    position: absolute;
    top: 16px; right: 16px;
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.4);
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}
.modal-close:hover { color: #fff; }
.modal-title {
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(156,92,203,0.2);
}
.modal-title span {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.modal-subtitle {
    font-size: 13px;
    color: rgba(255,255,255,0.45);
    margin-bottom: 20px;
    line-height: 1.6;
}
.modal-body { display: flex; flex-direction: column; gap: 16px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
}

/* ---- Form fields ---- */
.field-box { display: flex; flex-direction: column; gap: 6px; }
.field-box label {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    letter-spacing: 1px;
    text-transform: uppercase;
}
.input {
    width: 100%;
    background: #0e0e22;
    border: 1px solid rgba(129,138,200,0.3);
    color: #fff;
    padding: 9px 12px;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}
.input:focus {
    outline: none;
    border-color: #9C5CCB;
    box-shadow: 0 0 0 2px rgba(156,92,203,0.15);
}
.hint {
    font-size: 12px;
    color: rgba(255,255,255,0.35);
}
.hint-rate { color: rgba(156,92,203,0.7); }
.error-msg { font-size: 12px; color: #fca5a5; }
.section-label {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin: 0;
}
.divider { border: none; border-top: 1px solid rgba(129,138,200,0.12); margin: 4px 0; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.col-span-2 { grid-column: span 2; }

/* ---- Redsys test card info ---- */
.test-card-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(59,195,219,0.08);
    border: 1px solid rgba(59,195,219,0.2);
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 12px;
    color: rgba(255,255,255,0.55);
    line-height: 1.7;
}
.test-card-info i { color: #3BC3DB; font-size: 16px; margin-top: 2px; flex-shrink: 0; }
.test-card-info code {
    font-family: monospace;
    background: rgba(59,195,219,0.12);
    padding: 1px 5px;
    border-radius: 3px;
    color: #3BC3DB;
}

/* ---- Buttons ---- */
.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 10px 24px;
    border-radius: 50px;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB);
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-cancel {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: 50px;
    background: transparent;
    border: 1px solid rgba(129,138,200,0.3);
    color: rgba(255,255,255,0.5);
    font-size: 13px;
    cursor: pointer;
}
.btn-cancel:hover { color: #fff; border-color: rgba(129,138,200,0.6); }

@media (max-width: 640px) {
    .transactions-container { padding-left: 16px; padding-right: 16px; }
    .wallet-header { flex-direction: column; align-items: flex-start; }
    .grid-2 { grid-template-columns: 1fr; }
    .col-span-2 { grid-column: span 1; }
}
</style>
