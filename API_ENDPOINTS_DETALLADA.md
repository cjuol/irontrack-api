# Documentacion detallada de endpoints (API v1)

Documento generado desde el codigo fuente actual (`development/src/Controller` y `development/src/DTO/Request`).
Pensado para consumo por un agente de IA que necesite modificar la API sin romper contratos.

## 1. Contexto general

- Base URL local: `http://localhost:8000`
- Prefijo API versionada: `/api/v1`
- Autenticacion: JWT stateless (LexikJWTAuthenticationBundle)
- Login publico: `POST /api/login`
- Todas las rutas bajo `/api` (salvo `/api/login`) requieren header:
  - `Authorization: Bearer <token>`
- IDs: UUID (string RFC 4122)

## 2. Seguridad y comportamiento transversal

- Firewall `login`: `json_login` con:
  - `check_path`: `/api/login`
  - `username_path`: `email`
  - `password_path`: `password`
- Firewall `api`: JWT obligatorio en `^/api`
- `access_control`:
  - `/api/login`: `PUBLIC_ACCESS`
  - `/api`: `IS_AUTHENTICATED_FULLY`

### Errores comunes globales

- `401 Unauthorized`: token ausente/invalido/expirado.
- `422 Unprocessable Entity`: payload invalido en endpoints con `#[MapRequestPayload]` + constraints.
- `403 Forbidden`: cuando el recurso existe pero pertenece a otro usuario (validacion manual en controllers).
- `404 Not Found`: recurso no encontrado.

## 3. Login JWT

### `POST /api/login`

Autentica usuario y devuelve JWT.

#### Request JSON
```json
{
  "email": "cristobal@trainingdiary.local",
  "password": "password123"
}
```

#### Response esperada (200)
```json
{
  "token": "<jwt>",
  "refresh_token": "<si estuviera habilitado>",
  "user": "<depende del handler>"
}
```

Nota: en esta configuracion, el success/failure lo gestiona Lexik (`authentication_success`/`authentication_failure`).

## 4. Endpoints de Programacion

### `GET /api/v1/mesocycles`

- Devuelve mesociclos del usuario autenticado.
- Serializacion: grupo `mesocycle:read`.

### `GET /api/v1/mesocycles/{id}`

- `id`: UUID de mesociclo.
- Busca el mesociclo con estructura completa para ese usuario.
- `404` si no existe o no pertenece al usuario.
- Serializacion: `mesocycle:read`.

### `GET /api/v1/mesocycles/{id}/sessions`

- `id`: UUID de mesociclo.
- Devuelve plantillas de sesion del mesociclo (carga plan completo por plantilla).
- `404` si mesociclo no existe/no pertenece.
- Serializacion: `mesocycle:sessions`.

## 5. Endpoints de Diario (Training Days)

### `GET /api/v1/training-days?year=YYYY&month=M`

- Query params opcionales:
  - `year` (por defecto: anio actual)
  - `month` (por defecto: mes actual)
- Devuelve dias del usuario para ese mes.
- Serializacion: `training-day:read`.

### `GET /api/v1/training-days/{date}`

- `date`: formato `YYYY-MM-DD` (requerido por route regex).
- `400` si la fecha no parsea.
- `404` si no existe ese dia para el usuario.
- Serializacion: `training-day:read`.

### `POST /api/v1/training-days/{date}/sessions`

Crea sesion en ese dia (o reutiliza/crea el TrainingDay) con tres modos:
- Desde `templateId`
- Desde `templateSortOrder`
- Libre (sin plantilla)

#### Body (`CreateSessionRequest`)
```json
{
  "templateSortOrder": 1,
  "templateId": "uuid-opcional"
}
```

#### Validaciones
- `templateSortOrder`: rango `1..20` (opcional)
- `templateId`: UUID valido (opcional)

#### Reglas
- Si `templateId` viene informado, debe existir y ser del usuario autenticado.
- Si hay error de negocio en preloader/servicio, devuelve `422` con `error`.

#### Responses
- `201 Created`: sesion creada.
- `400`: fecha invalida.
- `404`: plantilla no encontrada.
- `422`: error de dominio (RuntimeException).
- Serializacion: `session:read`.

### `PUT /api/v1/training-days/{date}/steps`

Registra pasos en un dia (crea el dia si no existe).

#### Body (`LogStepsRequest`)
```json
{
  "steps": 12345
}
```

#### Validaciones
- `steps`: entero `>= 0`, no nulo.

