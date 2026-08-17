@echo off
title HubEstudantil - Login Unico
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
  echo ERRO: PHP nao encontrado no PATH.
  pause
  exit /b 1
)

php -m | findstr /I "pdo_pgsql" >nul
if errorlevel 1 (
  echo ERRO: pdo_pgsql nao habilitado.
  pause
  exit /b 1
)

if not exist ".env" (
  echo ERRO: .env nao encontrado na raiz.
  pause
  exit /b 1
)

echo.
echo HubEstudantil com Login Unico
echo SGC/Login    http://127.0.0.1:8000/index.php
echo Atividades   http://127.0.0.1:8002
echo GameHub      http://127.0.0.1:8003
echo Teste DB     http://127.0.0.1:8090/teste-db.php
echo.

start "Hub - SGC" cmd /k "cd /d %~dp0SGC && php -S 127.0.0.1:8000"
start "Hub - Atividades" cmd /k "cd /d %~dp0PHP-web-app && php -S 127.0.0.1:8002 -t public"
start "Hub - GameHub" cmd /k "cd /d %~dp0GameHub && php -S 127.0.0.1:8003 -t public"
start "Hub - DB Test" cmd /k "cd /d %~dp0 && php -S 127.0.0.1:8090"

timeout /t 3 >nul
start http://127.0.0.1:8000/index.php
