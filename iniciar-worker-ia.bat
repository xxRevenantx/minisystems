@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title MiniSystems - Worker Groq IA

if not exist "artisan" (
    echo [ERROR] Coloca este archivo en la raiz del proyecto, junto a artisan.
    pause
    exit /b 1
)

set "PHP_BIN=php"
where php >nul 2>&1
if errorlevel 1 (
    set "PHP_BIN="
    for /f "delims=" %%F in ('dir /b /s "C:\laragon\bin\php\php.exe" 2^>nul') do set "PHP_BIN=%%F"
)

if not defined PHP_BIN (
    echo [ERROR] No se encontro PHP. Inicia Laragon e intenta nuevamente.
    pause
    exit /b 1
)

echo ================================================================
echo  MINISYSTEMS - COLA DE REDACCION GROQ IA
echo ================================================================
echo  Mantenga esta ventana abierta mientras genere publicaciones.
echo  Para detener el worker presione Ctrl+C.
echo.

:worker
"%PHP_BIN%" artisan queue:work database --queue=ai-social,default --sleep=2 --tries=4 --timeout=300 --memory=512 --max-time=3600

echo.
echo El worker se detuvo. Se reiniciara en 3 segundos...
timeout /t 3 /nobreak >nul
goto worker
