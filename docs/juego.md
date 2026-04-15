# Sistema de Juego — Blackjack

## Visión general

El juego sigue las reglas estándar de Blackjack: el objetivo es llegar a 21 sin pasarse, superando la puntuación del dealer. El sistema está implementado en el backend con `BlackjackService` y se refleja en el frontend a través de polling cada 2 segundos.

---

## Modelos de datos

### `Partida`

Representa una ronda completa de juego dentro de una sala.

| Campo | Tipo | Descripción |
|---|---|---|
| `sala_id` | FK | Sala a la que pertenece |
| `mano_dealer` | JSON array | Cartas del dealer: `[{palo, valor}, ...]` |
| `estado` | enum | `en_curso` → `finalizada` |

### `PartidaUser` — tabla `partida_usuario`

Un registro por jugador por partida. Almacena el estado individual de cada jugador.

| Campo | Tipo | Descripción |
|---|---|---|
| `partida_id` | FK | Partida a la que pertenece |
| `user_id` | FK | Jugador |
| `apuesta_total` | integer | Fichas apostadas en esta ronda |
| `mano_usuario` | JSON array | Cartas del jugador |
| `estado` | string | Estado del turno del jugador |
| `resultado` | string | Resultado final (`gana`, `pierde`, `empata`, `blackjack`) |
| `balance_resultado` | integer | Fichas ganadas/perdidas en la ronda |

**Estados posibles del jugador:**

```
apostando → esperando → jugando → plantado
                                 → reventado (bust)
                                 → doblado
                                 → dividido
                     → finalizado
```

---

## Estructura de una carta

Todas las cartas se representan como objetos JSON:

```json
{ "palo": "hearts", "valor": "A" }
```

- **Palos:** `hearts`, `diamonds`, `clubs`, `spades`
- **Valores:** `2`–`10`, `J`, `Q`, `K`, `A`

---

## `BlackjackService` — Lógica central

### Generación de baraja

```php
private function generarBaraja(): array
{
    $palos   = ['hearts', 'diamonds', 'clubs', 'spades'];
    $valores = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];
    $baraja  = [];
    foreach ($palos as $palo)
        foreach ($valores as $valor)
            $baraja[] = ['palo' => $palo, 'valor' => $valor];
    shuffle($baraja);
    return $baraja;
}
```

52 cartas barajadas aleatoriamente. Se genera una baraja nueva en cada operación (no se mantiene una baraja persistente entre acciones).

### Cálculo de puntuación — `PartidaUser::calcularPuntuacion()`

```php
public static function calcularPuntuacion(array $cartas): int
{
    $total = 0; $ases = 0;
    foreach ($cartas as $carta) {
        if (in_array($carta['valor'], ['J', 'Q', 'K'])) $total += 10;
        elseif ($carta['valor'] === 'A') { $total += 11; $ases++; }
        else $total += (int) $carta['valor'];
    }
    // Los ases pasan de 11 a 1 si se supera 21
    while ($total > 21 && $ases > 0) { $total -= 10; $ases--; }
    return $total;
}
```

Los ases valen 11 pero se reducen a 1 automáticamente si la mano se pasa de 21.

---

## Flujo completo de una partida

### 1. Iniciar — `iniciarPartida()`

```php
// Solo el owner puede iniciar
if ($sala->owner_id !== Auth::id()) return 403;
if ($sala->status !== 'waiting')    return 422;

$partida = Partida::create(['sala_id' => $sala->id, 'mano_dealer' => [], 'estado' => 'en_curso']);

foreach ($jugadores as $jugador) {
    PartidaUser::create([
        'partida_id'    => $partida->id,
        'user_id'       => $jugador->id,
        'apuesta_total' => 0,
        'mano_usuario'  => [],
        'estado'        => 'apostando',   // todos empiezan apostando
    ]);
}

$sala->update(['status' => 'playing']);
```

Se crean los registros `PartidaUser` con estado `apostando` para cada jugador activo.

### 2. Apostar — `apostar()`

```php
// Validar que la apuesta esté dentro de límites (Ajustes)
if ($apuesta < $ajuste->apuesta_minima) throw new Exception(...);
if ($apuesta > $ajuste->apuesta_maxima) throw new Exception(...);

$pu->update(['apuesta_total' => $apuesta, 'estado' => 'esperando']);

// Cuando todos han apostado → repartir cartas
$pendientes = PartidaUsuario::where('partida_id', $partida->id)
    ->where('estado', 'apostando')->count();

if ($pendientes === 0) {
    $this->repartirCartasIniciales($partida);
}
```

El sistema espera a que **todos** los jugadores apuesten. Cuando el último apuesta, las cartas se reparten automáticamente.

