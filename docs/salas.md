# Sistema de Salas

## Visión general

Una **Sala** es el espacio donde los jugadores se reúnen antes y durante una partida de Blackjack. Tiene capacidad para 1-3 jugadores, un propietario (owner) que controla el inicio, y pasa por varios estados a lo largo de su ciclo de vida.

---

## Modelo — `Sala.php`

```php
protected $fillable = ['nombre_sala', 'code', 'status', 'max_players', 'owner_id'];
```

| Campo | Tipo | Descripción |
|---|---|---|
| `nombre_sala` | string | Nombre visible en el lobby |
| `code` | string (8) | Código único auto-generado (`BJ-XXXX`) |
| `status` | enum | `waiting` → `playing` → `finished` |
| `max_players` | tinyint | Máximo de jugadores (1, 2 o 3) |
| `owner_id` | FK users | Quién puede iniciar la partida |

### Generación automática del código

```php
protected static function booted(): void
{
    static::creating(function (Sala $sala) {
        $sala->code = strtoupper('BJ-' . Str::random(4));
    });
}
```

Cada sala obtiene un código único al crearse (ej. `BJ-K4TZ`) usando el hook `creating` de Eloquent. No requiere lógica en el controlador.

### Relaciones

```php
// Jugadores inscritos (tabla pivote sala_usuario)
public function players() {
    return $this->belongsToMany(User::class, 'sala_usuario')
                ->withPivot(['status', 'chips', 'seat']);
}

// Solo los jugadores activos (sentados, listos o jugando)
public function activePlayers() {
    return $this->players()->wherePivotIn('status', ['sitting', 'ready', 'playing']);
}

// Partidas de esta sala
public function partidas() {
    return $this->hasMany(Partida::class);
}
```

### Helpers

```php
public function isFull(): bool
{
    return $this->activePlayers()->count() >= $this->max_players;
}

public function hasPlayer(int $userId): bool
{
    return $this->players()->where('user_id', $userId)->exists();
}

public function availableSeat(): ?int
{
    $taken = $this->activePlayers()->pluck('sala_usuario.seat')->filter()->toArray();
    for ($i = 1; $i <= $this->max_players; $i++) {
        if (!in_array($i, $taken)) return $i;
    }
    return null;
}
```

---

## Base de datos — Migraciones

### Tabla `salas`

```php
$table->id();
$table->string('nombre_sala');
$table->string('code', 8)->unique();
$table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
$table->unsignedTinyInteger('max_players')->default(3);
$table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
$table->timestamps();
```

### Tabla pivote `sala_usuario`

```php
$table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->enum('status', ['sitting', 'ready', 'playing', 'spectating'])->default('sitting');
$table->unsignedInteger('chips')->default(1000);
$table->unsignedTinyInteger('seat')->nullable();
$table->unique(['sala_id', 'user_id']); // un usuario solo puede estar una vez por sala
$table->unique(['sala_id', 'seat']);    // dos jugadores no pueden ocupar el mismo asiento
```

`cascadeOnDelete` en `sala_id` significa que si se borra una sala, todos sus registros en el pivot se eliminan automáticamente.

---

## Controlador — `SalaController.php`

Todas las rutas de sala requieren `auth:sanctum`:

```
GET    /api/salas                → index()
POST   /api/salas                → store()
GET    /api/salas/{sala}         → show()
POST   /api/salas/{sala}         → update()
DELETE /api/salas/{sala}         → destroy()
POST   /api/salas/{sala}/join    → join()
DELETE /api/salas/{sala}/leave   → leave()
```

### `index()` — Listar salas

```php
public function index(): JsonResponse
{
    $salas = Sala::with(['players'])
        ->whereIn('status', ['waiting', 'playing'])  // excluye las 'finished'
        ->latest()
        ->get()
        ->map(fn ($sala) => array_merge($sala->toArray(), [
            'isFull' => $sala->isFull(),  // campo calculado para el frontend
        ]));
    return response()->json($salas);
}
```

Solo devuelve salas activas. Las salas `finished` (cerradas) no aparecen en el lobby.

### `store()` — Crear sala

```php
public function store(StoreSalaRequest $request): JsonResponse
{
    $sala = DB::transaction(function () use ($request) {
        $sala = Sala::create([
            ...$request->validated(),
            'owner_id' => Auth::id(),
        ]);
        $sala->players()->attach(Auth::id(), [
            'status' => 'sitting',
            'chips'  => 1000,
            'seat'   => 1,
        ]);
        return $sala;
    });
    // ...
}
```

El creador queda automáticamente inscrito en el asiento 1 con 1000 fichas.

### `join()` — Unirse a una sala

