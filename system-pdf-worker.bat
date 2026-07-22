@echo off
setlocal

title Minisystems - Cola System PDF

cd /d "%~dp0.."

echo ============================================
echo       MINISYSTEMS - COLA SYSTEM PDF
echo ============================================
echo.
echo Proyecto: %CD%
echo.

if not exist "artisan" (
    echo ERROR: No se encontro el archivo artisan.
    echo.
    echo La cola debe ejecutarse desde:
    echo C:\laragon\www\minisystems
    echo.
    pause
    exit /b 1
)

where php >nul 2>&1

if errorlevel 1 (
    echo ERROR: PHP no esta disponible en el PATH.
    echo Inicia el archivo desde la Terminal de Laragon
    echo o agrega PHP a las variables de entorno.
    echo.
    pause
    exit /b 1
)

echo Iniciando la cola System PDF...
echo Mantenga esta ventana abierta mientras procesa documentos.
echo.

php artisan queue:work database ^
    --queue=system-pdf,default ^
    --tries=3 ^
    --timeout=900 ^
    --sleep=2

echo.
echo La cola termino con un error.
echo Revisa storage\logs\laravel.log
echo.
pause