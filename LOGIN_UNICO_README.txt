HUBESTUDANTIL — LOGIN ÚNICO

Fluxo:
1. O SGC é a entrada: http://127.0.0.1:8000/index.php
2. O login consulta a mesma tabela users do Neon.
3. "Request An Account" abre /register.php e cria a conta na mesma tabela.
4. Após login/cadastro, é criado o cookie HTTP-only "auth_token".
5. Em localhost, cookies não são separados por porta. Portanto 8000, 8002 e 8003 recebem o mesmo auth_token.
6. PHP-web-app e GameHub validam o mesmo JWT_SECRET e reconhecem o usuário.
7. Se o usuário abrir /home.php dos módulos sem cookie válido, é enviado para o login daquele módulo.
8. "To access CMS" autentica e abre /home/, que é o antigo Portal integrado.

Compatibilidade:
- Novas senhas: password_hash/password_verify.
- Contas antigas SHA-256 continuam funcionando e são migradas automaticamente após login.

Produção/Vercel:
- Em projetos Vercel separados (*.vercel.app), um cookie não pode ser compartilhado entre projetos independentes.
- Para SSO por cookie em produção, use um domínio próprio comum, por exemplo:
    sgc.seudominio.com
    atividades.seudominio.com
    game.seudominio.com
  e configure COOKIE_DOMAIN=.seudominio.com e COOKIE_SECURE=true.
- Alternativamente, hospede os três módulos sob a mesma origem/domínio.
