@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title MiniSystems - Worker de imagenes

set "PHP_BIN=php"
where php >nul 2>&1
if errorlevel 1 (
    set "PHP_BIN="
    for /f "delims=" %%F in ('dir /b /s "C:\laragon\bin\php\php.exe" 2^>nul') do set "PHP_BIN=%%F"
)

if not defined PHP_BIN (
    echo ================================================================
    echo  NO SE ENCONTRO PHP
    echo ================================================================
    echo  Abre una Terminal de Laragon y ejecuta este archivo desde ahi,
    echo  o agrega la carpeta de PHP de Laragon a la variable PATH.
    echo.
    pause
    exit /b 1
)

 echo ================================================================
 echo  MINISYSTEMS - COLA DE SYSTEM IMAGES Y OPTIMIZADOR
 echo ================================================================
 echo  PHP: %PHP_BIN%
 echo  Mantenga esta ventana abierta mientras use lotes grandes de System Images u Optimizador.
 echo  Para detener el worker presione Ctrl+C.
 echo.

:worker
"%PHP_BIN%" artisan queue:work database --queue=system-images,image-optimizer,default --sleep=1 --tries=1 --timeout=900 --memory=1024 --max-time=3600

 echo.
 echo El worker se detuvo. Se reiniciara en 3 segundos...
timeout /t 3 /nobreak >nul
goto worker
