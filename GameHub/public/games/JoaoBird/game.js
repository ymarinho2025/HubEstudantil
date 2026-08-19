const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

const SCREEN_WIDTH = 500;
const SCREEN_HEIGHT = 800;

const TARGET_FPS = 60;
const FRAME_DURATION = 1000 / TARGET_FPS; // ~16.67ms

const bgMusic = document.getElementById("bgMusic");
bgMusic.volume = 0.03;

const bgImage = new Image();
bgImage.src = "bg.png";

const floorImage = new Image();
floorImage.src = "floor.png";

const pipeImage = new Image();
pipeImage.src = "pipe.png";

const birdFrames = [];
for (let i = 1; i <= 3; i++) {
  const img = new Image();
  img.src = `bird${i}.png`;
  birdFrames.push(img);
}

// Rendered sizes
const PIPE_W = 78;
const PIPE_H = 480;
const FLOOR_H = 112;
const FLOOR_Y = SCREEN_HEIGHT - FLOOR_H;
const BIRD_W = 68;
const BIRD_H = 48;
const FLOOR_TILE_W = 336;

// Physics constants (tuned for 60fps feel)
const GRAVITY = 0.45;        // gravidade por frame (era ~1.5 * t² imprevisível)
const JUMP_VELOCITY = -9.0;  // impulso do pulo
const MAX_FALL_SPEED = 10;   // velocidade máxima de queda

class Bird {
  constructor(x, y) {
    this.x = x;
    this.y = y;
    this.vy = 0;           // velocidade vertical simples
    this.angle = 0;

    this.maxRotation = 25;
    this.rotationSpeed = 4;
    this.animationTime = 5;
    this.frameCount = 0;
    this.frame = 0;
  }

  jump() {
    this.vy = JUMP_VELOCITY;
    if (bgMusic.paused) bgMusic.play().catch(() => {});
  }

  move() {
    // Física simples: velocidade + gravidade por frame
    this.vy += GRAVITY;
    if (this.vy > MAX_FALL_SPEED) this.vy = MAX_FALL_SPEED;
    this.y += this.vy;

    // Rotação baseada na velocidade vertical
    if (this.vy < 0) {
      // Subindo → aponta para cima
      this.angle = Math.max(this.angle - this.rotationSpeed * 2, -25);
    } else {
      // Caindo → aponta para baixo
      const targetAngle = Math.min(this.vy * 8, 90);
      this.angle = Math.min(this.angle + this.rotationSpeed, targetAngle);
    }
  }

  updateAnimation() {
    this.frameCount++;
    if (this.frameCount < this.animationTime) this.frame = 0;
    else if (this.frameCount < this.animationTime * 2) this.frame = 1;
    else if (this.frameCount < this.animationTime * 3) this.frame = 2;
    else if (this.frameCount < this.animationTime * 4) this.frame = 1;
    else { this.frame = 0; this.frameCount = 0; }

    if (this.angle >= 70) { this.frame = 1; this.frameCount = this.animationTime * 2; }
  }

  draw() {
    this.updateAnimation();
    const cx = this.x + BIRD_W / 2;
    const cy = this.y + BIRD_H / 2;
    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate((this.angle * Math.PI) / 180);
    ctx.drawImage(birdFrames[this.frame], -BIRD_W / 2, -BIRD_H / 2, BIRD_W, BIRD_H);
    ctx.restore();
  }

  getBounds() {
    return { x: this.x + 6, y: this.y + 6, width: BIRD_W - 12, height: BIRD_H - 12 };
  }
}

class Pipe {
  constructor(x) {
    this.x = x;
    this.gap = 200;
    this.speed = 5;
    this.passed = false;
    this.setHeight();
  }

  setHeight() {
    const minTop = 100;
    const maxTop = FLOOR_Y - this.gap - 100;
    this.height = Math.floor(Math.random() * (maxTop - minTop)) + minTop;
    this.topY = this.height - PIPE_H;
    this.bottomY = this.height + this.gap;
  }

