# [Nombre de tu Proyecto]

## Descripción

[Describe aquí tu proyecto: qué hace, para qué sirve, problemas que resuelve, etc.]

## Características

- [Característica 1]
- [Característica 2]
- [Característica 3]

## Tecnologías Utilizadas

- PHP 8.3
- MariaDB
- [Otras tecnologías que uses en tu proyecto]

## Requisitos Previos

- Docker Engine 20.10 o superior
- Docker Compose v2.0 o superior
- Git

## Instalación y Configuración

1. **Clona este repositorio**
   ```bash
   git clone [URL-DE-TU-REPOSITORIO]
   cd [nombre-directorio]
   ```

2. **Configura las variables de entorno**
   
   Edita el archivo [docker-compose.yml](docker-compose.yml) y modifica las credenciales de la base de datos según tus necesidades:
   - Base de datos: `demo_db` → Cambia a tu nombre de BD
   - Usuario: `demo_user` → Cambia a tu usuario
   - Contraseña: `demo_password` → Cambia a tu contraseña

3. **Inicia los contenedores**
   ```bash
   docker-compose up -d
   ```

4. **Accede a la aplicación**
   
   Abre tu navegador en: `http://localhost`

## Uso

[Explica aquí cómo usar tu aplicación: funcionalidades principales, ejemplos de uso, capturas de pantalla si es necesario]

## Estructura del Proyecto

```
.
├── development/           # Código fuente de tu aplicación
├── web/                  # Configuración del entorno Docker
├── docker-compose.yml    # Orquestación de servicios
└── README.md
```

## Contribuir

[Explica cómo otros pueden contribuir a tu proyecto]

## Licencia

[Especifica la licencia de tu proyecto]

## Contacto

[Tu nombre] - [Tu usuario de GitHub] - [Tu email]

---

# 📦 Guía del Entorno de Desarrollo Docker

Este proyecto utiliza un entorno de desarrollo completamente containerizado con Docker. A continuación se detalla cómo funciona y cómo utilizarlo.

## Componentes del Entorno

### Servicios Docker

El entorno incluye dos servicios principales definidos en [docker-compose.yml](docker-compose.yml):

1. **Web (Apache + PHP 8.3)**
   - Puerto: 80
   - Incluye: Composer, PHPUnit, extensiones PHP comunes
   - Directorio de trabajo: `/var/www/html/demo`

2. **Base de Datos (MariaDB)**
   - Puerto: 3306
   - Versión: MariaDB (última estable)
   - Persistencia: Volumen Docker

### Credenciales de Base de Datos

Las credenciales por defecto están en [docker-compose.yml](docker-compose.yml):

- **Host:** `db`
- **Puerto:** `3306`
- **Base de datos:** `demo_db`
- **Usuario:** `demo_user`
- **Contraseña:** `demo_password`
- **Usuario root:** `root`
- **Contraseña root:** `example`

⚠️ **Importante:** Cambia estas credenciales antes de usar en producción.

## Comandos Docker Útiles

### Gestión de Contenedores

```bash
# Iniciar los contenedores
docker-compose up -d

# Detener los contenedores
docker-compose down

# Reiniciar los contenedores
docker-compose restart

# Ver estado de los contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs solo del servicio web
docker logs -f docker-env-web-1
```

### Acceso a los Contenedores

```bash
# Acceder al contenedor web (bash interactivo)
docker exec -it docker-env-web-1 bash

# Acceder al contenedor de base de datos
docker exec -it docker-env-db-1 bash
```

### Comandos de Desarrollo

```bash
# Ejecutar Composer
docker exec docker-env-web-1 composer install
docker exec docker-env-web-1 composer update
docker exec docker-env-web-1 composer require [paquete]

# Ejecutar PHPUnit
docker exec docker-env-web-1 phpunit
docker exec docker-env-web-1 phpunit --filter [test-name]

# Ejecutar scripts PHP
docker exec docker-env-web-1 php script.php

# Ejecutar comandos de Symfony (si usas Symfony)
docker exec docker-env-web-1 php bin/console [comando]

# Ejecutar comandos de Laravel (si usas Laravel)
docker exec docker-env-web-1 php artisan [comando]
```

## Configuración del Entorno

### Selección de Framework

El entorno soporta la creación automática de proyectos. Edita [docker-compose.yml](docker-compose.yml) y añade la variable `FRAMEWORK`:

```yaml
services:
  web:
    environment:
      - FRAMEWORK=laravel  # Opciones: symfony, laravel, none
```

**Opciones disponibles:**
- `symfony` - Crea automáticamente un proyecto Symfony 6.4
- `laravel` - Crea automáticamente un proyecto Laravel con Filament y Livewire
- `none` (por defecto) - No crea ningún proyecto automáticamente

**Nota:** La creación solo ocurre si no existe `composer.json` en `development/`

### Directorio de Desarrollo

- **Local:** `./development/`
- **Contenedor:** `/var/www/html/demo`

Todo el código que escribas en `development/` se sincroniza automáticamente con el contenedor.

### Personalización Avanzada

#### Agregar Extensiones PHP

Edita [web/Dockerfile](web/Dockerfile) y añade las extensiones necesarias:

```dockerfile
RUN docker-php-ext-install [extension-name]
```

#### Modificar Inicialización

Edita [web/entrypoint.sh](web/entrypoint.sh) para personalizar lo que ocurre al iniciar el contenedor.

#### Cambiar Puertos

Edita [docker-compose.yml](docker-compose.yml):

```yaml
services:
  web:
    ports:
      - "8080:80"  # Cambiar puerto 80 a 8080
```

## Solución de Problemas

### Los contenedores no inician

```bash
# Ver logs detallados
docker-compose logs

# Reconstruir los contenedores
docker-compose build --no-cache
docker-compose up -d
```

### Error de permisos en archivos

```bash
# Desde dentro del contenedor web
docker exec -it docker-env-web-1 bash
chown -R www-data:www-data /var/www/html/demo
```

### Puerto ya en uso

Si el puerto 80 o 3306 ya está en uso, cambia los puertos en [docker-compose.yml](docker-compose.yml).

### Base de datos no conecta

Verifica que:
- El contenedor de base de datos esté corriendo: `docker-compose ps`
- Las credenciales en tu código coincidan con [docker-compose.yml](docker-compose.yml)
- Uses `db` como host, no `localhost`

## Recursos Adicionales

- [Documentación de Docker](https://docs.docker.com/)
- [Documentación de Docker Compose](https://docs.docker.com/compose/)
- [PHP Docker Official Image](https://hub.docker.com/_/php)

---

**Plantilla creada por:** Cristobal Jurado Oller - [@Cjuol](https://github.com/Cjuol)  
**Repositorio de la plantilla:** [https://github.com/cjuol/docker-env](https://github.com/cjuol/docker-env)