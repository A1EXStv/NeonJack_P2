# Sistema de Juego — Blackjack

## Visión general

El juego sigue las reglas estándar de Blackjack: el objetivo es llegar a 21 sin pasarse, superando la puntuación del dealer. El sistema está implementado en el backend con `BlackjackService` y se sincroniza en tiempo real con el frontend a través de **WebSockets (Laravel Reverb)**, con un fallback de polling cada 30 segundos.

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
broadcast(new GameStarted($sala, $data)); // → WS: canal sala.{id} y lobby
```

Se crean los registros `PartidaUser` con estado `apostando` para cada jugador activo. El evento `GameStarted` notifica a todos en la sala y en el lobby.

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
// Emitir evento por cada carta repartida
broadcast(new CardDealt($sala, $jugador->user_id, $carta, false)); // → WS: canal sala.{id}
broadcast(new CardDealt($sala, null, $cartaDealer, true));         // hidden=true → carta oculta del dealer

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

Un Blackjack natural (As + figura/10 en 2 cartas) se resuelve inmediatamente. Cada carta repartida dispara un evento WebSocket que el frontend recibe para actualizar el estado.

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
        broadcast(new TurnChanged($partida->sala, $siguiente->user_id, 30)); // → WS
        return;
    }

    // Nadie más → turno del dealer
    $this->turnoDealer($partida);
}
```

Los jugadores juegan por orden de ID de `PartidaUser`. El evento `TurnChanged` llega al frontend, que inmediatamente hace un fetch del estado completo y activa el timer de turno del jugador correspondiente.

### 5. Acciones del jugador

#### Pedir carta — `hit()`

```php
$carta = $this->generarBaraja()[0]; // carta aleatoria de nueva baraja
$pu->agregarCarta($carta);
broadcast(new CardDealt($sala, $pu->user_id, $carta)); // → WS

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
broadcast(new CardDealt($sala, $pu->user_id, $carta)); // → WS

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
broadcast(new CardDealt($partida->sala, null, $manoDealer[1])); // → WS (hidden=false ahora)

// El dealer pide cartas mientras tenga menos de 17 (regla estándar)
while (calcularPuntuacion($manoDealer) < 17) {
    $carta = array_shift($baraja);
    $manoDealer[] = $carta;
    $partida->update(['mano_dealer' => $manoDealer]);
    broadcast(new CardDealt($partida->sala, null, $carta)); // → WS
}

$this->resolverRonda($partida, $manoDealer);
```

El dealer siempre se planta en 17 o más (hard/soft 17). Cada carta del dealer dispara un evento WebSocket.

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
broadcast(new RoundEnded($sala, $resultados, $manoDealer)); // → WS
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

Devuelve toda la información de la partida incluyendo los jugadores y sus manos. El frontend lo llama en el mount inicial, ante cada evento WebSocket, y como fallback cada 30 segundos.

---

## WebSockets — Eventos de juego

### Stack tecnológico

```
Laravel Reverb  →  servidor WebSocket (localhost:8080, protocolo Pusher)
Laravel Echo    →  cliente WebSocket en el frontend (npm: laravel-echo)
pusher-js       →  driver de transporte que Echo usa internamente con Reverb
```

Aunque se usa `pusher-js` como librería de transporte, **no se conecta a los servidores externos de Pusher**. Reverb implementa el mismo protocolo WebSocket que Pusher pero corre localmente.

### Configuración del backend

