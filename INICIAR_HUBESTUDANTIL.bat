@echo off
title HubEstudantil
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
  echo ERRO: PHP nao encontrado no PATH.
  pause
  exit /b 1
)

php -m | findstr /I "pdo_pgsql" >nul
if errorlevel 1 (
  echo ERRO: extensao pdo_pgsql nao habilitada.
  pause
  exit /b 1
)

if not exist ".env" (
  echo ERRO: .env nao encontrado na raiz.
  pause
  exit /b 1
)

echo Iniciando...
start "Hub Portal" cmd /k "cd /d %~dp0portal && php -S 127.0.0.1:8000"
start "SGC" cmd /k "cd /d %~dp0SGC && php -S 127.0.0.1:8001"
start "Atividades" cmd /k "cd /d %~dp0PHP-web-app && php -S 127.0.0.1:8002 -t public"
start "GameHub" cmd /k "cd /d %~dp0GameHub && php -S 127.0.0.1:8003 -t public"
start "Teste DB" cmd /k "cd /d %~dp0 && php -S 127.0.0.1:8090"

timeout /t 3 >nul
start http://127.0.0.1:8000
