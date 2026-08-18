# GameHub Educacional — Interface Única

## O que foi alterado

- Super Mario removido do catálogo e da pasta `public/games`.
- Todos os jogos agora são abertos por `public/play.php` dentro de uma **mesma interface visual**.
- O jogo aparece em um `iframe` centralizado e sobreposto à moldura do GameHub.
- Cada jogo possui:
  - área do conhecimento;
  - habilidade trabalhada;
  - objetivo pedagógico;
  - instruções;
  - banco de desafios educacionais.
- Antes de o jogo ser liberado, o aluno precisa responder corretamente a um desafio.
- É possível solicitar um novo desafio sem sair da interface.

## Jogos e áreas

1. João Bird — Matemática
2. Corrida de Datilografia — Língua Portuguesa
3. Paint Criativo — Arte
4. Piano Musical — Música
5. Pixel Art — Arte + Matemática
6. Snake Lógico — Raciocínio Lógico
7. Space Invaders Ciência — Ciências

## Arquitetura

Os jogos originais continuam em suas próprias pastas. A padronização é feita pelo shell `play.php`, evitando reescrever e quebrar a lógica interna de cada jogo.
