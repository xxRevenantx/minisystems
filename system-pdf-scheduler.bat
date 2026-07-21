@echo off
setlocal
cd /d "%~dp0.."
title Minisystems - Programador de tareas
echo Iniciando el programador de Laravel...
echo Esta ventana ejecuta la limpieza automatica de System PDF.
echo.
php artisan schedule:work
if errorlevel 1 (
    echo.
    echo El programador termino con un error. Revisa PHP y la configuracion del proyecto.
)
pause
