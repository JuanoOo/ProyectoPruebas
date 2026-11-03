@echo off
chcp 65001 >nul
echo.
echo ========================================================
echo       Laravel + Oracle XE — Sprint 1 Setup (Windows)
echo ========================================================
echo.

echo [1/4] Levantando contenedores Docker y compilando imagen...
docker compose up -d --build
if %errorlevel% neq 0 (
    echo [ERROR] Falló docker compose up. Asegúrate de tener Docker Desktop abierto.
    pause
    exit /b %errorlevel%
)

echo.
echo [2/4] Esperando a que Oracle Database XE esté listo...
echo       (Esto puede tardar 2-3 minutos en la primera ejecución mientras inicializa la PDB)

:wait_oracle
for /f "tokens=*" %%i in ('docker inspect --format="{{.State.Health.Status}}" oracle_xe 2^>nul') do set HEALTH=%%i
if "%HEALTH%"=="healthy" goto oracle_ready
echo       ... inicializando Oracle XE (estado actual: %HEALTH%)
timeout /t 10 /nobreak >nul
goto wait_oracle

:oracle_ready
echo.
echo [OK] Oracle Database XE esta saludable y listo.
echo.

echo [3/4] Ejecutando migraciones de Laravel en Oracle Database...
docker compose exec app php artisan migrate --force
if %errorlevel% neq 0 (
    echo [ERROR] Fallaron las migraciones.
    pause
    exit /b %errorlevel%
)

echo.
echo [4/4] Ejecutando Seeder (Usuario de prueba)...
docker compose exec app php artisan db:seed --force

echo.
echo Limpiando caches...
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

echo.
echo ========================================================
echo                 ¡ENTORNO LISTO!
echo ========================================================
echo   URL Web:         http://localhost:8000
echo   Login:           http://localhost:8000/login
echo   Registro:        http://localhost:8000/register
echo --------------------------------------------------------
echo   Usuario Demo:    ana@test.local
echo   Password:        Abcd1234
echo --------------------------------------------------------
echo   Oracle XE:       localhost:1521
echo   Service Name:    XEPDB1
echo   Usuario DB:      laravel / laravel_pass
echo ========================================================
echo.
echo Para ver correos de recuperacion de contrasena:
echo   docker compose exec app tail -f storage/logs/laravel.log
echo.
echo Para detener el proyecto:
echo   docker compose down
echo.
pause
