# Contexto del Proyecto: Diario de Entrenamiento Hyrox

## 1. Descripción General
Aplicación web de diario de entrenamiento personal para un atleta de Hyrox.
- **Stack:** PHP 8.3, Symfony 7.4, Doctrine ORM, PostgreSQL.
- **Auth:** LexikJWTAuthenticationBundle (stateless JWT).
- **Métricas:** Librería estadística propia `cjuol/statguard` (Fase 6).

## 2. Decisiones de Arquitectura Importantes
- **Step goals NO son constantes:** Cada mesociclo tiene `stepGoalTrainingDay` y `stepGoalRestDay` propios (ej. M13=15000/10000, M16=10000/12000).
- **Campos transitorios en entidades:** `ExerciseEntry.previousPerformance` y `WorkoutSession.currentMetabolicPlan/currentCardioPlan` NO tienen `#[ORM\Column]`. Se rellenan en memoria por `SessionPreloader` y se exponen vía grupos de serialización.
- **Exercise denormalizado:** `ExerciseEntry` tiene FK directa a `Exercise` además de a `PlannedExercise` para queries de historial sin JOINs adicionales.
- **PostgreSQL Dates:** Para calcular duraciones en BD usar `EXTRACT(EPOCH FROM (col1 - col2))`, nunca `UNIX_TIMESTAMP`.
- **Serialización:** Se usa Symfony Serializer con `#[Groups]`. Los grupos son: `mesocycle:read`, `mesocycle:sessions`, `training-day:read`, `session:read`, `performance:read`, `exercise:read`.

## 3. Estado Actual del Modelo (✅ Implementado)

### Enums (`src/Enum/`)
- `SetType`, `SessionType`, `BlockType`, `CardioType`, `MetabolicFormat`, `CardioFormat`, `TrainingDayType`, `EquipmentType`, `WeightModifier`, `IntegrationProvider`, `SyncType`.

### Entidades (`src/Entity/`)
- **Usuarios:** `User`.
- **Catálogo:** `MuscleGroup`, `Exercise`.
- **Programación:** `Mesocycle`, `SessionTemplate`, `ExerciseBlock`, `PlannedExercise`, `PlannedSet`, `WeeklyMetabolicPlan`, `WeeklyCardioPlan`.
- **Logs:** `TrainingDay`, `WorkoutSession`, `ExerciseEntry`, `SetEntry`, `CardioEntry`, `MetabolicEntry`.
- **Integraciones:** `IntegrationAccount`, `ActivitySync` (scaffold).

### Repositorios Destacados (`src/Repository/`)
- `MesocycleRepository::findActiveForUser(user, date)` — crítico para step goals.
- `SessionTemplateRepository::findOneWithFullPlan()` — carga toda la estructura en 1 query.
- `SetEntryRepository` — el más complejo: historial, 1RM Epley, PRs, volumen por músculo.

### Servicios (`src/Service/`)
- `Log/PreviousPerformanceFetcher` — historial y resúmenes de rendimiento.
- `Log/TrainingDayService` — gestión de días y resolución de stepGoals desde el mesociclo activo.
- `Log/WorkoutSessionService` — CRUD completo de la sesión (sets, cardio, metabólico).
- `Program/SessionPreloader` — construye la sesión desde plantilla (`preloadFromTemplate`, `preloadFromSpecificTemplate`).

### Controllers (`src/Controller/`)
- `MesocycleController` — `GET /api/v1/mesocycles`, `GET /api/v1/mesocycles/{id}`, `GET /api/v1/mesocycles/{id}/sessions`.
- `TrainingDayController` — `GET /training-days`, `GET /training-days/{date}`, `POST /training-days/{date}/sessions`, `PUT /training-days/{date}/steps`.
- `SessionController` — `GET /sessions/{id}`, `GET /sessions/{id}/exercises`, `POST /sessions/{id}/exercises`, `POST /sessions/{id}/cardio`, `POST /sessions/{id}/metabolic`, `PUT /sessions/{id}/finish`.
- `ExerciseEntryController` — `POST /exercise-entries/{id}/sets`.
- `SetEntryController` — `PUT /set-entries/{id}`, `DELETE /set-entries/{id}`.
- `PerformanceController` — `GET /exercises/{id}/last-performance`, `GET /exercises/{id}/history`.

### DTOs de Request (`src/DTO/Request/`)
- `CreateSessionRequest`, `LogStepsRequest`, `AddExerciseRequest`, `LogSetRequest`, `LogCardioRequest`, `LogMetabolicRequest`, `FinishSessionRequest`.

## 4. API Endpoints (v1) — Implementados

### Programación
- `GET /api/v1/mesocycles`
- `GET /api/v1/mesocycles/{id}`
- `GET /api/v1/mesocycles/{id}/sessions`
- `GET /api/v1/sessions/{id}/exercises`

### Diario
- `GET /api/v1/training-days`
- `GET /api/v1/training-days/{date}`
- `POST /api/v1/training-days/{date}/sessions`
- `PUT /api/v1/training-days/{date}/steps`
- `GET /api/v1/sessions/{id}`
- `POST /api/v1/sessions/{id}/exercises`
- `POST /api/v1/exercise-entries/{id}/sets`
- `PUT /api/v1/set-entries/{id}`
- `DELETE /api/v1/set-entries/{id}`
- `POST /api/v1/sessions/{id}/cardio`
- `POST /api/v1/sessions/{id}/metabolic`
- `PUT /api/v1/sessions/{id}/finish`

### Rendimiento
- `GET /api/v1/exercises/{id}/last-performance`
- `GET /api/v1/exercises/{id}/history`