#### Responses
- `200 OK`: dia actualizado.
- `400`: fecha invalida.
- Serializacion: `training-day:read`.

## 6. Endpoints de Sesiones

### `GET /api/v1/sessions/{id}`

- `id`: UUID de sesion.
- `404` si no existe.
- `403` si no es del usuario.
- Serializacion: `session:read`.

### `GET /api/v1/sessions/{id}/exercises`

- Devuelve `exerciseEntries` de la sesion.
- `404` si sesion no existe.
- `403` si no pertenece al usuario.
- Serializacion: `session:read`.

### `POST /api/v1/sessions/{id}/exercises`

Anade ejercicio a una sesion (planificado o libre).

#### Body (`AddExerciseRequest`)
```json
{
  "exerciseId": "uuid-obligatorio",
  "plannedExerciseId": "uuid-opcional"
}
```

#### Validaciones
- `exerciseId`: requerido, no vacio, UUID.
- `plannedExerciseId`: UUID opcional.

#### Reglas
- Comprueba ownership de sesion.
- `exerciseId` debe existir en catalogo.
- Si `plannedExerciseId` viene, debe existir.

#### Responses
- `201 Created`: entrada creada.
- `404`: sesion/ejercicio/planificado no encontrado.
- `403`: acceso denegado.
- Serializacion: `session:read`.

### `POST /api/v1/sessions/{id}/cardio`

Registra bloque cardio en sesion.

#### Body (`LogCardioRequest`)
```json
{
  "type": "run",
  "durationSeconds": 1800,
  "distanceMeters": 5000,
  "avgSpeedKmh": 10.0,
  "inclinePct": 1.0,
  "notes": "sensaciones buenas",
  "weeklyCardioPlanId": "uuid-opcional"
}
```

#### Validaciones
- `type`: string no vacio.
- `durationSeconds`: entero positivo y no nulo.
- `distanceMeters`: float positivo opcional.
- `avgSpeedKmh`: float positivo opcional.
- `inclinePct`: float opcional `0..30`.
- `weeklyCardioPlanId`: UUID opcional.

#### Valores permitidos `type` (`CardioType`)
- `run`, `bike`, `row`, `ski`, `walk`, `rope`

#### Responses
- `201 Created`
- `400`: tipo de cardio invalido.
- `404`: sesion no encontrada.
- `403`: acceso denegado.
- Serializacion: `session:read`.

### `POST /api/v1/sessions/{id}/metabolic`

Registra bloque metabolico en sesion.

#### Body (`LogMetabolicRequest`)
```json
{
  "weeklyMetabolicPlanId": "uuid-opcional",
  "weekNumber": 1,
  "rounds": 6,
  "timeSeconds": 900,
  "result": "6 rounds + 20 reps",
  "notes": "ritmo estable"
}
```

#### Validaciones
- `weeklyMetabolicPlanId`: UUID opcional.
- `weekNumber`: `1..10` opcional.
- `rounds`: entero `>= 0` opcional.
- `timeSeconds`: entero positivo opcional.

#### Responses
- `201 Created`
- `404`: sesion no encontrada.
- `403`: acceso denegado.
- Serializacion: `session:read`.

### `PUT /api/v1/sessions/{id}/finish`

Marca sesion como finalizada y opcionalmente guarda RPE/notas.

#### Body (`FinishSessionRequest`)
```json
{
  "perceivedEffort": 8,
  "notes": "ultimo bloque exigente"
}
```

#### Validaciones
- `perceivedEffort`: opcional, rango `1..10`.
- `notes`: opcional.

#### Responses
- `200 OK`
- `404`: sesion no encontrada.
- `403`: acceso denegado.
- Serializacion: `session:read`.

## 7. Endpoints de Series y Entradas de ejercicio

### `POST /api/v1/exercise-entries/{id}/sets`

Registra una serie en una entrada de ejercicio.

#### Body (`LogSetRequest`)
```json
{
  "weightKg": 100,
  "reps": 5,
  "rir": 2,
  "toFailure": false,
  "plannedSetId": "uuid-opcional"
}
```

#### Validaciones
- `weightKg`: float `>= 0`, no nulo.
- `reps`: entero positivo, no nulo.
- `rir`: opcional `0..10`.
- `toFailure`: bool.
- `plannedSetId`: UUID opcional.

#### Responses
- `201 Created`
- `404`: entry no encontrada.
- `403`: acceso denegado.
- Serializacion: `session:read`.

### `PUT /api/v1/set-entries/{id}`

