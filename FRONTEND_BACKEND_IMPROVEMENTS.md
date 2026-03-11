# Mejoras Backend — Requeridas por el Frontend

Documento vivo. Añadir nuevas entradas a medida que el desarrollo del frontend
detecte nuevos desajustes de contrato o necesidades de API.

---

## [ALTA] Añadir campos de resumen a `WorkoutSession` en el grupo `training-day:read`

### Problema detectado
El grupo de serialización `training-day:read` en `WorkoutSession` incluye
`id`, `startedAt`, `finishedAt`, `durationSeconds`, `isFinished`… pero
**no incluye `exerciseEntries`** (solo está en `session:read`).

Esto provoca que el frontend:
1. Crashee con `Cannot read properties of undefined (reading 'length')` al
   intentar mostrar el conteo de ejercicios en las tarjetas del historial.
2. Tenga que hacer **una petición extra** por cada sesión abierta
   (`GET /api/v1/sessions/{id}`) únicamente para mostrar el número de
   ejercicios en la tarjeta del dashboard — lo que es ineficiente en móvil.
3. No pueda calcular `totalSets` mensual en cliente sin esas peticiones extra.

### Solución propuesta
Añadir tres campos calculados a `WorkoutSession` bajo el grupo `training-day:read`.
Son escalares baratos (no colecciones anidadas), evitan N+1 en el frontend y
no rompen el contrato actual:

```php
// src/Entity/Log/WorkoutSession.php

#[Groups(['training-day:read'])]
public function getExerciseCount(): int
{
    return $this->exerciseEntries->count();
}

#[Groups(['training-day:read'])]
public function getSetCount(): int
{
    $count = 0;
    foreach ($this->exerciseEntries as $entry) {
        $count += $entry->getSetEntries()->count();
    }
    return $count;
}

#[Groups(['training-day:read'])]
public function getTotalVolumeKg(): float
{
    $volume = 0.0;
    foreach ($this->exerciseEntries as $entry) {
        foreach ($entry->getSetEntries() as $set) {
            $volume += $set->getWeightKg() * $set->getRepsCompleted();
        }
    }
    return round($volume, 2);
}
```

### Campos resultantes en la respuesta de `GET /api/v1/training-days`
```json
{
  "workoutSessions": [
    {
      "id": "uuid",
      "startedAt": "...",
      "finishedAt": "...",
      "durationSeconds": 3600,
      "isFinished": true,
      "exerciseCount": 4,
      "setCount": 16,
      "totalVolumeKg": 4200.0
    }
  ]
}
```

### Impacto en el frontend cuando se implemente
- `TrainingDayCard` puede usar `session.exerciseCount` directamente.
- `DashboardPage` puede sumar `setCount` y `totalVolumeKg` sin peticiones extra.
- Se puede eliminar el workaround `?.length ?? 0` y los `??` defensivos
  del dashboard una vez actualizado el tipo `SessionSummary`.

---

## [MEDIA] Campo `exerciseName` en `ExerciseEntry` disponible en `training-day:read`

### Problema detectado
Si en el futuro el frontend necesita mostrar el nombre de los ejercicios
directamente en la lista (sin abrir el sheet de detalle), tampoco está disponible
en `training-day:read`; `ExerciseEntry` y sus relaciones solo se serializan
en `session:read`.

### Solución propuesta
Si se implementa la mejora anterior (contadores), considerar añadir también
un campo `exerciseNames: string[]` calculado en `WorkoutSession` bajo
`training-day:read` para poder mostrar un resumen tipo "Sentadilla, Press Banca…"
sin peticiones extra.

---

## [BAJA] Endpoint de resumen mensual de estadísticas

### Problema detectado
El frontend calcula `totalDays` y `totalSets` mensual en cliente a partir de
la lista completa de `training-days`. Cuando el historial crezca, esto requiere
traer muchos registros solo para agregar dos números.

### Solución propuesta
Añadir un endpoint de estadísticas:

```
GET /api/v1/training-days/stats?year=YYYY&month=M
```

Respuesta sugerida:
```json
{
  "trainingDays": 12,
  "totalSets": 198,
  "totalVolumeKg": 48230.5,
  "avgSessionDurationSeconds": 3720
}
```
