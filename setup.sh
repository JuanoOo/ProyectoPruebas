#!/bin/bash
# ============================================================
# setup.sh — Sprint 1 Authentication Module
# Inicializa el entorno Docker con Laravel + Oracle XE
# ============================================================

set -e

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║       Laravel 11 + Oracle XE — Sprint 1 Setup           ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Go to project root
cd "$(dirname "$0")"

echo "▶ Paso 1: Levantando contenedores..."
docker compose up -d --build

echo ""
echo "▶ Paso 2: Esperando a que Oracle XE esté listo (puede tardar 2-3 minutos)..."
echo "   (El contenedor Oracle hace healthcheck cada 30s, máximo 10 reintentos)"

# Wait for Oracle healthcheck
MAX_WAIT=180
ELAPSED=0
until [ "$(docker inspect --format='{{.State.Health.Status}}' oracle_xe 2>/dev/null)" = "healthy" ]; do
    if [ $ELAPSED -ge $MAX_WAIT ]; then
        echo "   ✗ Oracle XE no respondió en $MAX_WAIT segundos."
        echo "   Revisa los logs con: docker logs oracle_xe"
        exit 1
    fi
    echo "   ... esperando Oracle ($ELAPSED s)"
    sleep 15
    ELAPSED=$((ELAPSED + 15))
done

echo "   ✓ Oracle XE está listo"
echo ""

echo "▶ Paso 3: Ejecutando migraciones..."
docker compose exec app php artisan migrate --force

echo ""
echo "▶ Paso 4: Ejecutando seeder (usuario de prueba)..."
docker compose exec app php artisan db:seed --force

echo ""
echo "▶ Paso 5: Limpiando caché..."
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║                   ¡Entorno listo!                        ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║  URL:          http://localhost:8000                     ║"
echo "║  Login:        http://localhost:8000/login               ║"
echo "║  Register:     http://localhost:8000/register            ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║  Usuario prueba:  ana@test.local                         ║"
echo "║  Contraseña:      Abcd1234                               ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║  Oracle XE:    localhost:1521                            ║"
echo "║  DB:           XEPDB1                                    ║"
echo "║  Usuario DB:   laravel / laravel_pass                    ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Logs de emails (recuperación de contraseña):"
echo "  docker compose exec app tail -f storage/logs/laravel.log"
echo ""
echo "  Para detener el entorno:"
echo "  docker compose down"
echo ""