  move() { this.x -= this.speed; }

  draw() {
    // Top pipe (flipped)
    ctx.save();
    ctx.translate(this.x + PIPE_W / 2, this.height);
    ctx.scale(1, -1);
    ctx.drawImage(pipeImage, -PIPE_W / 2, 0, PIPE_W, PIPE_H);
    ctx.restore();
    // Bottom pipe
    ctx.drawImage(pipeImage, this.x, this.bottomY, PIPE_W, PIPE_H);
  }

  collides(bird) {
    const b = bird.getBounds();
    const top = { x: this.x, y: this.topY, width: PIPE_W, height: PIPE_H };
    const bot = { x: this.x, y: this.bottomY, width: PIPE_W, height: PIPE_H };
    return rectCollision(b, top) || rectCollision(b, bot);
  }
}

class Floor {
  constructor(y) {
    this.y = y;
    this.x1 = 0;
    this.x2 = FLOOR_TILE_W;
    this.speed = 5;
  }

  move() {
    this.x1 -= this.speed;
    this.x2 -= this.speed;
    if (this.x1 + FLOOR_TILE_W < 0) this.x1 = this.x2 + FLOOR_TILE_W;
    if (this.x2 + FLOOR_TILE_W < 0) this.x2 = this.x1 + FLOOR_TILE_W;
  }

  draw() {
    ctx.drawImage(floorImage, this.x1, this.y, FLOOR_TILE_W, FLOOR_H);
    ctx.drawImage(floorImage, this.x2, this.y, FLOOR_TILE_W, FLOOR_H);
    ctx.drawImage(floorImage, this.x1 + FLOOR_TILE_W * 2, this.y, FLOOR_TILE_W, FLOOR_H);
  }
}

function rectCollision(a, b) {
  return a.x < b.x + b.width && a.x + a.width > b.x &&
         a.y < b.y + b.height && a.y + a.height > b.y;
}

let bird, floor, pipes, score, gameOver, gameOverTime, scoreSaved, deathNotified, gamehubPaused=false;

function resetGame() {
  bird = new Bird(100, 350);
  floor = new Floor(FLOOR_Y);
  pipes = [new Pipe(600)];
  score = 0;
  gameOver = false;
  gameOverTime = 0;
  scoreSaved = false;
  deathNotified = false;
}

function drawCenteredText(text, y, color = "#fff", size = 50) {
  ctx.fillStyle = color;
  ctx.font = `bold ${size}px Arial`;
  const w = ctx.measureText(text).width;
  ctx.fillText(text, (SCREEN_WIDTH - w) / 2, y);
}

function update() {
  if (gamehubPaused) return;
  if (!gameOver) {
    bird.move();
    floor.move();

    let addPipe = false;
    const removePipes = [];

    for (const pipe of pipes) {
      pipe.move();
      if (pipe.collides(bird)) { gameOver = true; gameOverTime = Date.now(); }
      if (!pipe.passed && bird.x > pipe.x + PIPE_W) { pipe.passed = true; addPipe = true; }
      if (pipe.x + PIPE_W < 0) removePipes.push(pipe);
    }

    if (addPipe) { score++; pipes.push(new Pipe(SCREEN_WIDTH + 50)); }
    for (const pipe of removePipes) {
      const i = pipes.indexOf(pipe);
      if (i > -1) pipes.splice(i, 1);
    }

    if (bird.y + BIRD_H > FLOOR_Y || bird.y < 0) { gameOver = true; gameOverTime = Date.now(); }
  } else {
    // Reinício controlado pelo GameHub após a pergunta educacional.
  }
}