### 3. Repartir cartas iniciales — `repartirCartasIniciales()`

```
Jugador 1 → 1ª carta
Jugador 2 → 1ª carta
Dealer    → 1ª carta (visible)
Jugador 1 → 2ª carta
Jugador 2 → 2ª carta
Dealer    → 2ª carta (boca abajo / oculta)
```

```php
// Detectar blackjacks inmediatos
foreach ($jugadores as $pu) {
    $pu->refresh();
    if ($pu->tieneBlackjack()) {
        $pu->update(['estado' => 'finalizado', 'resultado' => 'blackjack']);
    }
}

// Pasar turno al primer jugador activo
$this->siguienteTurno($partida);
```

Un Blackjack natural (As + figura/10 en 2 cartas) se resuelve inmediatamente.

### 4. Gestión de turnos — `siguienteTurno()`

```php
private function siguienteTurno(Partida $partida): void
{
    $siguiente = $partida->partida_usuarios()
        ->where('estado', 'esperando')
        ->orderBy('id')   // respeta el orden de inscripción
        ->first();

    if ($siguiente) {
        $siguiente->update(['estado' => 'jugando']);
        broadcast(new TurnChanged($partida->sala, $siguiente->user_id, 30));
        return;
    }

    // Nadie más → turno del dealer
    $this->turnoDealer($partida);
}
```

Los jugadores juegan por orden de ID de `PartidaUser`. Cuando no queda nadie en `esperando`, juega el dealer.

### 5. Acciones del jugador

#### Pedir carta — `hit()`

```php
$carta = $this->generarBaraja()[0]; // carta aleatoria de nueva baraja
$pu->agregarCarta($carta);

if ($pu->haReventado()) {          // puntuación > 21
    $pu->update(['estado' => 'reventado']);
    $this->siguienteTurno($partida); // pasa al siguiente
}
```

#### Plantarse — `stand()`

```php
$pu->update(['estado' => 'plantado']);
$this->siguienteTurno($partida);
```

#### Doblar — `doblar()`

```php
// Solo con exactamente 2 cartas
if (count($pu->mano_usuario) !== 2) throw new Exception(...);

$nuevaApuesta = $pu->apuesta_total * 2;
$pu->update(['apuesta_total' => $nuevaApuesta]);
$pu->agregarCarta($carta);   // solo 1 carta más

$estado = $pu->haReventado() ? 'reventado' : 'doblado';
$pu->update(['estado' => $estado]);
$this->siguienteTurno($partida);
```

#### Dividir — `dividir()`

```php
// Solo con 2 cartas del mismo valor
if ($mano[0]['valor'] !== $mano[1]['valor']) throw new Exception(...);

// Mano original: primera carta + nueva
$pu->update(['mano_usuario' => [$mano[0], $carta1], 'estado' => 'dividido']);

// Segunda mano: segunda carta + nueva (nuevo PartidaUser del mismo user_id)
PartidaUsuario::create([
    'partida_id'    => $partida->id,
    'user_id'       => $usuario->id,
    'apuesta_total' => $pu->apuesta_total,
    'mano_usuario'  => [$mano[1], $carta2],
    'estado'        => 'jugando',   // ya es su turno
]);
```

El split crea un **segundo registro `PartidaUser`** para el mismo usuario. La segunda mano queda directamente en `jugando` porque es el turno de ese jugador.

> **Nota frontend:** `myPU` prioriza el registro con `estado = 'jugando'` para mostrar siempre la mano activa:
> ```javascript
> const myPU = computed(() => {
>   const mine = allPlayers.value.filter(p => p.user_id === authUser.id);
>   return mine.find(p => p.estado === 'jugando')
>       ?? mine.find(p => p.estado === 'apostando')
>       ?? mine[0] ?? null;
> });
> ```

### 6. Turno del dealer — `turnoDealer()`

```php
// Revelar la carta oculta
broadcast(new CardDealt($partida->sala, null, $manoDealer[1]));

// El dealer pide cartas mientras tenga menos de 17 (regla estándar)
while (calcularPuntuacion($manoDealer) < 17) {
    $carta = array_shift($baraja);
    $manoDealer[] = $carta;
    $partida->update(['mano_dealer' => $manoDealer]);
}

$this->resolverRonda($partida, $manoDealer);
```

El dealer siempre se planta en 17 o más (hard/soft 17).

### 7. Resolver ronda — `resolverRonda()`

