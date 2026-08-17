#!/usr/bin/env bash
set -e
command -v php >/dev/null || { echo "PHP 8.1+ não encontrado."; exit 1; }
ROOT="$(cd "$(dirname "$0")" && pwd)"
php -S 127.0.0.1:8000 -t "$ROOT/portal" >/tmp/hub_portal.log 2>&1 &
php -S 127.0.0.1:8001 -t "$ROOT/SGC" >/tmp/hub_sgc.log 2>&1 &
(cd "$ROOT/PHP-web-app" && php -S 127.0.0.1:8002 router.php) >/tmp/hub_atividades.log 2>&1 &
php -S 127.0.0.1:8003 -t "$ROOT/GameHub/public" >/tmp/hub_gamehub.log 2>&1 &
echo "HubEstudantil iniciado: http://127.0.0.1:8000"
echo "SGC: http://127.0.0.1:8001"
echo "Atividades: http://127.0.0.1:8002"
echo "GameHub: http://127.0.0.1:8003"
wait
