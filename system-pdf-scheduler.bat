@echo off
setlocal

title Minisystems - Programador

cd /d "%~dp0.."

if not exist "artisan" (
    echo ERROR: No se encontro artisan en:
    echo %CD%
    pause
    exit /b 1
)

echo Iniciando el programador de Minisystems...
echo Mantenga esta ventana abierta.
echo.

php artisan schedule:work

pause