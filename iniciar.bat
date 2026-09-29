@echo off
title SIS-APAFA V11 - Servidor Local
cd /d "%~dp0"

echo ===================================================
echo             INICIANDO SIS-APAFA V11
echo ===================================================
echo.
echo 1. Abriendo la aplicacion en el navegador...
start http://127.0.0.1:8000
echo.
echo 2. Iniciando servidor PHP Artisan en el puerto 8000...
echo    (Para detener el servidor presiona Ctrl + C)
echo.
php artisan serve --host=127.0.0.1 --port=8000
pause
