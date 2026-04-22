<template>
    <div class="rules-page">
        <div class="container py-5">

            <div class="rules-header text-center mb-5">
                <h1 class="rules-title">REGLAS DEL BLACKJACK</h1>
                <p class="rules-subtitle">Todo lo que necesitas saber para jugar</p>
            </div>

            <!-- Objetivo -->
            <div class="rule-card mb-4">
                <div class="rule-card-header">
                    <span class="rule-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 112c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-112c0-13.3 10.7-24 24-24zm-32 224a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/>
                        </svg>
                    </span>
                    <h2>Objetivo</h2>
                </div>
                <p>Consigue un valor de cartas lo más cercano posible a <strong>21</strong> sin pasarte. Ganas si superas el valor del crupier sin exceder 21.</p>
            </div>

            <!-- Valor de las cartas -->
            <div class="rule-card mb-4">
                <div class="rule-card-header">
                    <span class="rule-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <path d="M0 48C0 21.5 21.5 0 48 0l0 48 0 393.4 130.1-92.9c8.3-6 19.6-6 27.9 0L336 441.4 336 48 48 48 48 0 336 0c26.5 0 48 21.5 48 48l0 440c0 9-5 17.2-13 21.3s-17.6 3.4-24.9-1.8L192 397.5 37.9 507.5c-7.3 5.2-16.9 5.9-24.9 1.8S0 497 0 488 0 48 0 48z"/>
                        </svg>
                    </span>
                    <h2>Valor de las cartas</h2>
                </div>
                <div class="card-values">
                    <div class="value-row">
                        <span class="value-label">2 — 10</span>
                        <span class="value-desc">Su valor numérico</span>
                    </div>
                    <div class="value-row">
                        <span class="value-label">J, Q, K</span>
                        <span class="value-desc">Valen 10 puntos</span>
                    </div>
                    <div class="value-row highlight">
                        <span class="value-label">As</span>
                        <span class="value-desc">Vale 1 u 11 (lo que más convenga)</span>
                    </div>
                </div>
            </div>

            <!-- Desarrollo de la partida -->
            <div class="rule-card mb-4">
                <div class="rule-card-header">
                    <span class="rule-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm395.3 11.3-112-112c-4.6-4.6-11.5-5.9-17.4-3.5s-9.9 8.3-9.9 14.8l0 224c0 6.5 3.8 12.5 9.9 14.8s12.9 1.1 17.4-3.5l112-112c6.2-6.2 6.2-16.4 0-22.6z"/>
                        </svg>
                    </span>
                    <h2>Desarrollo de la partida</h2>
                </div>
                <ol class="steps-list">
                    <li><strong>Apuesta:</strong> Cada jugador realiza su apuesta antes de recibir cartas.</li>
                    <li><strong>Reparto inicial:</strong> El crupier reparte 2 cartas a cada jugador. El crupier recibe 1 carta visible y 1 boca abajo.</li>
                    <li><strong>Turno del jugador:</strong> Decides si pedir carta (Hit) o plantarte (Stand).</li>
                    <li><strong>Turno del crupier:</strong> El crupier revela su carta oculta y debe pedir carta hasta llegar a 17 o más.</li>
                    <li><strong>Resolución:</strong> Se comparan puntuaciones y se pagan las apuestas.</li>
                </ol>
            </div>

            <!-- Acciones -->
            <div class="rule-card mb-4">
                <div class="rule-card-header">
                    <span class="rule-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M278.5 215.6L23 471c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l80-80c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-80 80c-28.1 28.1-73.7 28.1-101.8 0s-28.1-73.7 0-101.8L246 181.4c-10.4-27-7.4-57.8 11.8-82C282.5 68.4 322.9 58 360 65.4L312 113.4c-15.6 15.6-15.6 40.9 0 56.6s40.9 15.6 56.6 0l48-48c38-38 38-99.6 0-137.6S314.8-34.1 276.8 4l-.6 .6c-28.1 28.1-36.3 69-24 104.5c3.2 9.1 1.1 19.2-5.7 26.5z"/>
                        </svg>
                    </span>
                    <h2>Acciones disponibles</h2>
                </div>
                <div class="actions-grid">
                    <div class="action-item">
                        <span class="action-name">Hit</span>
                        <span class="action-desc">Pedir una carta más</span>
                    </div>
                    <div class="action-item">
                        <span class="action-name">Stand</span>
                        <span class="action-desc">Plantarse con las cartas actuales</span>
                    </div>
                    <div class="action-item">
                        <span class="action-name">Double Down</span>
                        <span class="action-desc">Doblar la apuesta y recibir solo una carta más</span>
                    </div>
                    <div class="action-item">
                        <span class="action-name">Split</span>
                        <span class="action-desc">Separar dos cartas iguales en dos manos</span>
                    </div>
                </div>
            </div>

            <!-- Resultados -->
            <div class="rule-card mb-4">
                <div class="rule-card-header">
                    <span class="rule-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                            <path d="M400 0H176c-26.5 0-48.1 21.8-47.1 48.2c.2 5.3 .4 10.6 .7 15.8H24C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7c44.3 43.1 98.3 64.8 138.1 75.8c23.4 6.5 39.4 26 39.4 45.6c0 20.9-17 37.9-37.9 37.9H192c-17.7 0-32 14.3-32 32s14.3 32 32 32l192 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-26.1 0C337 448 320 431 320 410.1c0-19.6 15.9-39.2 39.4-45.6c39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24H446.4c.3-5.2 .5-10.5 .7-15.8C448.1 21.8 426.5 0 400 0z"/>
                        </svg>
                    </span>
                    <h2>Resultados y pagos</h2>
                </div>
                <div class="results-list">
                    <div class="result-row win">
                        <span class="result-label">Victoria</span>
                        <span class="result-pay">Cobras 2x tu apuesta</span>
                    </div>
                    <div class="result-row blackjack">
                        <span class="result-label">Blackjack <small>(As + figura)</small></span>
                        <span class="result-pay">Cobras 2.5x tu apuesta</span>
                    </div>
                    <div class="result-row push">
                        <span class="result-label">Empate (Push)</span>
                        <span class="result-pay">Recuperas tu apuesta</span>
                    </div>
                    <div class="result-row lose">
                        <span class="result-label">Derrota / Bust</span>
                        <span class="result-pay">Pierdes tu apuesta</span>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center mt-5">
                <router-link to="/app/salas" class="btn-play">JUGAR AHORA</router-link>
            </div>

        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
