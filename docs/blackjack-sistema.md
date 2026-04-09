# Sistema de Salas y Blackjack — Documentación técnica

## Índice
1. [Estructura de la BD](#estructura-bd)
2. [Sistema de Salas](#sistema-salas)
3. [Sistema de Juego](#sistema-juego)
4. [Flujo completo](#flujo-completo)
5. [Estados](#estados)
6. [Endpoints API](#endpoints-api)
7. [Eventos de broadcast](#eventos)
8. [Estructura de datos](#estructuras)

---

## 1. Estructura de la BD {#estructura-bd}

### Tablas principales

```
users
├── id
├── name
├── email
└── ...

salas
├── id
├── nombre_sala
├── code          (único, formato BJ-XXXX, generado automáticamente)
├── status        (waiting | playing | finished)
├── max_players   (default: 3)
└── owner_id      (FK → users)

sala_usuario      [pivot]
├── sala_id       (FK → salas)
├── user_id       (FK → users)
├── status        (sitting | ready | playing | spectating)
├── chips         (fichas del jugador en la sala, default: 1000)
└── seat          (asiento 1..max_players)

partidas
├── id
├── sala_id       (FK → salas)
├── mano_dealer   (JSON — array de cartas)
└── estado        (en_curso | finalizada)

partida_usuario
├── id
├── partida_id    (FK → partidas)
├── user_id       (FK → users)
├── estado        (apostando | esperando | jugando | plantado | reventado | doblado | dividido | finalizado)
├── apuesta_total (integer)
├── mano_usuario  (JSON — array de cartas)
├── resultado     (nullable → blackjack | gana | empata | pierde)
└── balance_resultado (nullable — positivo o negativo)

manos             [historial contable]
├── id
├── sala_id       (FK → salas)
├── user_id       (FK → users)
├── partida_id    (FK → partidas)
├── creditos_jugados
└── creditos_ganados

ajustes
├── clave         (apuesta_minima | apuesta_maxima | ...)
└── valor
```

### Relaciones clave

- `Sala` → tiene muchos `players` (via `sala_usuario`) y muchos `manos`
- `Partida` → pertenece a una `Sala`, tiene muchos `PartidaUser` y muchos `Mano`
- `PartidaUser` → pertenece a `Partida` y a `User`

---

## 2. Sistema de Salas {#sistema-salas}

### Crear sala
- Cualquier usuario autenticado puede crear una sala
- Al crearla, el creador se convierte en **owner** y ocupa el **asiento 1** con 1000 chips
- Se genera automáticamente un código único (`BJ-XXXX`)
- El estado inicial es `waiting`

### Unirse a sala
- Validaciones antes de unirse:
  - El jugador no puede estar ya en la sala
  - La sala no puede estar llena (`activePlayers >= max_players`)
  - El estado debe ser `waiting` (no se puede entrar con partida en curso)
- Al unirse se asigna el siguiente asiento disponible y 1000 chips

### Salir de sala
- Si el que sale es el **owner**, la propiedad se transfiere al siguiente jugador activo
- Si era el **único jugador**, la sala se elimina automáticamente

### Estados de sala

| Estado    | Descripción                              |
|-----------|------------------------------------------|
| `waiting` | Esperando jugadores, se puede unir       |
| `playing` | Partida en curso, no se puede unir       |
| `finished`| Partida finalizada (vuelve a `waiting`)  |

---

## 3. Sistema de Juego {#sistema-juego}

### Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Services/blackjackservice.php` | Toda la lógica del juego |
| `app/Http/Controllers/Api/blackjackController.php` | Endpoints HTTP |
| `app/Models/Partida.php` | Modelo de partida |
| `app/Models/PartidaUser.php` | Modelo de mano por jugador |

### Baraja
- 52 cartas estándar (4 palos × 13 valores)
- Se mezcla en cada reparto con `shuffle()`
- Estructura de cada carta: `{ "palo": "hearts", "valor": "A" }`
- Palos: `hearts | diamonds | clubs | spades`
- Valores: `2 3 4 5 6 7 8 9 10 J Q K A`

### Puntuación
- `J`, `Q`, `K` → 10 puntos
- `A` → 11 puntos (se reduce a 1 si la mano supera 21)
- Números → valor nominal
- Blackjack: 21 exacto con exactamente 2 cartas

### Reglas del dealer
- Pide carta mientras su puntuación sea < 17
- Planta automáticamente en 17 o más

### Pagos
| Resultado  | Balance                  |
|------------|--------------------------|
| `blackjack`| +apuesta × 1.5 (pago 3:2)|
| `gana`     | +apuesta                 |
| `empata`   | 0                        |
| `pierde`   | -apuesta                 |

---

## 4. Flujo completo {#flujo-completo}

```
1. CREAR SALA
   POST /salas
   → Sala creada (status: waiting), owner en asiento 1

2. UNIRSE (otros jugadores)
   POST /salas/{sala}/join
   → Jugador añadido a sala_usuario

3. INICIAR PARTIDA (solo owner)
   POST /salas/{sala}/iniciar
   → Crea Partida (estado: en_curso)
   → Crea PartidaUser por cada jugador (estado: apostando)
   → Sala pasa a status: playing
   → Broadcast: GameStarted

4. FASE DE APUESTAS
   POST /partidas/{partida}/apostar  (cada jugador)
   → PartidaUser.estado: apostando → esperando
   → Cuando TODOS han apostado → repartirCartasIniciales()

5. REPARTO INICIAL (automático)
   → 2 cartas a cada jugador
   → 2 cartas al dealer (1ª visible, 2ª oculta)
   → Broadcast: CardDealt (por cada carta)
   → Si alguien tiene blackjack → estado: finalizado, resultado: blackjack
   → Primer jugador pasa a estado: jugando
   → Broadcast: TurnChanged

6. TURNO DE JUGADORES (en orden de ID)
   El jugador con estado 'jugando' puede:

   ├── HIT   POST /partidas/{partida}/hit
   │   → +1 carta
   │   → Si puntuación > 21 → estado: reventado → siguiente turno
   │
   ├── STAND  POST /partidas/{partida}/stand
   │   → estado: plantado → siguiente turno
   │
   ├── DOBLAR  POST /partidas/{partida}/doblar
   │   → Solo con exactamente 2 cartas
   │   → apuesta × 2
   │   → +1 carta → estado: doblado (o reventado)
   │   → siguiente turno automático
   │
   └── DIVIDIR  POST /partidas/{partida}/dividir
       → Solo con 2 cartas del mismo valor
       → Crea un 2º PartidaUser con la 2ª carta + carta nueva
       → Mano original continúa con 1ª carta + carta nueva

7. TURNO DEL DEALER (automático cuando todos terminan)
   → Revela carta oculta
   → Pide cartas mientras puntuación < 17
   → Broadcast: CardDealt

8. RESOLUCIÓN (automática)
   Por cada jugador:
   ├── reventado           → pierde
   ├── blackjack           → blackjack (si dealer no tiene BJ también)
   ├── dealer reventado    → gana
   ├── puntuación > dealer → gana
   ├── puntuación = dealer → empata
   └── puntuación < dealer → pierde

   → Crea registro en tabla manos (historial)
   → Partida.estado → finalizada
   → Sala.status → waiting
   → Broadcast: RoundEnded

9. NUEVA RONDA
   → El owner vuelve a llamar POST /salas/{sala}/iniciar
```

---

## 5. Estados {#estados}

### Estados de PartidaUser

| Estado      | Cuándo se asigna                              |
|-------------|-----------------------------------------------|
| `apostando` | Al crear el registro (inicio de partida)      |
| `esperando` | Tras apostar, esperando que los demás apuesten|
| `jugando`   | Es el turno de este jugador                   |
| `plantado`  | El jugador hizo stand                         |
| `reventado` | Puntuación > 21                               |
| `doblado`   | Dobló la apuesta y recibió 1 carta            |
| `dividido`  | Dividió su mano (la mano original)            |
| `finalizado`| Ronda terminada (también para blackjacks)     |

### Estados de Sala

| Estado    | Transición                                    |
|-----------|-----------------------------------------------|
| `waiting` | → `playing` al iniciar partida                |
| `playing` | → `waiting` al resolver la ronda              |

### Estados de Partida

| Estado      | Transición                                   |
|-------------|----------------------------------------------|
| `en_curso`  | → `finalizada` al resolver la ronda          |

---

## 6. Endpoints API {#endpoints-api}

Todos requieren autenticación Sanctum: `Authorization: Bearer {token}`

### Salas

| Método | Ruta                    | Descripción                          | Restricción   |
|--------|-------------------------|--------------------------------------|---------------|
| GET    | `/salas`                | Listar todas las salas               | —             |
| POST   | `/salas`                | Crear sala                           | —             |
| GET    | `/salas/{sala}`         | Ver sala                             | —             |
| PUT    | `/salas/{sala}`         | Editar sala                          | —             |
| DELETE | `/salas/{sala}`         | Eliminar sala                        | —             |
| POST   | `/salas/{sala}/join`    | Unirse a sala                        | —             |
| DELETE | `/salas/{sala}/leave`   | Salir de sala                        | —             |

### Juego

| Método | Ruta                        | Descripción              | Restricción         |
|--------|-----------------------------|--------------------------|---------------------|
| POST   | `/salas/{sala}/iniciar`     | Iniciar partida          | Solo owner          |
| GET    | `/partidas/{partida}/estado`| Ver estado de la partida | —                   |
| POST   | `/partidas/{partida}/apostar`| Apostar                 | estado: apostando   |
| POST   | `/partidas/{partida}/hit`   | Pedir carta              | estado: jugando     |
| POST   | `/partidas/{partida}/stand` | Plantarse                | estado: jugando     |
| POST   | `/partidas/{partida}/doblar`| Doblar apuesta           | estado: jugando, 2 cartas |
| POST   | `/partidas/{partida}/dividir`| Dividir mano            | estado: jugando, 2 cartas iguales |

### Historial

| Método | Ruta          | Descripción          |
|--------|---------------|----------------------|
| GET    | `/manos`      | Historial de manos   |
| GET    | `/manos/{id}` | Ver mano específica  |

### Body de peticiones

**POST /salas**
```json
{ "nombre_sala": "Mi sala" }
```

**POST /partidas/{partida}/apostar**
```json
{ "apuesta": 100 }
```

---

## 7. Eventos de broadcast {#eventos}

Canal: `sala.{sala_id}` (público)

| Evento          | Cuándo se emite                        | Datos principales                       |
|-----------------|----------------------------------------|-----------------------------------------|
| `PlayerJoinedRoom` | Un jugador entra a la sala          | sala, user, seat                        |
| `PlayerLeftRoom`   | Un jugador sale de la sala          | sala, user                              |
| `GameStarted`      | La partida arranca                  | sala, partida_id, jugadores, apuesta_min/max |
| `CardDealt`        | Se reparte una carta                | sala, userId (null=dealer), card, hidden |
| `TurnChanged`      | Cambia el turno                     | sala, userId (null=dealer), timeout     |
| `RoundEnded`       | La ronda termina                    | sala, resultados[], manoDealer[]        |

---

## 8. Estructura de datos {#estructuras}

### Carta
```json
{
  "palo": "hearts | diamonds | clubs | spades",
  "valor": "2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | J | Q | K | A"
}
```

### Respuesta GET /partidas/{partida}/estado
```json
{
  "id": 5,
  "sala_id": 3,
  "estado": "en_curso",
  "mano_dealer": [
    { "palo": "hearts", "valor": "K" },
    { "palo": "clubs",  "valor": "7" }
  ],
  "partida_usuarios": [
    {
      "id": 12,
      "user_id": 1,
      "estado": "jugando",
      "apuesta_total": 100,
      "mano_usuario": [
        { "palo": "diamonds", "valor": "A" },
        { "palo": "spades",   "valor": "9" }
      ],
      "resultado": null,
      "balance_resultado": null,
      "usuario": { "id": 1, "name": "Alex" }
    }
  ]
}
```

### Respuesta POST /partidas/{partida}/apostar / hit / stand
```json
{
  "id": 12,
  "partida_id": 5,
  "user_id": 1,
  "estado": "esperando",
  "apuesta_total": 100,
  "mano_usuario": [],
  "resultado": null,
  "balance_resultado": null
}
```

### Ajustes relevantes (tabla ajustes)

| clave           | valor por defecto |
|-----------------|-------------------|
| `apuesta_minima`| 10                |
| `apuesta_maxima`| 1000              |
