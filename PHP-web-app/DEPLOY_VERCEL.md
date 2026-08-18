# Deploy independente na Vercel — PHP-web-app

Esta pasta foi preparada para funcionar sem depender de arquivos das pastas irmãs ou da raiz do HubEstudantil.

## Variáveis obrigatórias na Vercel
Configure em Project > Settings > Environment Variables:
- DATABASE_URL
- JWT_SECRET (mínimo 32 caracteres)

Se desejar autenticação compartilhada entre domínios próprios, configure também COOKIE_DOMAIN e COOKIE_SECURE conforme o domínio utilizado.

## Deploy
Defina esta pasta (PHP-web-app) como a raiz do projeto na Vercel. O vercel.json usa uma única função PHP em api/index.php (runtime vercel-php@0.9.0) para evitar o limite de múltiplas Serverless Functions do plano Hobby.
