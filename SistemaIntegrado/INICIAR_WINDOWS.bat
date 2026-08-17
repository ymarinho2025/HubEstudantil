@echo off
title HubEstudantil
where php >nul 2>nul
if errorlevel 1 (
  echo PHP nao encontrado no PATH.
  echo Instale PHP 8.1+ e execute novamente.
  pause
  exit /b 1
)
start "Hub Portal" cmd /k "cd /d %~dp0portal && php -S 127.0.0.1:8000"
start "SGC" cmd /k "cd /d %~dp0SGC && php -S 127.0.0.1:8001"
start "Atividades" cmd /k "cd /d %~dp0PHP-web-app && php -S 127.0.0.1:8002 router.php"
start "GameHub" cmd /k "cd /d %~dp0GameHub\public && php -S 127.0.0.1:8003"
timeout /t 2 >nul
start http://127.0.0.1:8000