Edita una serie existente.

#### Body
Mismo contrato que `LogSetRequest`.

#### Responses
- `200 OK`
- `404`: serie no encontrada.
- `403`: acceso denegado.
- Serializacion: `session:read`.

### `DELETE /api/v1/set-entries/{id}`

Elimina una serie.

#### Responses
- `204 No Content`
- `404`: serie no encontrada.
- `403`: acceso denegado.

## 8. Endpoints de Rendimiento

### `GET /api/v1/exercises/{id}/last-performance`

- Devuelve ultimo rendimiento para un ejercicio del usuario.
- Incluye:
  - `exercise`
  - `summary`
  - `sets`
- `404` si ejercicio no existe.
- Serializacion: `performance:read`.

### `GET /api/v1/exercises/{id}/history?limit=10`

- Query param:
  - `limit` opcional (default `10`, max `50`).
- Devuelve historial agregado para ese ejercicio.
- `404` si ejercicio no existe.
- Serializacion: `performance:read`.

## 9. Endpoints de Metricas

### `GET /api/v1/metrics/exercises/{id}/progression?days=90`

- Query param:
  - `days` opcional (default `90`, max `365`).
- `404` si ejercicio no existe.
- Response:
```json
{
  "exercise": {"id": "uuid", "name": "Back Squat"},
  "days": 90,
  "dataPoints": [],
  "trend": {}
}
```

### `GET /api/v1/metrics/volume?from=YYYY-MM-DD&to=YYYY-MM-DD`

- Si `from` y `to` no vienen:
  - usa mesociclo activo
  - o fallback ultimos 28 dias
- `400` si fecha invalida.
- Response:
```json
{
  "from": "2026-02-10",
  "to": "2026-03-11",
  "muscles": []
}
```

### `GET /api/v1/metrics/personal-records`

- Devuelve PRs del usuario autenticado.
- Response:
```json
{
  "personalRecords": []
}
```

## 10. Dashboard

### `GET /api/v1/dashboard/summary`

- Devuelve resumen de dashboard calculado por `DashboardAggregator`.
- Contrato depende del agregador (response no serializada por grupos especificos).

## 11. Serializacion por endpoint

- `mesocycle:read`:
  - `GET /api/v1/mesocycles`
  - `GET /api/v1/mesocycles/{id}`
- `mesocycle:sessions`:
  - `GET /api/v1/mesocycles/{id}/sessions`
- `training-day:read`:
  - `GET /api/v1/training-days`
  - `GET /api/v1/training-days/{date}`
  - `PUT /api/v1/training-days/{date}/steps`
- `session:read`:
  - `POST /api/v1/training-days/{date}/sessions`
  - `GET /api/v1/sessions/{id}`
  - `GET /api/v1/sessions/{id}/exercises`
  - `POST /api/v1/sessions/{id}/exercises`
  - `POST /api/v1/sessions/{id}/cardio`
  - `POST /api/v1/sessions/{id}/metabolic`
  - `PUT /api/v1/sessions/{id}/finish`
  - `POST /api/v1/exercise-entries/{id}/sets`
  - `PUT /api/v1/set-entries/{id}`
- `performance:read`:
  - `GET /api/v1/exercises/{id}/last-performance`
  - `GET /api/v1/exercises/{id}/history`
- Sin grupo explicito:
  - endpoints de `metrics/*`
  - `dashboard/summary`

## 12. Ejemplos rapidos `curl`

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"cristobal@trainingdiary.local","password":"password123"}'
```

### Usar token
```bash
curl http://localhost:8000/api/v1/training-days \
  -H "Authorization: Bearer <TOKEN>"
```

### Crear sesion libre en fecha
```bash
curl -X POST http://localhost:8000/api/v1/training-days/2026-03-11/sessions \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### Registrar serie
```bash
curl -X POST http://localhost:8000/api/v1/exercise-entries/<ENTRY_UUID>/sets \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"weightKg":80,"reps":8,"rir":2,"toFailure":false}'
```

## 13. Notas para agentes que modifiquen esta API

- Mantener ownership checks por usuario en todos los endpoints de recursos anidados.
- Mantener IDs UUID en path/body.
- Si se agregan enums nuevos, mapear en Doctrine como `string`.
- Si se anaden endpoints bajo `/api`, quedaran protegidos por JWT salvo excepcion explicita en `access_control`.
- Para nuevos DTOs con `MapRequestPayload`, definir constraints para evitar romper consistencia de validaciones.