```php
private function determinarResultado($pu, $puntuacion, $puntuacionDealer, $dealerReventado): string
{
    if ($pu->estado === 'reventado')              return 'pierde';
    if ($pu->resultado === 'blackjack')           return 'blackjack';
    if ($dealerReventado)                         return 'gana';
    if ($puntuacion > $puntuacionDealer)          return 'gana';
    if ($puntuacion === $puntuacionDealer)        return 'empata';
    return 'pierde';
}

private function calcularBalance(string $resultado, int $apuesta): int
{
    return match ($resultado) {
        'blackjack' => (int) round($apuesta * 1.5), // paga 3:2
        'gana'      => $apuesta,
        'empata'    => 0,
        'pierde'    => -$apuesta,
    };
}
```

Pagos: Blackjack paga **3:2**. Victoria normal paga **1:1**. Empate devuelve la apuesta. Derrota pierde la apuesta.

Al final de la ronda:
```php
$partida->update(['estado' => 'finalizada']);
$partida->sala->update(['status' => 'waiting']); // sala vuelve a aceptar jugadores
```

---

## Controlador — `BlackjackController`

Actúa como puente entre las rutas HTTP y el servicio. Cada acción sigue el mismo patrón:

```php
public function hit(Partida $partida): JsonResponse
{
    try {
        $pu = $this->blackjack->hit($partida, Auth::user());
        return response()->json($pu);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
}
```

```
POST /api/salas/{sala}/iniciar        → BlackjackController::iniciar()
GET  /api/partidas/{partida}/estado   → BlackjackController::estado()
POST /api/partidas/{partida}/apostar  → BlackjackController::apostar()
POST /api/partidas/{partida}/hit      → BlackjackController::hit()
POST /api/partidas/{partida}/stand    → BlackjackController::stand()
POST /api/partidas/{partida}/doblar   → BlackjackController::doblar()
POST /api/partidas/{partida}/dividir  → BlackjackController::dividir()
```

### Endpoint de estado

```php
public function estado(Partida $partida): JsonResponse
{
    return response()->json(
        $partida->load('partida_usuarios.usuario')
    );
}
```

Devuelve toda la información de la partida incluyendo los jugadores y sus manos. El frontend lo llama cada 2 segundos (polling).

---

## Frontend — `useGame.js` (composable)

Todo el estado y lógica del juego vive en este composable. `GameTable.vue` solo importa lo que necesita.

### Polling del estado

```javascript
onMounted(async () => {
    await Promise.all([fetchGameState(), fetchSkinUrl(), fetchSalaCode()]);
    pollTimer = setInterval(fetchGameState, 2000); // refresca cada 2s
});
onUnmounted(() => {
    clearInterval(pollTimer);
    clearInterval(bettingInterval);
    clearInterval(turnInterval);
    clearInterval(restartInterval);
});
```

No usa WebSockets. El estado se sincroniza via polling, lo que simplifica la arquitectura a costa de ~2s de latencia entre acciones.

### Timers automáticos

El composable gestiona tres temporizadores independientes:

#### Timer de apuesta (60s)

```javascript
if (newState === 'apostando') {
    bettingTimer.value = BETTING_TIME; // 60s
    bettingInterval = setInterval(() => {
        if (bettingTimer.value > 1) { bettingTimer.value--; return; }
        clearInterval(bettingInterval);
        if (!isActing.value) {
            if (betAmount.value < 10) addChip(10); // mínimo si no apostó nada
            placeBet();                            // apuesta automática
        }
    }, 1000);
}
```

Si el jugador no apuesta en 60s, el sistema apuesta 10 fichas (mínimo) automáticamente.

#### Timer de turno (25s)

```javascript
} else if (newState === 'jugando') {
    turnTimer.value = TURN_TIME; // 25s
    turnInterval = setInterval(() => {
        if (turnTimer.value > 1) { turnTimer.value--; return; }
        clearInterval(turnInterval);
        if (!isActing.value) doStand(); // se planta automáticamente
    }, 1000);
}
```

Si el jugador no actúa en 25s, se planta automáticamente.

#### Timer de reinicio (10s)

```javascript
// Cuando la partida se finaliza, cuenta atrás para nueva ronda
watch(() => gameState.value?.estado, (newState) => {
    if (newState !== 'finalizada') { ... return; }
    startRestartCountdown(); // 10s
});

const autoRestart = async () => {
    try { await axios.post(`/api/salas/${salaId}/iniciar`); } catch {} // solo owner puede
    await new Promise(r => setTimeout(r, 1200)); // esperar a que se cree
    // Todos navegan a la nueva partida
    const res = await axios.get(`/api/salas/${salaId}`);
    const newPartida = res.data?.partidas?.[0];
    if (newPartida?.id !== currentPartidaId) {
        router.push({ name: 'game.table', params: { ..., partidaId: newPartida.id } });
        return;
    }
    startRestartCountdown(); // Si no hay nueva partida, reintentar
};
```

Al acabar la ronda, el owner crea la siguiente automáticamente. Si falla (owner ausente), el countdown se reinicia indefinidamente hasta que alguien inicie.

