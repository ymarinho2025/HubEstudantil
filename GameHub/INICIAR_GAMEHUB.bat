@echo off
title GameHub
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
  echo ERRO: PHP nao encontrado no PATH.
  pause
  exit /b 1
)
php -m | findstr /I "pdo_pgsql" >nul
if errorlevel 1 (
  echo ERRO: pdo_pgsql nao habilitado no PHP.
  pause
  exit /b 1
)
if not exist ".env" (
  echo ERRO: .env nao encontrado.
  pause
  exit /b 1
)
echo Iniciando GameHub em http://127.0.0.1:8003
start "GameHub" cmd /k "cd /d %~dp0 && php -S 127.0.0.1:8003 -t public"
timeout /t 2 >nul
start http://127.0.0.1:8003
