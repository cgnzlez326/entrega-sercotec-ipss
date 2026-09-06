@echo off
REM Importa la base de datos del sitio CDN Santiago (XAMPP por defecto).
REM Requiere MySQL con root sin contrasena.
set MYSQL=C:\xampp\mysql\bin\mysql.exe
set SQL=%~dp0..\database.sql
set TITULO=Importacion base de datos CDN Santiago

if not exist "%MYSQL%" (
  echo [ERROR] No se encontro MySQL en %MYSQL%
  echo Verifica tu ruta de XAMPP o importa database.sql por phpMyAdmin.
  pause
  exit /b 1
)
if not exist "%SQL%" (
  echo [ERROR] No se encontro %SQL%
  pause
  exit /b 1
)

echo.
echo ============================================================
echo  %TITULO%
echo ============================================================
echo Se importara el archivo:
echo   %SQL%
echo En el servidor MySQL local (root sin contrasena).
echo.
choice /C SN /M "Deseas continuar? [S]i / [N]o"
if errorlevel 2 exit /b 0

"%MYSQL%" -u root --default-character-set=utf8mb4 < "%SQL%"
if errorlevel 1 (
  echo.
  echo [ERROR] Fallo la importacion. Tu root podria tener contrasena:
  echo         importa database.sql por phpMyAdmin (ver LEEME_INSTALACION.txt).
) else (
  echo.
  echo [OK] Base de datos importada correctamente.
  echo Abre http://localhost/sercotec-cdn
)
pause
