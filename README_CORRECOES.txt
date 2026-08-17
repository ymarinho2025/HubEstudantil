HUBESTUDANTIL - PACOTE CORRIGIDO

CORREÇÕES PRINCIPAIS

1. PHP-web-app:
   - api/ e public/ foram unificados.
   - Os arquivos PHP antes em api/ agora estão em PHP-web-app/public/.
   - O servidor local deve usar PHP-web-app/public como DocumentRoot.
   - vendor/ foi incluído no PHP-web-app quando ausente.
   - db.php agora RETORNA o objeto PDO, conforme esperado pelos controllers.
   - responsive.css foi criado a partir do arquivo original resposive.css para corrigir 404.
   - O logo referenciado pelo index foi preenchido com um asset Adventista já existente no projeto.

2. Neon:
   - DATABASE_URL centralizada.
   - Compatibilidade com libpq antigo sem SNI.
   - sslmode=require mantido.

3. Inicialização:
   Execute INICIAR_HUBESTUDANTIL.bat.

ENDEREÇOS
Portal:     http://127.0.0.1:8000
SGC:        http://127.0.0.1:8001
Atividades: http://127.0.0.1:8002
GameHub:    http://127.0.0.1:8003
Teste DB:   http://127.0.0.1:8090/teste-db.php

IMPORTANTE:
A porta 8090 é SOMENTE para o teste do banco.
Não use 8090/PHP-web-app/... para acessar o módulo de atividades.