function draw() {
  ctx.clearRect(0, 0, SCREEN_WIDTH, SCREEN_HEIGHT);
  ctx.drawImage(bgImage, 0, 0, SCREEN_WIDTH, SCREEN_HEIGHT);

  for (const pipe of pipes) pipe.draw();
  bird.draw();
  floor.draw();

  // Score
  ctx.shadowColor = "rgba(0,0,0,0.8)";
  ctx.shadowBlur = 5;
  ctx.fillStyle = "#ffffff";
  ctx.font = "bold 38px Arial";
  const scoreText = `Pontuação: ${score}`;
  const sw = ctx.measureText(scoreText).width;
  ctx.fillText(scoreText, (SCREEN_WIDTH - sw) / 2, 55);
  ctx.shadowBlur = 0;

  if (gameOver) {
    if (!deathNotified) {
      deathNotified = true;
      window.parent.postMessage({type:"gamehub:gameover", score}, window.location.origin);
    }
    if (!scoreSaved) {
      scoreSaved = true;
      fetch("/api/flappy/pontuacao.php", {
    method: "POST",

    headers: {
        "Content-Type": "application/json"
    },

    body: JSON.stringify({
        score: Number(score)
    })

})
.then(async response => {

    const text = await response.text();

    let data;

    try {
        data = JSON.parse(text);
    } catch (e) {

        console.error(
            "Resposta inválida da API:",
            text
        );

        return;
    }

    if (!response.ok || !data.ok) {

        console.error(
            "Erro ao salvar pontuação:",
            data
        );

        return;
    }

    console.log(
        "Pontuação salva:",
        data
    );

})
.catch(error => {

    console.error(
        "Falha ao enviar pontuação:",
        error
    );

});
    }
    ctx.fillStyle = "rgba(0,0,0,0.5)";
    ctx.fillRect(0, SCREEN_HEIGHT / 2 - 85, SCREEN_WIDTH, 150);
    ctx.shadowColor = "rgba(0,0,0,0.9)";
    ctx.shadowBlur = 8;
    drawCenteredText("GAME OVER", SCREEN_HEIGHT / 2 - 15, "#ff3333", 60);
    const remaining = Math.max(0, 1 - Math.floor((Date.now() - gameOverTime) / 1000));
    drawCenteredText(remaining > 0 ? `Pergunta em ${remaining}...` : `Responda para continuar`, SCREEN_HEIGHT / 2 + 45, "#ffffff", 30);
    ctx.shadowBlur = 0;
  }
}

// ── Loop com FPS fixo em 60 ──────────────────────────────────────────────────
let lastTime = 0;
let accumulator = 0;

function gameLoop(timestamp) {
  requestAnimationFrame(gameLoop);

  const elapsed = timestamp - lastTime;
  lastTime = timestamp;

  // Limita o acúmulo para evitar espiral da morte (ex: aba em background)
  accumulator += Math.min(elapsed, 100);

  // Roda update quantas vezes forem necessárias para manter 60 UPS fixos
  while (accumulator >= FRAME_DURATION) {
    update();
    accumulator -= FRAME_DURATION;
  }

  draw();
}

// Controles
function jumpAction() { if (!gameOver) bird.jump(); }

document.addEventListener("keydown", (e) => {
  if (e.code === "Space") { e.preventDefault(); jumpAction(); }
});
canvas.addEventListener("click", jumpAction);
canvas.addEventListener("touchstart", (e) => { e.preventDefault(); jumpAction(); }, { passive: false });

resetGame();

Promise.all([
  new Promise(r => { bgImage.onload = r; if (bgImage.complete) r(); }),
  new Promise(r => { floorImage.onload = r; if (floorImage.complete) r(); }),
  new Promise(r => { pipeImage.onload = r; if (pipeImage.complete) r(); }),
  ...birdFrames.map(img => new Promise(r => { img.onload = r; if (img.complete) r(); }))
]).then(() => requestAnimationFrame(gameLoop));
window.addEventListener("message", (event) => {
  if (event.origin !== window.location.origin || !event.data) return;
  if (event.data.type === "gamehub:pause") gamehubPaused = true;
  if (event.data.type === "gamehub:continue") {
    if (gameOver) resetGame();
    gamehubPaused = false;
  }
  if (event.data.type === "gamehub:restart") { resetGame(); gamehubPaused=false; }
});