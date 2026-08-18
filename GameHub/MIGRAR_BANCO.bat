@echo off
cd /d "%~dp0"
php migrate.php
echo.
echo Se apareceu "Hub de jogos migrado com sucesso", o catalogo esta pronto.
pause
