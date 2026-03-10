#!/bin/bash
set -e

PROJECT_DIR="/var/www/html/demo"
FRAMEWORK="${FRAMEWORK:-none}"  # Valores: symfony, laravel, none

# Crear el directorio del proyecto Symfony si no existe
if [ "$FRAMEWORK" = "symfony" ] && [ ! -f "$PROJECT_DIR/composer.json" ]; then
  echo "⚙️  Creando proyecto Symfony en $PROJECT_DIR..."
  composer create-project symfony/skeleton:"6.4.*" "$PROJECT_DIR" --no-interaction --prefer-dist
  echo "✅ Proyecto Symfony creado correctamente."
elif [ "$FRAMEWORK" = "symfony" ]; then
  echo "✅ Proyecto Symfony ya presente, no se vuelve a crear."
fi

# Crear el directorio del proyecto Laravel si no existe con Filament y Livewire
if [ "$FRAMEWORK" = "laravel" ] && [ ! -f "$PROJECT_DIR/composer.json" ]; then
  echo "⚙️  Creando proyecto Laravel en $PROJECT_DIR..."
  composer create-project laravel/laravel "$PROJECT_DIR" --no-interaction --prefer-dist
  
  echo "⚙️  Instalando Livewire..."
  cd "$PROJECT_DIR" && composer require livewire/livewire
  
  echo "⚙️  Instalando Filament..."
  cd "$PROJECT_DIR" && composer require filament/filament:"^3.0"
  
  echo "✅ Laravel con Filament y Livewire instalados correctamente."
elif [ "$FRAMEWORK" = "laravel" ]; then
  echo "✅ Proyecto Laravel ya presente, no se vuelve a crear."
fi

chown -R www-data:www-data "$PROJECT_DIR"

exec apache2-foreground