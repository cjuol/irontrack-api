# Hoja de Ruta y Tareas

## 🚧 Siguiente Tarea Inmediata

**Integraciones externas**
- [ ] Scaffold Garmin — OAuth 2.0, sync de pasos, HRV y sueño.
- [ ] Scaffold Fitbit — OAuth 2.0, sync de pasos y actividad.
- [ ] Scaffold Strava — OAuth 2.0, import de workouts de cardio.

---

## 📅 Roadmap General

| Fase | Estado | Contenido |
|------|--------|-----------|
| 1. Entidades | ✅ Hecho | 18 entidades + 11 enums. |
| 2. Repositorios | ✅ Hecho | Repositorios enlazados en todas las entidades. |
| 3. Servicios | ✅ Hecho | PreviousPerformanceFetcher, TrainingDayService, WorkoutSessionService, SessionPreloader. |
| 4. Fixtures | ✅ Hecho | DataFixtures del Mesociclo 16 + catálogo base. |
| 5. Controllers/DTOs | ✅ Hecho | 6 controllers + 7 DTOs. Serialización con grupos. |
| 6. Métricas | ✅ Hecho | ProgressionAnalyzer (StatGuard), DashboardAggregator, MetricsController, DashboardController. |
| 7. Integraciones | 🚧 **Siguiente** | Scaffold Garmin, Fitbit, Strava. |
| 8. Calidad | ⏳ Pendiente | PHPStan lvl 8, tests >80%, CI/CD. |

---

## 📝 Notas de desarrollo recientes

- `symfony/validator` añadido como dependencia — ejecutar `composer install` dentro del contenedor.
- Los grupos de serialización son: `mesocycle:read`, `mesocycle:sessions`, `training-day:read`, `session:read`, `performance:read`.
- `cjuol/statguard` v1.1 instalado. Namespace: `Cjuol\StatGuard\`. Clases: `RobustStats`, `ClassicStats`, `StatsComparator`.
- La migración de BD es ahora `Version20260311095437.php` (PostgreSQL nativo). La versión anterior era MySQL y fue reemplazada.
- `WorkoutSessionRepository::getAvgDurationInRange()` usa SQL nativo con `EXTRACT(EPOCH FROM ...)` — compatible con PostgreSQL.
