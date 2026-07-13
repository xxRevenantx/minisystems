@echo off
setlocal
cd /d "%~dp0"

echo =====================================================
echo   REPARACION DE MINISYSTEMS - ERROR TARGET CLASS VIEW

echo =====================================================
echo.

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP no esta disponible en la terminal.
    echo Abre la Terminal de Laragon y ejecuta nuevamente este archivo.
    pause
    exit /b 1
)

where composer >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer no esta disponible en la terminal.
    echo Abre la Terminal de Laragon y ejecuta nuevamente este archivo.
    pause
    exit /b 1
)

echo [1/7] Verificando version de PHP...
php -r "if (version_compare(PHP_VERSION, '8.2.0', '<')) {fwrite(STDERR, 'PHP 8.2 o superior es obligatorio. Version actual: '.PHP_VERSION.PHP_EOL); exit(1);} echo 'PHP '.PHP_VERSION.' OK'.PHP_EOL;"
if errorlevel 1 goto :error

echo.
echo [2/7] Eliminando caches generadas de Laravel...
if exist "bootstrap\cache\*.php" del /f /q "bootstrap\cache\*.php" >nul 2>&1
if exist "storage\framework\views\*" del /f /s /q "storage\framework\views\*" >nul 2>&1
if exist "storage\framework\cache\data\*" del /f /s /q "storage\framework\cache\data\*" >nul 2>&1

echo.
echo [3/7] Eliminando vendor incompleto o incompatible...
if exist "vendor" rmdir /s /q "vendor"

echo.
echo [4/7] Limpiando cache de Composer...
call composer clear-cache
if errorlevel 1 goto :error

echo.
echo [5/7] Instalando exactamente las dependencias de composer.lock...
call composer install --no-interaction --prefer-dist
if errorlevel 1 goto :error

echo.
echo [6/7] Regenerando autoload y descubrimiento de paquetes...
call composer dump-autoload -o
if errorlevel 1 goto :error
call php artisan package:discover --ansi
if errorlevel 1 goto :error

echo.
echo [7/7] Limpiando caches finales de Laravel...
call php artisan optimize:clear
if errorlevel 1 goto :error

echo.
echo Version instalada:
call php artisan --version

echo.
echo =====================================================
echo   REPARACION COMPLETADA CORRECTAMENTE

echo   Reinicia Laragon y abre: http://minisystems.test

echo =====================================================
pause
exit /b 0

:error
echo.
echo =====================================================
echo   LA REPARACION NO PUDO COMPLETARSE

echo   Revisa el mensaje anterior. No se modifico tu .env

echo   ni tu base de datos.

echo =====================================================
pause
exit /b 1