**.env:**
```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=793789
REVERB_APP_KEY=8qdpzt7jfwze4mni5bsa
REVERB_APP_SECRET=v5zaywmbhfsqchrordsu
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

**config/broadcasting.php** — conexión añadida:
```php
'reverb' => [
    'driver' => 'reverb',
    'key'    => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host'   => env('REVERB_HOST'),
        'port'   => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
        'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
    ],
],
```

### Configuración del frontend

**resources/js/plugins/axios.js:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,       // 8qdpzt7jfwze4mni5bsa
    wsHost: import.meta.env.VITE_REVERB_HOST,        // localhost
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

`window.Echo` queda disponible globalmente y puede usarse desde cualquier componente o composable.

### Eventos del juego y sus canales

Todos los eventos de juego se emiten en el canal público `sala.{id}`, donde `id` es el ID de la sala en la base de datos (no el código `BJ-XXXX`).

| Evento | Canal | Payload | Cuándo se emite |
|---|---|---|---|
| `CardDealt` | `sala.{id}` | `{ sala, userId, card, hidden }` | Cada carta repartida (jugadores y dealer) |
| `TurnChanged` | `sala.{id}` | `{ sala, userId, timeout }` | Cambio de turno entre jugadores |
| `GameStarted` | `sala.{id}` + `lobby` | `{ sala, data }` | Al iniciar la partida |
| `RoundEnded` | `sala.{id}` | `{ sala, resultados, manoDealer }` | Al resolver la ronda |

Todos los eventos implementan `ShouldBroadcast` y definen `broadcastAs()` con el nombre corto (sin namespace) para que Echo pueda escucharlos con prefijo `.`:

```php
class CardDealt implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new Channel('sala.' . $this->sala->id)];
    }

    public function broadcastAs(): string
    {
        return 'CardDealt'; // nombre limpio para Echo
    }
}
```

### Por qué `QUEUE_CONNECTION=sync`

Con `QUEUE_CONNECTION=sync`, los eventos se emiten **de forma síncrona** durante la misma petición HTTP que los dispara. Esto significa que cuando el frontend llama a `POST /hit`, la respuesta HTTP no llega hasta que el evento WebSocket ya fue enviado a Reverb. No hace falta un worker de colas corriendo.

Si en producción se cambia a `redis` o `database`, los eventos se encolarán y harán falta workers (`php artisan queue:work`) para procesarlos.

---

## Frontend — `useGame.js` (composable)

Todo el estado y lógica del juego vive en este composable. `GameTable.vue` solo importa lo que necesita.

### Conexión WebSocket al montar

```javascript
onMounted(async () => {
    // Carga inicial — estado completo de la partida, skin y código de sala
    await Promise.all([fetchGameState(), fetchSkinUrl(), fetchSalaCode()]);

    const salaId = route.params.salaId;

    // Suscripción al canal de la sala
    window.Echo.channel(`sala.${salaId}`)
        .listen('.CardDealt',   () => fetchGameState())
        .listen('.TurnChanged', () => fetchGameState())
        .listen('.GameStarted', () => fetchGameState())
        .listen('.RoundEnded',  () => fetchGameState());

    // Fallback: refresca el estado completo cada 30s
    // (protege contra desconexiones WS o mensajes perdidos)
    pollTimer = setInterval(fetchGameState, 30000);
});

onUnmounted(() => {
    const salaId = route.params.salaId;
    if (salaId) window.Echo.leave(`sala.${salaId}`); // cancela la suscripción
    clearInterval(pollTimer);
    clearInterval(bettingInterval);
    clearInterval(turnInterval);
    clearInterval(restartInterval);
});
```

### Flujo de un evento WebSocket recibido

```
Backend: BlackjackService llama $this->siguienteTurno()
    └─► broadcast(new TurnChanged($sala, $userId, 30))
    └─► Reverb emite el mensaje en WebSocket al canal 'sala.{id}'

Frontend: Echo recibe el mensaje
    └─► .listen('.TurnChanged', () => fetchGameState())
    └─► fetchGameState() → GET /api/partidas/{id}/estado
    └─► gameState.value = respuesta
    └─► Vue reactivity actualiza todos los computed (myPU, isPlayerTurn, etc.)
    └─► watch(() => myPU.value?.estado) detecta el cambio de estado
    └─► Si es 'jugando' → arranca el turnInterval de 25 segundos