```php
public function join(Sala $sala): JsonResponse
{
    $partidaEnApuesta = null;
    if ($sala->status === 'playing') {
        $partida = $sala->partidas()->where('estado', 'en_curso')->latest()->first();

        if (!$partida || !$partida->partida_usuarios()->where('estado', 'apostando')->exists()) {
            return response()->json(['message' => 'La partida ya está en curso...'], 422);
        }
        $partidaEnApuesta = $partida;
    }

    $seat = $sala->availableSeat();

    DB::transaction(function () use ($sala, $user, $seat, $partidaEnApuesta) {
        $sala->players()->attach($user->id, ['status' => 'sitting', 'chips' => 1000, 'seat' => $seat]);

        // Si hay apuesta en curso, añadir al jugador a la partida activa
        if ($partidaEnApuesta) {
            PartidaUser::create([
                'partida_id'    => $partidaEnApuesta->id,
                'user_id'       => $user->id,
                'apuesta_total' => 0,
                'mano_usuario'  => [],
                'estado'        => 'apostando',
            ]);
        }
    });

    broadcast(new PlayerJoinedRoom($sala, $user, $seat))->toOthers();
}
```

**Casos de uso:**
- `status = 'waiting'` → unión normal, el jugador espera a que el owner inicie.
- `status = 'playing'` + partida en fase de apuesta → se une y puede apostar en la ronda actual.
- `status = 'playing'` + cartas ya repartidas → rechazado (422).

Al unirse, se emite `PlayerJoinedRoom` vía WebSocket a todos los demás (`.toOthers()`).

### `leave()` — Salir de una sala

```php
public function leave(Sala $sala): JsonResponse
{
    $user    = Auth::user();
    $isOwner = $sala->owner_id === $user->id;

    DB::transaction(function () use ($sala, $user, $isOwner) {
        $sala->players()->detach($user->id);

        if ($isOwner) {
            $next = $sala->activePlayers()->first();
            if ($next) {
                $sala->update(['owner_id' => $next->id]); // transferir ownership
            } else {
                $sala->players()->detach();              // limpiar pivot
                $sala->update(['status' => 'finished']); // cerrar sala
            }
        }
    });

    $sala->refresh();

    if ($sala->status === 'finished') {
        return response()->json(['closed' => true, 'message' => 'Sala cerrada.']);
    }

    broadcast(new PlayerLeftRoom($sala, $user))->toOthers();
    return response()->json(['closed' => false, 'message' => 'Has salido de la sala.']);
}
```

**Lógica de cierre:**
- Si quedan otros jugadores → el ownership pasa al siguiente en la lista.
- Si es el último → sala pasa a `finished` y deja de aparecer en el lobby.
- `PlayerLeftRoom` se emite vía WebSocket solo si la sala no se cerró.

---

## WebSockets — Eventos de sala

El sistema de salas emite eventos en tiempo real usando **Laravel Reverb** (servidor WebSocket) con el protocolo de Pusher. El frontend los recibe a través de **Laravel Echo**.

### Configuración

```
Backend:  BROADCAST_DRIVER=reverb  (config/broadcasting.php → conexión 'reverb')
Servidor: php artisan reverb:start  (WebSocket en localhost:8080)
Frontend: Echo con broadcaster 'reverb', conectado a ws://localhost:8080
```

### Eventos disponibles

| Evento | Canal | Descripción |
|---|---|---|
| `PlayerJoinedRoom` | `sala.{id}` + `lobby` | Un jugador entró a la sala |
| `PlayerLeftRoom` | `sala.{id}` + `lobby` | Un jugador salió de la sala |
| `GameStarted` | `sala.{id}` + `lobby` | El owner inició la partida |

Los eventos `PlayerJoinedRoom`, `PlayerLeftRoom` y `GameStarted` se emiten en **dos canales simultáneamente**: el canal de la sala específica (`sala.{id}`) y un canal global (`lobby`). Esto permite que tanto los jugadores dentro de la sala como el lobby en general reciban la actualización.

### Estructura de los eventos (PHP)

```php
// PlayerJoinedRoom — se emite en join() y store()
class PlayerJoinedRoom implements ShouldBroadcast
{
    public function __construct(
        public Sala $sala,
        public $user,
        public ?int $seat
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('sala.' . $this->sala->id),
            new Channel('lobby'),
        ];
    }

    public function broadcastAs(): string { return 'PlayerJoinedRoom'; }
}
```

El payload que recibe el frontend contiene los atributos públicos del evento serializados como JSON: `{ sala: {...}, user: {...}, seat: 1 }`.

### Canal `lobby` — para qué sirve

