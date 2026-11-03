# Módulo de Autenticación — Sprint 1
**Stack Tecnológico:** Laravel + Oracle Database XE 21c (Docker)

---

## 📌 Confirmación Tecnológica

> **¿Sigue siendo Laravel y Oracle?**  
> **Sí, 100% verificado.**  
> - **Backend:** Laravel Framework (PHP 8.4 con extensión nativa `oci8` y conector `yajra/laravel-oci8`).
> - **Base de Datos:** Oracle Database Express Edition 21c (`gvenzl/oracle-xe:21-slim`), conectada mediante la Pluggable Database (PDB) `XEPDB1`.
> - **Entorno:** Contenedores Docker multi-plataforma (Linux / Windows / macOS).

---

## 🎯 Alcance del Sprint 1 (Historias de Usuario)

| Historia de Usuario | Criterios de Aceptación Cumplidos |
|---|---|
| **HU-01: Registro de usuarios** | Formulario con nombre, correo, contraseña y confirmación. Validaciones obligatorias, correo único insensible a mayúsculas/minúsculas (`strtolower`), contraseña con mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número (`Password::min(8)->mixedCase()->numbers()`). Almacenamiento seguro con hashing `bcrypt`. |
| **HU-02: Inicio de sesión** | Formulario con validación de campos obligatorios. Mensaje genérico de credenciales incorrectas (no revela cuál campo falló). Redirección automática a `/dashboard` (área privada). Protección contra acceso no autenticado a URLs privadas (redirección a login). |
| **HU-03: Recuperación de contraseña** | Formulario de solicitud por correo. Mensaje genérico para evitar enumeración de usuarios. Generación de token seguro aleatorio (64 caracteres) cifrado en base de datos. Enlace con expiración de 30 minutos y uso único (se invalida tras el cambio). La contraseña anterior deja de funcionar. El correo se emite en `storage/logs/laravel.log`. |
| **HU-04: Cierre de sesión** | Opción accesible en el dashboard. Invalida la sesión activa en el servidor y regenera el token CSRF. Redirige al login y bloquea navegación hacia atrás a páginas privadas sin volver a autenticarse. |
| **HU-05: Control de intentos fallidos** | Máximo 5 intentos fallidos consecutivos por combinación de correo + dirección IP en una ventana de 15 minutos. Bloqueo temporal por 15 minutos con mensaje seguro. Al iniciar sesión exitosamente, el contador se reinicia. |

---

## 💻 Requisitos Previos

1. **Docker Desktop** (en Windows o macOS) o **Docker Engine + Docker Compose** (en Linux).
2. Asegurarse de que Docker se encuentre abierto y en ejecución.
3. Puertos libres en tu equipo:
   - `8000`: Aplicación web Laravel.
   - `1521`: Oracle Database Listener.

---

## 🚀 Guía de Instalación y Ejecución en LINUX

### Opción 1: Script Automático (Recomendado)

1. Abre una terminal y navega a la carpeta del proyecto descomprimido:
   ```bash
   cd ruta/al/proyecto
   ```
2. Otorga permisos de ejecución al script:
   ```bash
   chmod +x setup.sh
   ```
3. Ejecuta el script:
   ```bash
   ./setup.sh
   ```
   *El script levantará los contenedores, esperará que Oracle XE esté saludable (~2 minutos la primera vez), ejecutará las migraciones en Oracle y sembrará el usuario de prueba.*

### Opción 2: Paso a paso con Docker Compose

1. Levantar los contenedores y construir la imagen:
   ```bash
   docker compose up -d --build
   ```
2. Esperar a que el contenedor `oracle_xe` pase al estado `healthy`:
   ```bash
   docker compose ps
   ```
3. Ejecutar las migraciones en Oracle:
   ```bash
   docker compose exec app php artisan migrate --force
   ```
4. Ejecutar el seeder (usuario demo):
   ```bash
   docker compose exec app php artisan db:seed --force
   ```
5. Limpiar caché de configuración:
   ```bash
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan cache:clear
   ```

---

## 🪟 Guía de Instalación y Ejecución en WINDOWS

### Opción 1: Doble Clic o CMD con `setup.bat` (Recomendado)

