# Training Diary

API REST de diario de entrenamiento personal para atletas de Hyrox. Permite registrar sesiones de entrenamiento, seguir la progresión por ejercicio, gestionar mesociclos y consultar métricas de rendimiento.

## Stack

- **Backend:** Symfony 7.4 + Doctrine ORM
- **Base de datos:** PostgreSQL 16
- **Auth:** LexikJWTAuthenticationBundle (JWT stateless)
- **PHP:** 8.3
- **Entorno:** Docker (Apache + PHP-FPM)
- **Estadística:** cjuol/statguard v1.1 (RobustStats, StatsComparator)

## Requisitos

- Docker Engine 20.10+
- Docker Compose v2.0+
- Git

## Instalación

1. **Clona el repositorio**
   ```bash
   git clone https://github.com/Cjuol/ideal-potato.git
   cd ideal-potato
   ```

2. **Configura el entorno**
   ```bash
   cp development/.env development/.env.local
   # Edita .env.local con tus valores (APP_SECRET, JWT_PASSPHRASE...)
   ```

3. **Levanta los contenedores**
   ```bash
   docker-compose up -d
   ```

4. **Genera las claves JWT**
   ```bash
   docker exec ideal-potato-web-1 php bin/console lexik:jwt:generate-keypair
   ```

5. **Ejecuta las migraciones y los fixtures**
   ```bash
   docker exec ideal-potato-web-1 php bin/console doctrine:migrations:migrate --no-interaction
   docker exec ideal-potato-web-1 php bin/console doctrine:fixtures:load --no-interaction
   ```

La API queda disponible en `http://localhost`.

## Credenciales de base de datos (desarrollo)

| Parámetro | Valor |
|-----------|-------|
| Host | `db` |
| Puerto | `5432` |
| Base de datos | `training_diary` |
| Usuario | `app` |
| Contraseña | `secret` |

⚠️ Cambia estas credenciales antes de cualquier despliegue.

## API Endpoints (v1)

### Programación
```
GET    /api/v1/mesocycles
GET    /api/v1/mesocycles/{id}
GET    /api/v1/mesocycles/{id}/sessions
GET    /api/v1/sessions/{id}/exercises
```

### Diario de entrenamiento
```
GET    /api/v1/training-days
GET    /api/v1/training-days/{date}
POST   /api/v1/training-days/{date}/sessions
PUT    /api/v1/training-days/{date}/steps
GET    /api/v1/sessions/{id}
POST   /api/v1/sessions/{id}/exercises
POST   /api/v1/exercise-entries/{id}/sets
PUT    /api/v1/set-entries/{id}
DELETE /api/v1/set-entries/{id}
POST   /api/v1/sessions/{id}/cardio
POST   /api/v1/sessions/{id}/metabolic
PUT    /api/v1/sessions/{id}/finish
```

### Rendimiento
```
GET    /api/v1/exercises/{id}/last-performance
GET    /api/v1/exercises/{id}/history
```

### Métricas y progresión
```
GET    /api/v1/metrics/exercises/{id}/progression   ?days=90
GET    /api/v1/metrics/volume                        ?from=YYYY-MM-DD&to=YYYY-MM-DD
GET    /api/v1/metrics/personal-records
GET    /api/v1/dashboard/summary
```

Todos los endpoints protegidos requieren `Authorization: Bearer <token>`.

## Estructura del proyecto

```
.
├── development/           # Código fuente Symfony
│   ├── src/
│   │   ├── Controller/    # Endpoints API v1
│   │   ├── Entity/        # Entidades Doctrine (18 entidades)
│   │   ├── Enum/          # Enums de dominio (11 enums)
│   │   ├── Repository/    # Repositorios con queries complejas
│   │   ├── Service/       # Lógica de negocio
│   │   └── DataFixtures/  # Mesociclo 16 + catálogo base
│   └── config/
├── web/                   # Dockerfile + configuración Apache
├── docker-compose.yml
└── README.md
```

## Comandos útiles

```bash
# Acceder al contenedor web
docker exec -it ideal-potato-web-1 bash

# Consola Symfony
docker exec ideal-potato-web-1 php bin/console [comando]

# Tests
docker exec ideal-potato-web-1 phpunit

# Ver logs
docker-compose logs -f web
```

## Hoja de ruta

| Fase | Estado |
|------|--------|
| Entidades + Enums | ✅ |
| Repositorios | ✅ |
| Servicios de dominio | ✅ |
| Fixtures (M16) | ✅ |
| Controllers + DTOs | ✅ |
| Métricas y progresión | ✅ |
| Integraciones (Garmin, Fitbit, Strava) | ⏳ |
| PHPStan lvl 8 + tests >80% + CI/CD | ⏳ |

---

**Autor:** Cristóbal Jurado Oller — [@Cjuol](https://github.com/Cjuol)
