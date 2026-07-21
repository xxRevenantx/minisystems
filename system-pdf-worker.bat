@echo off
setlocal
cd /d "%~dp0.."
title Minisystems - Cola System PDF
echo Iniciando la cola System PDF...
echo Mantenga esta ventana abierta mientras procesa documentos.
echo.
php artisan queue:work database --queue=system-pdf --timeout=3600 --tries=1
if errorlevel 1 (
    echo.
    echo La cola termino con un error. Revisa PHP, .env y la conexion a la base de datos.
)
pause
