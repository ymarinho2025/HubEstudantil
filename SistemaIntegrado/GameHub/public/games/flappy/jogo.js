console.log('[Flappy Bird] Jogo corrigido');

let frames = 0;

const som_HIT = new Audio('./hit.wav');
const som_PULO = new Audio('./pulo.wav');
const som_PONTO = new Audio('./ponto.wav');

const sprites = new Image();
sprites.src = './sprites.png';

const canvas = document.querySelector('canvas');
const contexto = canvas.getContext('2d');

function tocaSom(som) {
  som.currentTime = 0;
  som.play().catch(function () {});
}


function salvaMelhorPontuacao(pontuacao) {
  const melhorAtual = Number(localStorage.getItem('flappy_best') || 0);
  const melhorPontuacao = Math.max(melhorAtual, pontuacao);
  localStorage.setItem('flappy_best', String(melhorPontuacao));
  return melhorPontuacao;
}

async function enviarPontuacaoParaBanco(pontuacao) {
  try {
    const resposta = await fetch('/api/flappy/pontuacao.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ score: pontuacao }),
    });

    if (!resposta.ok) {
      throw new Error('Erro HTTP ' + resposta.status);
    }

    const dados = await resposta.json();
    if (globais.resultado) {
      globais.resultado.melhorPontuacao = Number(dados.bestScore || globais.resultado.melhorPontuacao || 0);
    }
  } catch (erro) {
    console.warn('Pontuação não enviada ao banco.', erro);
  }
}

// [Plano de Fundo]
const planoDeFundo = {
  spriteX: 390,
  spriteY: 0,
  largura: 275,
  altura: 204,
  x: 0,
  y: canvas.height - 204,
  desenha() {
    contexto.fillStyle = '#70c5ce';
    contexto.fillRect(0, 0, canvas.width, canvas.height);

    contexto.drawImage(
      sprites,
      planoDeFundo.spriteX, planoDeFundo.spriteY,
      planoDeFundo.largura, planoDeFundo.altura,
      planoDeFundo.x, planoDeFundo.y,
      planoDeFundo.largura, planoDeFundo.altura,
    );

    contexto.drawImage(
      sprites,
      planoDeFundo.spriteX, planoDeFundo.spriteY,
      planoDeFundo.largura, planoDeFundo.altura,
      planoDeFundo.x + planoDeFundo.largura, planoDeFundo.y,
      planoDeFundo.largura, planoDeFundo.altura,
    );
  },
};

// [Chão]
function criaChao() {
  const chao = {
    spriteX: 0,
    spriteY: 610,
    largura: 224,
    altura: 112,
    x: 0,
    y: canvas.height - 112,
    atualiza() {
      const movimentoDoChao = 1;
      const repeteEm = chao.largura / 2;
      const movimentacao = chao.x - movimentoDoChao;
      chao.x = movimentacao % repeteEm;
    },
    desenha() {
      contexto.drawImage(
        sprites,
        chao.spriteX, chao.spriteY,
        chao.largura, chao.altura,
        chao.x, chao.y,
        chao.largura, chao.altura,
      );

      contexto.drawImage(
        sprites,
        chao.spriteX, chao.spriteY,
        chao.largura, chao.altura,
        chao.x + chao.largura, chao.y,
        chao.largura, chao.altura,
      );
    },
  };
  return chao;
}

function fazColisaoComChao(flappyBird, chao) {
  const peDoFlappyBird = flappyBird.y + flappyBird.altura;
  return peDoFlappyBird >= chao.y;
}

