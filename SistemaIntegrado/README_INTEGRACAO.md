# HubEstudantil — pacote integrado

Este pacote foi criado a partir do ZIP original fornecido pelo autor.

## Garantia de preservação

- `ORIGINAL_INTACTO/` é uma cópia dos arquivos recebidos, sem edição.
- `SISTEMA_INTEGRADO/` contém uma cópia para execução conjunta.
- O repositório GitHub não é acessado nem modificado por este pacote.

## Como iniciar no Windows

1. Instale PHP 8.1 ou superior e deixe `php` disponível no PATH.
2. Entre em `SISTEMA_INTEGRADO`.
3. Execute `INICIAR_WINDOWS.bat`.
4. O navegador abrirá `http://127.0.0.1:8000`.

## Como iniciar no Linux/macOS

Execute:

    cd SISTEMA_INTEGRADO
    ./iniciar_linux_mac.sh

## Serviços

- Portal: http://127.0.0.1:8000
- SGC: http://127.0.0.1:8001
- PHP-web-app: http://127.0.0.1:8002
- GameHub: http://127.0.0.1:8003

## Banco de dados

PHP-web-app e GameHub usam `DATABASE_URL` para PostgreSQL/NeonDB. Para funcionalidades que consultam o banco, a variável deve existir no ambiente de execução.

## Por que portas separadas?

Os projetos existentes usam caminhos absolutos como `/css/...`, `/login.php` e `/home.php`. Servi-los em portas separadas preserva esses caminhos e evita reescrever o código original.

## Dependências

O PHP-web-app não trazia a pasta `vendor` no ZIP recebido. Como o GameHub já continha `vendor` e ambos declaram `firebase/php-jwt ^7.0`, a cópia integrada reutiliza essa dependência para permitir o carregamento do JWT sem exigir conexão ao GitHub.