### Visualización de timers — SVG ring

```html
<svg width="44" height="44" viewBox="0 0 44 44" style="transform:rotate(-90deg)">
  <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
  <circle cx="22" cy="22" r="18" fill="none"
          :stroke="timerColor(turnTimer, TURN_TIME)"
          stroke-dasharray="113.1"
          :stroke-dashoffset="ringOffset(turnTimer, TURN_TIME)"
          style="transition:stroke-dashoffset 0.9s linear;" />
</svg>
```

- Circunferencia del círculo: `2π × 18 ≈ 113.1px`
- `stroke-dashoffset = 113.1 × (1 - tiempoRestante/tiempoTotal)` → el arco se "vacía" conforme pasa el tiempo
- Color: verde (>50%) → amarillo (>25%) → rojo (<25%)

### Sistema de apuestas con fichas

```javascript
const CHIPS = [
    { value: 5,   label: '5',   bg: '#e5e7eb', ... },
    { value: 10,  label: '10',  bg: '#2563eb', ... },
    { value: 25,  label: '25',  bg: '#16a34a', ... },
    { value: 50,  label: '50',  bg: '#dc2626', ... },
    { value: 100, label: '100', bg: '#111827', ... },
    { value: 500, label: '500', bg: '#7c3aed', ... },
];

const addChip  = (v) => { betAmount.value += v; betHistory.value.push(v); };
const undoChip = () => { betAmount.value -= betHistory.value.pop(); };
const clearBet = () => { betAmount.value = 0; betHistory.value = []; };
```

`betHistory` registra cada ficha añadida en orden, permitiendo deshacer una a una con `undoChip`.

---

## Componente `GameCard.vue`

Renderiza una carta individual. Soporta dos modos:

```
faceDown = false  →  Muestra valor y palo (cara visible)
faceDown = true   →  Muestra dorso (skin del jugador o patrón por defecto)
```

```javascript
const CARD_SIZES = {
    sm: { width: '42px',  height: '60px'  },  // jugadores laterales
    md: { width: '56px',  height: '80px'  },  // dealer
    lg: { width: '72px',  height: '102px' },  // jugador principal
};
```

El dorso puede ser una imagen de skin personalizada (Spatie MediaLibrary) o el patrón azul por defecto.

### Animación de reparto

Cuando una carta es "nueva" (recién añadida a la mano), recibe la clase `card-deal-up` (dealer) o `card-deal-down` (jugador):

```css
@keyframes card-deal-up {
    0%   { transform: translateY(70px) scale(0.35) rotateY(0deg);  opacity: 0; }
    30%  { transform: translateY(-6px) scale(1.06) rotateY(0deg);  opacity: 1; }
    50%  { transform: translateY(0) scale(1) rotateY(90deg); }      /* flip */
    100% { transform: translateY(0) scale(1) rotateY(0deg);  opacity: 1; }
}
```

La carta aparece desde el mazo central, vuela hasta su posición y se voltea revelando su valor. El tracking de cartas nuevas usa un `Set` de índices:

```javascript
const animNew = (idxSet, prev, next) => {
    for (let i = prev; i < next; i++) {
        idxSet.value.add(i);
        setTimeout(() => idxSet.value.delete(i), 800); // quitar clase tras la animación
    }
};
watch(myHand, cards => {
    if (cards.length > prevMyLen.value)
        animNew(myNewIdx, prevMyLen.value, cards.length);
    prevMyLen.value = cards.length;
}, { deep: true });
```

---

## Flujo resumido E2E

```
[Lobby] Owner crea sala
    └─► Jugadores se unen (join)
    └─► Owner pulsa "Iniciar" → POST /salas/{id}/iniciar
            └─► BlackjackService::iniciarPartida()
                    └─► Crea Partida + PartidaUsers (estado: apostando)
                    └─► sala.status = 'playing'

[GameTable] Todos apuestan → POST /partidas/{id}/apostar
    └─► Último en apostar → repartirCartasIniciales()
            └─► 2 cartas por jugador + 2 al dealer (1 oculta)
            └─► Detectar blackjacks → siguienteTurno()

[Turno de cada jugador] hit / stand / doblar / dividir
    └─► Cada acción llama a siguienteTurno()
    └─► Cuando no hay más jugadores en 'esperando' → turnoDealer()

[Dealer juega] Se revela carta oculta + pide hasta 17
    └─► resolverRonda() → calcula resultados + balances
    └─► partida.estado = 'finalizada' / sala.status = 'waiting'

[Frontend] restartTimer (10s) → autoRestart()
    └─► Owner llama a iniciar → nueva Partida
    └─► Todos navegan al nuevo partidaId
```