function criaFlappyBird() {
  const flappyBird = {
    largura: 33,
    altura: 24,
    x: 10,
    y: 50,
    pulo: 4.6,
    gravidade: 0.25,
    velocidade: 0,
    pula() {
      flappyBird.velocidade = -flappyBird.pulo;
      tocaSom(som_PULO);
    },
    atualiza() {
      // Impede o pássaro de sair por cima da tela.
      if (flappyBird.y < 0) {
        flappyBird.y = 0;
        flappyBird.velocidade = 0;
      }

      if (fazColisaoComChao(flappyBird, globais.chao)) {
        flappyBird.y = globais.chao.y - flappyBird.altura;
        tocaSom(som_HIT);
        finalizaPartida();
        return;
      }

      flappyBird.velocidade = flappyBird.velocidade + flappyBird.gravidade;
      flappyBird.y = flappyBird.y + flappyBird.velocidade;
    },
    movimentos: [
      { spriteX: 0, spriteY: 0 },
      { spriteX: 0, spriteY: 26 },
      { spriteX: 0, spriteY: 52 },
      { spriteX: 0, spriteY: 26 },
    ],
    frameAtual: 0,
    atualizaOFrameAtual() {
      const intervaloDeFrames = 10;
      const passouOIntervalo = frames % intervaloDeFrames === 0;

      if (passouOIntervalo) {
        flappyBird.frameAtual = (flappyBird.frameAtual + 1) % flappyBird.movimentos.length;
      }
    },
    desenha() {
      flappyBird.atualizaOFrameAtual();
      const { spriteX, spriteY } = flappyBird.movimentos[flappyBird.frameAtual];

      contexto.drawImage(
        sprites,
        spriteX, spriteY,
        flappyBird.largura, flappyBird.altura,
        flappyBird.x, flappyBird.y,
        flappyBird.largura, flappyBird.altura,
      );
    },
  };
  return flappyBird;
}

// [Mensagem Get Ready]
const mensagemGetReady = {
  sX: 134,
  sY: 0,
  w: 174,
  h: 152,
  x: canvas.width / 2 - 174 / 2,
  y: 50,
  desenha() {
    contexto.drawImage(
      sprites,
      mensagemGetReady.sX, mensagemGetReady.sY,
      mensagemGetReady.w, mensagemGetReady.h,
      mensagemGetReady.x, mensagemGetReady.y,
      mensagemGetReady.w, mensagemGetReady.h,
    );
  },
};

// [Mensagem Game Over]
const mensagemGameOver = {
  sX: 134,
  sY: 153,
  w: 226,
  h: 200,
  x: canvas.width / 2 - 226 / 2,
  y: 50,
  desenha() {
    contexto.drawImage(
      sprites,
      mensagemGameOver.sX, mensagemGameOver.sY,
      mensagemGameOver.w, mensagemGameOver.h,
      mensagemGameOver.x, mensagemGameOver.y,
      mensagemGameOver.w, mensagemGameOver.h,
    );
  },
};

// [Canos]
function criaCanos() {
  const canos = {
    largura: 52,
    altura: 400,
    chao: {
      spriteX: 0,
      spriteY: 169,
    },
    ceu: {
      spriteX: 52,
      spriteY: 169,
    },
    espaco: 90,
    velocidade: 2,
    desenha() {
      canos.pares.forEach(function (par) {
        const canoCeuX = par.x;
        const canoCeuY = par.y;

        contexto.drawImage(
          sprites,
          canos.ceu.spriteX, canos.ceu.spriteY,
          canos.largura, canos.altura,
          canoCeuX, canoCeuY,
          canos.largura, canos.altura,
        );

        const canoChaoX = par.x;
        const canoChaoY = canos.altura + canos.espaco + par.y;

        contexto.drawImage(
          sprites,
          canos.chao.spriteX, canos.chao.spriteY,
          canos.largura, canos.altura,
          canoChaoX, canoChaoY,
          canos.largura, canos.altura,
        );

        par.canoCeu = {
          x: canoCeuX,
          y: canos.altura + canoCeuY,
        };

        par.canoChao = {
          x: canoChaoX,
          y: canoChaoY,
        };
      });
    },
    temColisaoComOFlappyBird(par) {
      const flappy = globais.flappyBird;
      const flappyDireita = flappy.x + flappy.largura;
      const flappyEsquerda = flappy.x;
      const flappyTopo = flappy.y;
      const flappyBase = flappy.y + flappy.altura;

      const canoDireita = par.x + canos.largura;
      const existeColisaoHorizontal = flappyDireita >= par.x && flappyEsquerda <= canoDireita;

      if (!existeColisaoHorizontal) {
        return false;
      }

      const bateuNoCanoDeCima = flappyTopo <= par.canoCeu.y;
      const bateuNoCanoDeBaixo = flappyBase >= par.canoChao.y;

      return bateuNoCanoDeCima || bateuNoCanoDeBaixo;
    },
    pares: [],
    atualiza() {
      const passou100Frames = frames % 100 === 0;

      if (passou100Frames) {
        canos.pares.push({
          x: canvas.width,
          y: -150 * (Math.random() + 1),
          pontuado: false,
        });
      }

      canos.pares.forEach(function (par) {
        par.x = par.x - canos.velocidade;

        if (canos.temColisaoComOFlappyBird(par)) {
          tocaSom(som_HIT);
          finalizaPartida();
          return;
        }

        // Pontua somente uma vez, quando o cano passou totalmente pelo pássaro.
        if (!par.pontuado && par.x + canos.largura < globais.flappyBird.x) {
          par.pontuado = true;
          globais.placar.pontuacao = globais.placar.pontuacao + 1;
          tocaSom(som_PONTO);
        }
      });

      canos.pares = canos.pares.filter(function (par) {
        return par.x + canos.largura > 0;
      });
    },
  };

  return canos;
}