```

La estrategia de "recibir evento → hacer fetch completo" es más robusta que actualizar el estado parcialmente desde el payload del evento, ya que garantiza que el frontend siempre tiene el estado exacto de la base de datos.

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
watch(() => gameState.value?.estado, (newState) => {
    if (newState !== 'finalizada') { clearInterval(restartInterval); return; }
    startRestartCountdown(); // 10s
});

const autoRestart = async () => {
    try { await axios.post(`/api/salas/${salaId}/iniciar`); } catch {} // 403 si no es owner
    await new Promise(r => setTimeout(r, 1200)); // espera a que la partida se cree en BD
    const res = await axios.get(`/api/salas/${salaId}`);
    const newPartida = res.data?.partidas?.[0];
    if (newPartida?.id !== currentPartidaId) {
        router.push({ name: 'game.table', params: { ..., partidaId: newPartida.id } });
        return; // todos navegan al nuevo partidaId
    }
    startRestartCountdown(); // si no hay nueva partida, reintentar
};
```

Al acabar la ronda: el owner lanza la siguiente automáticamente. Los demás jugadores reciben el `GameStarted` por WebSocket, hacen fetch del estado y navegan al nuevo `partidaId`.

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

Cuando una carta es "nueva" (recién añadida a la mano), recibe la clase `card-deal-up` (dealer) o `card-deal-down` (jugador). Esta animación se activa gracias a los eventos WebSocket: `CardDealt` llega → `fetchGameState()` → la mano crece → el watcher detecta el índice nuevo → se añade la clase de animación.

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

```css
@keyframes card-deal-up {
    0%   { transform: translateY(70px) scale(0.35) rotateY(0deg);  opacity: 0; }
    30%  { transform: translateY(-6px) scale(1.06) rotateY(0deg);  opacity: 1; }
    50%  { transform: translateY(0) scale(1) rotateY(90deg); }      /* flip */
    100% { transform: translateY(0) scale(1) rotateY(0deg);  opacity: 1; }
}
```

---

## Flujo resumido E2E con WebSockets

```
[Lobby] Owner crea sala
    └─► Jugadores se unen → WS: PlayerJoinedRoom → lobby actualiza lista
    └─► Owner pulsa "Iniciar" → POST /salas/{id}/iniciar
            └─► BlackjackService::iniciarPartida()
                    └─► Crea Partida + PartidaUsers (estado: apostando)
                    └─► sala.status = 'playing'
                    └─► WS: GameStarted → sala.{id} + lobby

[GameTable] useGame monta → fetch inicial → suscripción a sala.{id}

[Fase apuesta] Todos apuestan → POST /partidas/{id}/apostar
    └─► Último en apostar → repartirCartasIniciales()
            └─► WS: CardDealt × (2 por jugador + 2 dealer)
            └─► Frontend: cada CardDealt → fetchGameState() → mano aparece con animación
            └─► Detectar blackjacks → siguienteTurno()
                    └─► WS: TurnChanged → frontend activa timer 25s

[Turno de cada jugador] hit / stand / doblar / dividir
    └─► POST /partidas/{id}/hit → carta añadida
            └─► WS: CardDealt → frontend → fetchGameState() → animación
    └─► Cada acción llama a siguienteTurno()
            └─► WS: TurnChanged → siguiente jugador recibe timer

[Dealer juega]
    └─► WS: CardDealt (carta oculta revelada + nuevas cartas del dealer)
    └─► resolverRonda() → wallets actualizadas en BD
            └─► WS: RoundEnded → frontend → fetchGameState() → muestra resultados

[Fin de ronda] restartTimer (10s) → autoRestart()
    └─► Owner: POST /salas/{id}/iniciar → nueva Partida
            └─► WS: GameStarted → todos hacen fetchGameState()
    └─► Todos: GET /salas/{id} → nuevo partidaId → router.push a nueva partida
```

---

## Cómo arrancar el sistema completo

```bash
# Terminal 1 — API Laravel
php artisan serve

# Terminal 2 — Servidor WebSocket Reverb
php artisan reverb:start

# Terminal 3 — Vite (frontend)
npm run dev
```

**Verificar la conexión WebSocket:** Abrir DevTools → Network → WS → debe aparecer una conexión activa a `ws://localhost:8080/app/{REVERB_APP_KEY}`. En la pestaña "Messages" de esa conexión se pueden ver en tiempo real los eventos que llegan (CardDealt, TurnChanged, etc.).