El canal `lobby` es un canal público (sin autenticación) al que se suscriben todos los usuarios que están en la pantalla de lobby. Cuando cualquier jugador entra o sale de cualquier sala, o cuando una partida comienza, el lobby recibe el evento y recarga la lista de salas sin esperar al siguiente ciclo de polling.

```
Usuario A entra a Sala #3
    └─► SalaController::join() llama broadcast(new PlayerJoinedRoom($sala, $user, $seat))
    └─► Reverb emite el evento en canal 'sala.3' Y en canal 'lobby'
    └─► Todos los usuarios en el lobby reciben el evento por WS
    └─► Lobby.vue llama a loadSalas() → lista actualizada al instante
```

---

## Frontend — `Lobby.vue`

### Conexión WebSocket

Al montar el componente, el lobby se suscribe al canal `lobby` y escucha los tres eventos relevantes:

```javascript
onMounted(() => {
    loadSalas();

    window.Echo.channel('lobby')
        .listen('.PlayerJoinedRoom', () => loadSalas())
        .listen('.PlayerLeftRoom',   () => loadSalas())
        .listen('.GameStarted',      () => loadSalas());

    // Fallback: refresca la lista completa cada 30s
    // (captura salas nuevas creadas que no tienen evento propio)
    pollTimer = setInterval(loadSalas, 30000);
});

onUnmounted(() => {
    window.Echo.leave('lobby'); // limpia la suscripción
    clearInterval(pollTimer);
});
```

El punto clave del prefijo `.` en `.listen('.PlayerJoinedRoom', ...)`: la barra antes del nombre indica a Echo que use el nombre literal del evento tal como lo define `broadcastAs()`, sin añadir ningún prefijo de namespace automático.

### Por qué se sigue usando un poll de 30s

Los eventos del canal `lobby` cubren join/leave/game-start pero **no** la creación de nuevas salas: cuando alguien crea una sala, no hay un evento WebSocket que llegue al lobby (no existe un `SalaCreated` event). El fallback de 30 segundos garantiza que las salas nuevas aparezcan aunque nadie entre o salga de ellas.

### Lógica de botones por estado

| Situación | Botón |
|---|---|
| En la sala + jugando | **Entrar al juego** |
| En la sala + esperando (owner) | **Iniciar partida** |
| En la sala + esperando (no owner) | *Esperando inicio...* (desactivado) |
| No en la sala + `waiting` o `playing en apuesta` | **Unirse** / **Unirse (apostando)** |
| No en la sala + sala llena o `playing avanzado` | *En curso / Sala llena* (desactivado) |

### Unirse a sala en curso

```javascript
const joinSala = async (sala) => {
    await axios.post(`/api/salas/${sala.id}/join`);
    if (sala.status === 'playing') {
        // Navegar directamente a la mesa
        const res = await axios.get(`/api/salas/${sala.id}`);
        const partida = (res.data?.partidas ?? [])[0];
        if (partida) {
            router.push({ name: 'game.table', params: { salaId: sala.id, partidaId: partida.id } });
        }
    }
};
```

Si la sala estaba jugando, el join navega automáticamente a `GameTable` con el ID de la partida activa.

---

## Ciclo de vida de una sala

```
Crear sala (waiting)
    └─► Jugadores se unen (join)  → WS: PlayerJoinedRoom → lobby actualiza
    └─► Owner inicia partida → status: playing
            └─► WS: GameStarted → lobby actualiza status de la sala
            └─► Partida en curso...
            └─► Ronda finalizada → status: waiting (vuelve a aceptar jugadores)
            └─► Auto-restart → nueva partida
    └─► Jugador sale → WS: PlayerLeftRoom → lobby actualiza
    └─► Owner sale sin jugadores → status: finished (no aparece en lobby)
```

---

## Rutas registradas en `api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/salas',              [SalaController::class, 'index']);
    Route::post('/salas',             [SalaController::class, 'store']);
    Route::get('/salas/{sala}',       [SalaController::class, 'show']);
    Route::post('/salas/{sala}',      [SalaController::class, 'update']);
    Route::delete('/salas/{sala}',    [SalaController::class, 'destroy']);
    Route::post('/salas/{sala}/join', [SalaController::class, 'join']);
    Route::delete('/salas/{sala}/leave', [SalaController::class, 'leave']);

    Route::post('/salas/{sala}/iniciar', [BlackjackController::class, 'iniciar']);
    // ... rutas de partida
});
```

Todas las rutas de sala están protegidas por Sanctum. `EnsureFrontendRequestsAreStateful` está en el grupo `api` del Kernel, habilitando autenticación por cookie de sesión para la SPA.

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

Para verificar la conexión WebSocket: DevTools → Network → WS → debe aparecer una conexión activa a `ws://localhost:8080/app/{REVERB_APP_KEY}`.
