# Hoja de Ruta y Tareas

## 🚧 Siguiente Tarea Inmediata

**Métricas y progresión**
- [ ] Endpoints de estadísticas por músculo (`/api/v1/stats/volume`, etc.).
- [ ] 1RM histórico y progresión de carga por ejercicio.
- [ ] Integración con `cjuol/statguard` para análisis estadístico.
- [ ] Dashboard endpoint con resumen del mesociclo activo.

---

## 📅 Roadmap General

| Fase | Estado | Contenido |
|------|--------|-----------|
| 1. Entidades | ✅ Hecho | 18 entidades + 11 enums. |
| 2. Repositorios | ✅ Hecho | Repositorios enlazados en todas las entidades. |
| 3. Servicios | ✅ Hecho | PreviousPerformanceFetcher, TrainingDayService, WorkoutSessionService, SessionPreloader. |
| 4. Fixtures | ✅ Hecho | DataFixtures del Mesociclo 16 + catálogo base. |
| 5. Controllers/DTOs | ✅ Hecho | 6 controllers + 7 DTOs. Serialización con grupos. |
| 6. Métricas | 🚧 **Siguiente** | Progresión, StatGuard, dashboard. |
| 7. Integraciones | ⏳ Pendiente | Scaffold Garmin, Fitbit, Strava. |
| 8. Calidad | ⏳ Pendiente | PHPStan lvl 8, tests >80%, CI/CD. |

---

## 📝 Notas de desarrollo recientes

- `symfony/validator` añadido como dependencia — ejecutar `composer install` dentro del contenedor.
- Los grupos de serialización son: `mesocycle:read`, `mesocycle:sessions`, `training-day:read`, `session:read`, `performance:read`.
- `WorkoutSessionRepository::getAvgDurationInRange()` usa `UNIX_TIMESTAMP` — pendiente de migrar a `EXTRACT(EPOCH...)` cuando se implemente el dashboard.
