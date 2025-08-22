#!/bin/bash
set -e

echo "Starting TALL Stack Laravel setup..."

# ------------------------------
# Check Docker Compose
# ------------------------------
DOCKER_COMPOSE_SRC="docker-compose.yml"

if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose -f $DOCKER_COMPOSE_SRC"
elif command -v docker &> /dev/null && docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose -f $DOCKER_COMPOSE_SRC"
else
    echo "Docker or Docker Compose not found!"
    exit 1
fi
echo "Using: $DOCKER_COMPOSE"

# ------------------------------
# Stop old containers (safely)
# ------------------------------
echo "Checking and stopping old Docker containers if present..."
CONTAINERS=$($DOCKER_COMPOSE ps -a -q)

if [ "$CONTAINERS" ]; then
    echo "Old containers found, stopping them..."
    $DOCKER_COMPOSE stop
    docker rm -f bbc_laravel_db 2>/dev/null || true
    echo "Old containers stopped / name conflicts resolved"
else
    echo "No old containers found"
fi

# ------------------------------
# Create Laravel project if missing
# ------------------------------
if [ ! -f "composer.json" ]; then
    echo "Creating new Laravel project..."
    TEMP_DIR="laravel-temp-$(date +%s)"
    echo "Downloading Laravel (inside Docker container)..."
    $DOCKER_COMPOSE run --rm app composer create-project laravel/laravel:^12.0 $TEMP_DIR --prefer-dist --no-interaction
    echo "Moving Laravel files..."
    mv $TEMP_DIR/* . 2>/dev/null || true
    mv $TEMP_DIR/.[^.]* . 2>/dev/null || true
    [[ -d "$TEMP_DIR" ]] && rmdir "$TEMP_DIR" 2>/dev/null || true
    if [ ! -f "composer.json" ] || [ ! -f "artisan" ]; then
        echo "❌ Laravel installation failed!"
        exit 1
    fi
    echo "Laravel project created"
else
    echo "Laravel project already exists"
fi

# ------------------------------
# Copy .env
# ------------------------------
echo "Copying Docker files and .env..."
[ -f "src/.env" ] && cp src/.env .env && echo "  ✅ .env file copied"

# ------------------------------
# Cleanup old containers
# ------------------------------
$DOCKER_COMPOSE down 2>/dev/null || true
docker rm -f bbc_laravel_app bbc_laravel_webserver bbc_laravel_db bbc_laravel_phpmyadmin 2>/dev/null || true
docker network rm moviesearch_laravel 2>/dev/null || true

# ------------------------------
# Check ports
# ------------------------------
echo "Checking available ports..."
if lsof -Pi :3306 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Port 3306 in use – switching to port 3307 for DB"
fi

# ------------------------------
# Start Docker containers
# ------------------------------
echo "Starting Docker containers..."
$DOCKER_COMPOSE up -d --build
sleep 15
RUNNING_CONTAINERS=$($DOCKER_COMPOSE ps --services --filter "status=running")
if [ -z "$RUNNING_CONTAINERS" ]; then
    echo "❌ Containers not running. Showing logs:"
    $DOCKER_COMPOSE logs
    exit 1
fi
echo "✅ Containers successfully started ..."
sleep 25

# ------------------------------
# Install Composer & NPM
# ------------------------------
echo "Installing PHP dependencies..."
$DOCKER_COMPOSE exec -T app composer install --no-interaction

echo "Installing frontend dependencies..."
$DOCKER_COMPOSE exec -T app npm install

# ------------------------------
# Livewire, Breeze & TALL Stack
# ------------------------------
echo "Installing Livewire..."
$DOCKER_COMPOSE exec -T app composer require livewire/livewire --no-interaction

echo "Installing Laravel Breeze..."
$DOCKER_COMPOSE exec -T app composer require laravel/breeze --dev --no-interaction
$DOCKER_COMPOSE exec -T app php artisan breeze:install blade --no-interaction

echo "Installing TALL Stack frontend..."
$DOCKER_COMPOSE exec -T app npm install -D tailwindcss postcss autoprefixer alpinejs
$DOCKER_COMPOSE exec -T app npx tailwindcss init -p

# ------------------------------
# Copy custom development files from src
# ------------------------------
if [ -d "src" ]; then
    [ -d "src/Views" ] && cp -Rf src/Views/. resources/views/ 2>/dev/null && echo "  ✅ Views copied"
    [ -d "src/Factories" ] && cp -r src/Factories/* database/factories/ 2>/dev/null && echo "  ✅ Factories copied"
    [ -d "src/Models" ] && cp -r src/Models/* app/Models/ 2>/dev/null && echo "  ✅ Models copied"
    [ -d "src/Repositories" ] && mkdir -p app/Repositories && cp -r src/Repositories/* app/Repositories/ 2>/dev/null && echo "  ✅ Repositories copied"
    [ -d "src/Providers" ] && mkdir -p app/Providers && cp -r src/Providers/* app/Providers/ 2>/dev/null && echo "  ✅ Providers copied"
    [ -d "src/Livewire" ] && mkdir -p app/Livewire && cp -r src/Livewire/* app/Livewire/ 2>/dev/null && echo "  ✅ Livewire copied"
    [ -d "src/Tests" ] && mkdir -p tests && cp -r src/Tests/* tests/ 2>/dev/null && echo "  ✅ Tests copied"
    [ -d "src/Migrations" ] && cp -r src/Migrations/* database/migrations/ 2>/dev/null && echo "  ✅ Migrations copied"
    [ -d "src/Config" ] && cp -r src/Config/* config/ 2>/dev/null && echo "  ✅ Config copied"
fi

# ------------------------------
# Generate APP_KEY
# ------------------------------
$DOCKER_COMPOSE exec -T app php artisan key:generate --no-interaction

# ------------------------------
# Migration & Assets
# ------------------------------
echo "Running database migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --no-interaction

echo "Building assets..."
$DOCKER_COMPOSE exec -T app npm run build

# ------------------------------
# Finished
# ------------------------------
echo "✅ Setup completed!"
echo "Application available at: http://localhost:8000"
echo "Database: localhost:3306 (User: laravel, Password: laravel)"
echo "phpMyAdmin: http://localhost:8080"
echo "⚠️ Reminder: Please add your TMDB_API_KEY to the .env file"