.rules-page {
    min-height: 100vh;
    padding-top: 100px;
    color: white;
}

.rules-title {
    font-weight: 800;
    font-size: 2.5rem;
    text-transform: uppercase;
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.rules-subtitle {
    color: rgba(255, 255, 255, 0.6);
    font-size: 1.1rem;
    margin-top: 8px;
}

/* Cards */
.rule-card {
    background: rgba(96, 92, 92, 0.35);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 7px;
    padding: 28px 32px;
}

.rule-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
}

.rule-card-header h2 {
    font-size: 1.2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

.rule-icon svg {
    width: 22px;
    height: 22px;
    fill: #3BC3DB;
    flex-shrink: 0;
}

.rule-card p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    line-height: 1.7;
}

.rule-card strong {
    color: white;
}

/* Card values */
.card-values {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.value-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 10px 16px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.05);
}

.value-row.highlight {
    border-left: 3px solid #9C5CCB;
}

.value-label {
    font-weight: 700;
    min-width: 80px;
    color: white;
}

.value-desc {
    color: rgba(255, 255, 255, 0.7);
}

/* Steps */
.steps-list {
    color: rgba(255, 255, 255, 0.8);
    padding-left: 20px;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    line-height: 1.6;
}

.steps-list strong {
    color: white;
}

/* Actions */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}

.action-item {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.action-name {
    font-weight: 700;
    color: #3BC3DB;
    font-size: 0.95rem;
}

.action-desc {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

/* Results */
.results-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.result-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-radius: 6px;
    border-left: 3px solid transparent;
}

.result-row.win       { border-color: #3BC3DB; background: rgba(59,195,219,0.08); }
.result-row.blackjack { border-color: #9C5CCB; background: rgba(156,92,203,0.1); }
.result-row.push      { border-color: #818AC8; background: rgba(129,138,200,0.08); }
.result-row.lose      { border-color: #ff5f5f; background: rgba(255,95,95,0.08); }

.result-label {
    font-weight: 600;
    color: white;
}

.result-label small {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.8em;
}

.result-pay {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.9rem;
}

/* CTA */
.btn-play {
    display: inline-block;
    padding: 12px 40px;
    border-radius: 50px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB);
    border: none;
    letter-spacing: 1px;
    transition: opacity 0.2s;
}

.btn-play:hover {
    opacity: 0.85;
    color: white;
}

@media (max-width: 576px) {
    .rules-title {
        font-size: 1.8rem;
    }

    .rule-card {
        padding: 20px 18px;
    }

    .result-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>
