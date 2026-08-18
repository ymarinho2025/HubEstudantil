# GameHub corrigido

## Correções aplicadas
- GameHub agora é autocontido: `config/load_env.php`, `config/database.php` e `config/auth.php` ficam dentro dele.
- Compatibilidade com Neon e libpq antigo/SNI.
- Login/cadastro usam a mesma tabela `users` e o cookie `auth_token`.
- Todos os jogos foram cadastrados em `database/schema.sql`.
- João Bird substitui a URL quebrada `/games/flappy/` e salva pontuação no banco.
- Datilografia recebeu versão estática funcional, pois o conteúdo original era um projeto Next.js sem build/node_modules.
- Super Mario teve caminhos absolutos corrigidos para funcionar dentro da pasta do GameHub.
- Todos os jogos possuem retorno para a interface do GameHub.
- Home lista todos os jogos e mantém ranking do João Bird.

## Jogos
1. João Bird
2. Corrida de Datilografia
3. Paint
4. Piano
5. Pixel Art
6. Snake
7. Space Invaders
8. Super Mario

## Preparação do banco
Execute uma vez:
`php migrate.php`

## Execução local
Na pasta GameHub:
`php -S 127.0.0.1:8003 -t public`

Abra:
`http://127.0.0.1:8003`

## Variáveis
O arquivo `.env` deve conter `DATABASE_URL` e `JWT_SECRET`.
Para compartilhar login com os outros módulos locais, use o mesmo `JWT_SECRET` e o mesmo nome de cookie (`auth_token`).
