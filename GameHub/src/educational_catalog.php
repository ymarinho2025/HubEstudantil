<?php
function gamehub_educational_catalog(): array
{
    return [
        'joao-bird' => [
            'title' => 'João Bird',
            'route' => '/games/JoaoBird/',
            'icon' => '🐦',
            'area' => 'Matemática',
            'skill' => 'Cálculo mental, atenção e tomada de decisão',
            'mission' => 'Resolva um desafio rápido de matemática para liberar o voo. Depois, mantenha a atenção para superar os obstáculos e bater seu recorde.',
            'instructions' => 'Clique ou pressione Espaço para voar. Antes de cada rodada, responda ao desafio pedagógico.',
            'questions' => [
                ['q'=>'Quanto é 7 × 8?', 'a'=>['48','54','56','64'], 'correct'=>2, 'hint'=>'Pense em 7 grupos de 8.'],
                ['q'=>'Qual é a metade de 96?', 'a'=>['42','46','48','52'], 'correct'=>2, 'hint'=>'Divida 96 por 2.'],
                ['q'=>'Se você tem 35 pontos e ganha mais 17, quantos pontos terá?', 'a'=>['42','50','52','54'], 'correct'=>2, 'hint'=>'Some 35 + 17.'],
            ],
        ],
        'datilografia' => [
            'title' => 'Corrida de Datilografia',
            'route' => '/games/DatilografiaGame/',
            'icon' => '⌨️',
            'area' => 'Língua Portuguesa',
            'skill' => 'Ortografia, fluência leitora e precisão na escrita',
            'mission' => 'Treine digitação com atenção à grafia correta das palavras e ao ritmo de leitura.',
            'instructions' => 'Digite as frases exatamente como aparecem. Priorize a precisão antes da velocidade.',
            'questions' => [
                ['q'=>'Qual palavra está escrita corretamente?', 'a'=>['Excessão','Exceção','Eceção','Excesssão'], 'correct'=>1, 'hint'=>'A palavra deriva de “exceto”.'],
                ['q'=>'Qual opção tem acentuação correta?', 'a'=>['lapis','lápis','lapís','lápís'], 'correct'=>1, 'hint'=>'É uma palavra paroxítona terminada em “is”.'],
                ['q'=>'Complete: “Nós ____ muito ontem.”', 'a'=>['estudamos','estudámos','estudemos','estudavamos'], 'correct'=>0, 'hint'=>'Use o pretérito perfeito do verbo estudar.'],
            ],
        ],
        'paint' => [
            'title' => 'Paint Criativo',
            'route' => '/games/paint/',
            'icon' => '🎨',
            'area' => 'Arte',
            'skill' => 'Criatividade, composição e teoria das cores',
            'mission' => 'Use formas e cores para criar uma composição visual seguindo uma proposta artística.',
            'instructions' => 'Depois do desafio, crie uma imagem usando pelo menos três cores e duas formas diferentes.',
            'questions' => [
                ['q'=>'Quais são cores primárias na pintura tradicional?', 'a'=>['Azul, vermelho e amarelo','Verde, laranja e roxo','Preto, branco e cinza','Rosa, azul e verde'], 'correct'=>0, 'hint'=>'São cores que servem de base para formar outras.'],
                ['q'=>'Misturando azul e amarelo, normalmente obtemos:', 'a'=>['Roxo','Verde','Laranja','Marrom'], 'correct'=>1, 'hint'=>'Pense na cor das folhas.'],
                ['q'=>'Uma composição simétrica apresenta:', 'a'=>['Apenas cores frias','Equilíbrio entre lados','Somente linhas retas','Ausência de formas'], 'correct'=>1, 'hint'=>'Imagine um desenho refletido no espelho.'],
            ],
        ],
        'piano' => [
            'title' => 'Piano Musical',
            'route' => '/games/piano/',
            'icon' => '🎹',
            'area' => 'Música',
            'skill' => 'Percepção sonora, ritmo e sequência musical',
            'mission' => 'Explore notas e ritmos, identificando relações entre sons graves e agudos.',
            'instructions' => 'Toque as teclas e experimente sequências. Tente repetir um padrão rítmico de quatro notas.',
            'questions' => [
                ['q'=>'Qual nota vem depois de Dó na sequência musical?', 'a'=>['Mi','Ré','Fá','Si'], 'correct'=>1, 'hint'=>'A sequência começa Dó, Ré, Mi...'],
                ['q'=>'Um som mais agudo possui, em geral:', 'a'=>['Frequência maior','Frequência menor','Nenhuma vibração','Volume sempre menor'], 'correct'=>0, 'hint'=>'Agudo está relacionado a vibrações mais rápidas.'],
                ['q'=>'Quantas notas naturais existem na sequência Dó–Ré–Mi–Fá–Sol–Lá–Si?', 'a'=>['5','6','7','8'], 'correct'=>2, 'hint'=>'Conte os nomes apresentados.'],
            ],
        ],
        'pixel-art' => [
            'title' => 'Pixel Art',
            'route' => '/games/pixels-art/',
            'icon' => '👾',
            'area' => 'Arte + Matemática',
            'skill' => 'Geometria, padrões, coordenadas e criatividade',
            'mission' => 'Crie desenhos em grade explorando simetria, repetição e organização espacial.',
            'instructions' => 'Monte uma figura usando a grade. Experimente criar um desenho simétrico.',
            'questions' => [
                ['q'=>'Uma grade com 8 linhas e 8 colunas possui quantos quadrados?', 'a'=>['16','32','64','80'], 'correct'=>2, 'hint'=>'Multiplique linhas por colunas.'],
                ['q'=>'Se uma figura possui dois lados espelhados, ela apresenta:', 'a'=>['Rotação','Simetria','Perspectiva','Escala'], 'correct'=>1, 'hint'=>'Pense nas asas de uma borboleta.'],
                ['q'=>'Qual forma possui quatro lados iguais e quatro ângulos retos?', 'a'=>['Triângulo','Retângulo qualquer','Quadrado','Círculo'], 'correct'=>2, 'hint'=>'É uma forma muito comum em pixel art.'],
            ],
        ],
        'snake' => [
            'title' => 'Snake Lógico',
            'route' => '/games/snake-game/',
            'icon' => '🐍',
            'area' => 'Raciocínio Lógico',
            'skill' => 'Planejamento espacial, antecipação e estratégia',
            'mission' => 'Planeje rotas, antecipe movimentos e evite colisões enquanto aumenta sua pontuação.',
            'instructions' => 'Use as setas ou clique/toque na direção desejada. A cobra começa com 8 segmentos, anda continuamente e exige planejamento.',
            'questions' => [
                ['q'=>'Na sequência 2, 4, 8, 16, qual é o próximo número?', 'a'=>['18','24','30','32'], 'correct'=>3, 'hint'=>'Cada termo é o dobro do anterior.'],
                ['q'=>'Se você está voltado para o norte e gira 90° à direita, ficará voltado para:', 'a'=>['Sul','Leste','Oeste','Norte'], 'correct'=>1, 'hint'=>'Visualize uma bússola.'],
                ['q'=>'Qual sequência está em ordem crescente?', 'a'=>['9,7,5,3','2,4,6,8','8,6,7,5','3,2,4,1'], 'correct'=>1, 'hint'=>'Crescente significa do menor para o maior.'],
            ],
        ],
        'space-invaders' => [
            'title' => 'Defesa Espacial — Ciências',
            'route' => '/games/space-invaders/',
            'icon' => '🚀',
            'area' => 'Ciências',
            'skill' => 'Astronomia básica, atenção e coordenação',
            'mission' => 'Defenda sua base enquanto revisa conceitos fundamentais sobre o Sistema Solar e o espaço.',
            'instructions' => 'Use ← → ou A/D para mover. Atire com Espaço ou clique/toque. Destrua a formação antes que ela alcance sua nave.',
            'questions' => [
                ['q'=>'Qual planeta é conhecido como Planeta Vermelho?', 'a'=>['Vênus','Marte','Júpiter','Mercúrio'], 'correct'=>1, 'hint'=>'Seu solo possui muito óxido de ferro.'],
                ['q'=>'A Terra realiza uma volta completa ao redor do Sol em aproximadamente:', 'a'=>['24 horas','7 dias','30 dias','365 dias'], 'correct'=>3, 'hint'=>'Esse movimento define o ano.'],
                ['q'=>'Qual é a estrela do nosso Sistema Solar?', 'a'=>['Lua','Sol','Saturno','Sirius'], 'correct'=>1, 'hint'=>'É a fonte de luz e calor mais próxima da Terra.'],
            ],
        ],
    ];
}