1. Abre la carpeta del proyecto descomprimido en el Explorador de Archivos de Windows.
2. Haz doble clic en el archivo **`setup.bat`** (o ejecútalo desde CMD / PowerShell):
   ```cmd
   setup.bat
   ```
3. El script automáticamente:
   - Compila la imagen con soporte Oracle Instant Client y PHP 8.4.
   - Inicia Oracle XE 21c y monitorea su salud.
   - Aplica las migraciones en Oracle Database.
   - Inserta el usuario de pruebas.

### Opción 2: Manual mediante PowerShell o CMD

1. Abre PowerShell o CMD como administrador o usuario normal y navega al proyecto:
   ```powershell
   cd C:\ruta\al\proyecto
   ```
2. Inicia los contenedores:
   ```powershell
   docker compose up -d --build
   ```
3. Espera 2 minutos hasta que Oracle esté listo (puedes verificar con `docker compose ps` que figure `healthy`).
4. Corre las migraciones y datos de prueba:
   ```powershell
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan cache:clear
   ```

---

## 🔑 Credenciales y Accesos

### Aplicación Web
- **URL Principal / Login:** [http://localhost:8000/login](http://localhost:8000/login)
- **Registro de usuarios:** [http://localhost:8000/register](http://localhost:8000/register)
- **Recuperar contraseña:** [http://localhost:8000/forgot-password](http://localhost:8000/forgot-password)
- **Área privada (Dashboard):** [http://localhost:8000/dashboard](http://localhost:8000/dashboard)

### Usuario de Prueba (Pre-cargado)
- **Correo:** `ana@test.local`
- **Contraseña:** `Abcd1234`

### Conexión Directa a Oracle XE (DBeaver / Oracle SQL Developer / VS Code)
- **Host:** `localhost`
- **Puerto:** `1521`
- **Tipo de conexión:** Service Name
- **Service Name:** `XEPDB1`
- **Usuario de la App:** `laravel`
- **Contraseña de la App:** `laravel_pass`
- **Usuario Administrador:** `system` (Contraseña: `oracle`)

---

## 🧪 Pruebas del Sprint 1 (Matriz de Validación)

1. **Prueba de Registro (HU-01):**
   - Ve a `/register`.
   - Ingresa una contraseña menor a 8 caracteres (ej. `Abc1234`) y verifica el rechazo.
   - Ingresa una contraseña sin números (ej. `Abcdefgh`) y verifica el rechazo.
   - Registra un usuario válido (ej. `juan@test.local` con `Clave1234`).
   - Intenta registrar nuevamente con `JUAN@test.local`; se rechazará por duplicado case-insensitive.

2. **Prueba de Login y Autenticación (HU-02):**
   - Ve a `/login` e ingresa `ana@test.local` con contraseña incorrecta. Se mostrará un mensaje genérico sin pistas.
   - Ingresa con `ana@test.local` y `Abcd1234`. Accederás al Dashboard privado.
   - Intenta acceder directamente a `http://localhost:8000/dashboard` desde una ventana de incógnito; serás redirigido a `/login`.

3. **Prueba de Recuperación de Contraseña (HU-03):**
   - Ve a `/forgot-password`.
   - Ingresa `ana@test.local` y pulsa "Enviar enlace".
   - Verás el mensaje genérico de confirmación.
   - Revisa el enlace emitido ejecutando en tu terminal:
     ```bash
     docker compose exec app tail -n 25 storage/logs/laravel.log
     ```
   - Copia la URL del enlace de recuperación en el navegador, establece una nueva contraseña válida y verifica que la contraseña anterior ya no permita el acceso.

4. **Prueba de Cierre de Sesión (HU-04):**
   - Estando en el Dashboard, presiona "Cerrar sesión".
   - Al cerrar sesión serás redirigido a `/login`.
   - Presiona el botón "Atrás" de tu navegador; la sesión estará invalidada en el servidor.

5. **Prueba de Bloqueo por Intentos Fallidos (HU-05):**
   - En `/login`, falla la contraseña 5 veces seguidas para `ana@test.local`.
   - Al sexto intento el sistema bloqueará temporalmente el acceso por 15 minutos para esa cuenta e IP.

---

## 🛑 Detener o Reiniciar el Entorno

Para detener los contenedores:
```bash
docker compose down
```

Para detener eliminando volúmenes (reinicio limpio desde cero):
```bash
docker compose down -v
```