function criaPlacar() {
  const placar = {
    pontuacao: 0,
    desenha() {
      contexto.font = '35px "VT323"';
      contexto.textAlign = 'right';
      contexto.fillStyle = 'white';
      contexto.fillText(`${placar.pontuacao}`, canvas.width - 10, 35);
    },
  };
  return placar;
}

function finalizaPartida() {
  if (telaAtiva === Telas.GAME_OVER) {
    return;
  }

  const pontuacaoFinal = globais.placar ? globais.placar.pontuacao : 0;
  const melhorPontuacao = salvaMelhorPontuacao(pontuacaoFinal);

  globais.resultado = {
    pontuacaoFinal,
    melhorPontuacao,
  };

  enviarPontuacaoParaBanco(pontuacaoFinal);
  mudaParaTela(Telas.GAME_OVER);
}

function desenhaPontuacaoFinal() {
  const resultado = globais.resultado || { pontuacaoFinal: 0, melhorPontuacao: 0 };

  contexto.font = '24px "VT323"';
  contexto.textAlign = 'right';
  contexto.fillStyle = 'white';

  // Números dentro da placa de Game Over, ao lado dos textos SCORE e BEST.
  contexto.fillText(String(resultado.pontuacaoFinal), mensagemGameOver.x + 205, mensagemGameOver.y + 82);
  contexto.fillText(String(resultado.melhorPontuacao), mensagemGameOver.x + 205, mensagemGameOver.y + 124);
}

// [Telas]
const globais = {};
let telaAtiva = {};

function mudaParaTela(novaTela) {
  telaAtiva = novaTela;

  if (telaAtiva.inicializa) {
    telaAtiva.inicializa();
  }
}

const Telas = {
  INICIO: {
    inicializa() {
      frames = 0;
      globais.flappyBird = criaFlappyBird();
      globais.chao = criaChao();
      globais.canos = criaCanos();
      globais.resultado = null;
    },
    desenha() {
      planoDeFundo.desenha();
      globais.flappyBird.desenha();
      globais.chao.desenha();
      mensagemGetReady.desenha();
    },
    click() {
      mudaParaTela(Telas.JOGO);
    },
    atualiza() {
      globais.chao.atualiza();
    },
  },
};

Telas.JOGO = {
  inicializa() {
    globais.placar = criaPlacar();
  },
  desenha() {
    planoDeFundo.desenha();
    globais.canos.desenha();
    globais.chao.desenha();
    globais.flappyBird.desenha();
    globais.placar.desenha();
  },
  click() {
    globais.flappyBird.pula();
  },
  atualiza() {
    globais.canos.atualiza();
    globais.chao.atualiza();
    globais.flappyBird.atualiza();
  },
};

Telas.GAME_OVER = {
  desenha() {
    planoDeFundo.desenha();
    globais.canos.desenha();
    globais.chao.desenha();
    globais.flappyBird.desenha();
    if (globais.placar) {
      globais.placar.desenha();
    }
    mensagemGameOver.desenha();
    desenhaPontuacaoFinal();
  },
  atualiza() {},
  click() {
    mudaParaTela(Telas.INICIO);
  },
};

function loop() {
  telaAtiva.desenha();
  telaAtiva.atualiza();

  frames = frames + 1;
  requestAnimationFrame(loop);
}

window.addEventListener('click', function () {
  if (telaAtiva.click) {
    telaAtiva.click();
  }
});

window.addEventListener('keydown', function (event) {
  if (event.code === 'Space' && telaAtiva.click) {
    telaAtiva.click();
  }
});

mudaParaTela(Telas.INICIO);
loop();
