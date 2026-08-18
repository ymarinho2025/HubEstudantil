const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

/*
|--------------------------------------------------------------------------
| SESSÃO SEGURA DA PARTIDA
|--------------------------------------------------------------------------
*/

let gameSession = null;
let startingSession = false;
let finishingSession = false;

/*
 * Os checkpoints são processados em sequência.
 * Isso evita duas requisições alterando o score fora de ordem.
 */
let checkpointChain = Promise.resolve();

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÕES
|--------------------------------------------------------------------------
*/

const SCREEN_WIDTH = 500;
const SCREEN_HEIGHT = 800;

const TARGET_FPS = 60;
const FRAME_DURATION = 1000 / TARGET_FPS;

const bgMusic = document.getElementById("bgMusic");

if (bgMusic) {
    bgMusic.volume = 0.03;
}

/*
|--------------------------------------------------------------------------
| IMAGENS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| TAMANHOS
|--------------------------------------------------------------------------
*/

const PIPE_W = 78;
const PIPE_H = 480;

const FLOOR_H = 112;
const FLOOR_Y = SCREEN_HEIGHT - FLOOR_H;

const BIRD_W = 68;
const BIRD_H = 48;

const FLOOR_TILE_W = 336;

/*
|--------------------------------------------------------------------------
| FÍSICA
|--------------------------------------------------------------------------
*/

const GRAVITY = 0.45;
const JUMP_VELOCITY = -9.0;
const MAX_FALL_SPEED = 10;

/*
|--------------------------------------------------------------------------
| VARIÁVEIS DO JOGO
|--------------------------------------------------------------------------
*/

let bird;
let floor;
let pipes;

let score = 0;

let gameOver = false;
let gameOverTime = 0;

let scoreSaved = false;
let deathNotified = false;

let gamehubPaused = false;
let gameReady = false;


/*
|--------------------------------------------------------------------------
| CRIAR SESSÃO NO SERVIDOR
|--------------------------------------------------------------------------
*/

async function startServerSession() {

    if (startingSession) {
        return false;
    }

    startingSession = true;

    try {

        const response = await fetch(
            "/api/flappy/start.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                }
            }
        );

        const text = await response.text();

        let data;

        try {
            data = JSON.parse(text);
        } catch (error) {

            console.error(
                "Resposta inválida de start.php:",
                text
            );

            return false;
        }

        if (!response.ok || !data.ok) {

            console.error(
                "Não foi possível iniciar a partida:",
                data
            );

            return false;
        }

        gameSession = data.session;

        score = Number(data.score || 0);

        checkpointChain = Promise.resolve();

        console.log(
            "Partida registrada:",
            gameSession
        );

        return true;

    } catch (error) {

        console.error(
            "Erro ao criar sessão:",
            error
        );

        return false;

    } finally {

        startingSession = false;
    }
}


/*
|--------------------------------------------------------------------------
| REGISTRAR UM PONTO
|--------------------------------------------------------------------------
|
| O navegador NÃO envia:
|
| score = 10
|
| Ele apenas informa:
|
| "houve mais um checkpoint"
|
| Quem incrementa o score é o PHP.
|--------------------------------------------------------------------------
*/

function registerCheckpoint() {

    if (
        !gameSession ||
        gameOver ||
        finishingSession
    ) {
        return;
    }

    /*
     * Guarda a sessão atual.
     */
    const session = gameSession;

    checkpointChain = checkpointChain.then(
        async () => {

            /*
             * Se a partida já mudou,
             * não envia checkpoint antigo.
             */
            if (
                !session ||
                finishingSession
            ) {
                return;
            }

            try {

                const response = await fetch(
                    "/api/flappy/checkpoint.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({
                            session: session
                        })
                    }
                );

                const text = await response.text();

                let data;

                try {

                    data = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "Resposta inválida do checkpoint:",
                        text
                    );

                    return;
                }

                if (!response.ok || !data.ok) {

                    console.error(
                        "Checkpoint rejeitado:",
                        data
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | SCORE VEM DO SERVIDOR
                |--------------------------------------------------------------------------
                */

                score = Number(
                    data.score || 0
                );

                console.log(
                    "Checkpoint confirmado. Score:",
                    score
                );

            } catch (error) {

                console.error(
                    "Erro ao registrar checkpoint:",
                    error
                );
            }
        }
    );

    return checkpointChain;
}


/*
|--------------------------------------------------------------------------
| FINALIZAR PARTIDA
|--------------------------------------------------------------------------
*/

async function finishServerSession() {

    if (
        !gameSession ||
        finishingSession
    ) {
        return;
    }

    finishingSession = true;

    const sessionToFinish = gameSession;

    try {

        /*
        |--------------------------------------------------------------------------
        | ESPERA CHECKPOINTS PENDENTES
        |--------------------------------------------------------------------------
        |
        | Se o jogador passou pelo cano e morreu imediatamente,
        | esperamos o servidor confirmar aquele ponto antes de finalizar.
        |--------------------------------------------------------------------------
        */

        try {
            await checkpointChain;
        } catch (error) {
            console.error(
                "Erro na fila de checkpoints:",
                error
            );
        }

        const response = await fetch(
            "/api/flappy/finish.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    session: sessionToFinish
                })
            }
        );

        const text = await response.text();

        let data;

        try {

            data = JSON.parse(text);

        } catch (error) {

            console.error(
                "Resposta inválida de finish.php:",
                text
            );

            return;
        }

        if (!response.ok || !data.ok) {

            console.error(
                "Erro ao finalizar partida:",
                data
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CONFIRMA SCORE FINAL
        |--------------------------------------------------------------------------
        */

        score = Number(
            data.score || score
        );

        console.log(
            "Partida salva:",
            data
        );

        /*
         * Agora a sessão não poderá
         * ser reutilizada.
         */
        gameSession = null;

    } catch (error) {

        console.error(
            "Erro ao finalizar partida:",
            error
        );

    } finally {

        finishingSession = false;
    }
}


/*
|--------------------------------------------------------------------------
| PÁSSARO
|--------------------------------------------------------------------------
*/

class Bird {

    constructor(x, y) {

        this.x = x;
        this.y = y;

        this.vy = 0;

        this.angle = 0;

        this.maxRotation = 25;
        this.rotationSpeed = 4;

        this.animationTime = 5;

        this.frameCount = 0;
        this.frame = 0;
    }


    jump() {

        this.vy = JUMP_VELOCITY;

        if (
            bgMusic &&
            bgMusic.paused
        ) {

            bgMusic
                .play()
                .catch(() => {});
        }
    }


    move() {

        this.vy += GRAVITY;

        if (
            this.vy >
            MAX_FALL_SPEED
        ) {
            this.vy =
                MAX_FALL_SPEED;
        }

        this.y += this.vy;

        /*
        |--------------------------------------------------------------------------
        | ROTAÇÃO
        |--------------------------------------------------------------------------
        */

        if (this.vy < 0) {

            this.angle = Math.max(
                this.angle -
                this.rotationSpeed * 2,
                -25
            );

        } else {

            const targetAngle =
                Math.min(
                    this.vy * 8,
                    90
                );

            this.angle =
                Math.min(
                    this.angle +
                    this.rotationSpeed,
                    targetAngle
                );
        }
    }


    updateAnimation() {

        this.frameCount++;

        if (
            this.frameCount <
            this.animationTime
        ) {

            this.frame = 0;

        } else if (
            this.frameCount <
            this.animationTime * 2
        ) {

            this.frame = 1;

        } else if (
            this.frameCount <
            this.animationTime * 3
        ) {

            this.frame = 2;

        } else if (
            this.frameCount <
            this.animationTime * 4
        ) {

            this.frame = 1;

        } else {

            this.frame = 0;

            this.frameCount = 0;
        }

        if (this.angle >= 70) {

            this.frame = 1;

            this.frameCount =
                this.animationTime * 2;
        }
    }


    draw() {

        this.updateAnimation();

        const cx =
            this.x +
            BIRD_W / 2;

        const cy =
            this.y +
            BIRD_H / 2;

        ctx.save();

        ctx.translate(
            cx,
            cy
        );

        ctx.rotate(
            (this.angle * Math.PI) /
            180
        );

        ctx.drawImage(
            birdFrames[this.frame],
            -BIRD_W / 2,
            -BIRD_H / 2,
            BIRD_W,
            BIRD_H
        );

        ctx.restore();
    }


    getBounds() {

        return {
            x: this.x + 6,
            y: this.y + 6,
            width: BIRD_W - 12,
            height: BIRD_H - 12
        };
    }
}


/*
|--------------------------------------------------------------------------
| CANOS
|--------------------------------------------------------------------------
*/

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

        const maxTop =
            FLOOR_Y -
            this.gap -
            100;

        this.height =
            Math.floor(
                Math.random() *
                (maxTop - minTop)
            ) +
            minTop;

        this.topY =
            this.height -
            PIPE_H;

        this.bottomY =
            this.height +
            this.gap;
    }


    move() {

        this.x -= this.speed;
    }


    draw() {

        /*
        |--------------------------------------------------------------------------
        | CANO SUPERIOR
        |--------------------------------------------------------------------------
        */

        ctx.save();

        ctx.translate(
            this.x +
            PIPE_W / 2,
            this.height
        );

        ctx.scale(
            1,
            -1
        );

        ctx.drawImage(
            pipeImage,
            -PIPE_W / 2,
            0,
            PIPE_W,
            PIPE_H
        );

        ctx.restore();

        /*
        |--------------------------------------------------------------------------
        | CANO INFERIOR
        |--------------------------------------------------------------------------
        */

        ctx.drawImage(
            pipeImage,
            this.x,
            this.bottomY,
            PIPE_W,
            PIPE_H
        );
    }


    collides(bird) {

        const b =
            bird.getBounds();

        const top = {

            x: this.x,

            y: this.topY,

            width: PIPE_W,

            height: PIPE_H
        };

        const bottom = {

            x: this.x,

            y: this.bottomY,

            width: PIPE_W,

            height: PIPE_H
        };

        return (
            rectCollision(
                b,
                top
            ) ||

            rectCollision(
                b,
                bottom
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| CHÃO
|--------------------------------------------------------------------------
*/

class Floor {

    constructor(y) {

        this.y = y;

        this.x1 = 0;

        this.x2 =
            FLOOR_TILE_W;

        this.speed = 5;
    }


    move() {

        this.x1 -=
            this.speed;

        this.x2 -=
            this.speed;

        if (
            this.x1 +
            FLOOR_TILE_W <
            0
        ) {

            this.x1 =
                this.x2 +
                FLOOR_TILE_W;
        }

        if (
            this.x2 +
            FLOOR_TILE_W <
            0
        ) {

            this.x2 =
                this.x1 +
                FLOOR_TILE_W;
        }
    }


    draw() {

        ctx.drawImage(
            floorImage,
            this.x1,
            this.y,
            FLOOR_TILE_W,
            FLOOR_H
        );

        ctx.drawImage(
            floorImage,
            this.x2,
            this.y,
            FLOOR_TILE_W,
            FLOOR_H
        );

        ctx.drawImage(
            floorImage,
            this.x1 +
            FLOOR_TILE_W * 2,
            this.y,
            FLOOR_TILE_W,
            FLOOR_H
        );
    }
}


/*
|--------------------------------------------------------------------------
| COLISÃO
|--------------------------------------------------------------------------
*/

function rectCollision(a, b) {

    return (
        a.x <
        b.x + b.width &&

        a.x + a.width >
        b.x &&

        a.y <
        b.y + b.height &&

        a.y + a.height >
        b.y
    );
}


/*
|--------------------------------------------------------------------------
| REINICIAR PARTIDA
|--------------------------------------------------------------------------
*/

async function resetGame() {

    gameReady = false;

    bird =
        new Bird(
            100,
            350
        );

    floor =
        new Floor(
            FLOOR_Y
        );

    pipes = [
        new Pipe(600)
    ];

    score = 0;

    gameOver = false;

    gameOverTime = 0;

    scoreSaved = false;

    deathNotified = false;

    gameSession = null;

    checkpointChain =
        Promise.resolve();

    const started =
        await startServerSession();

    if (!started) {

        console.error(
            "O jogo não pode começar sem uma sessão válida."
        );

        gamehubPaused = true;

        return false;
    }

    gameReady = true;

    return true;
}


/*
|--------------------------------------------------------------------------
| TEXTO CENTRAL
|--------------------------------------------------------------------------
*/

function drawCenteredText(
    text,
    y,
    color = "#fff",
    size = 50
) {

    ctx.fillStyle =
        color;

    ctx.font =
        `bold ${size}px Arial`;

    const width =
        ctx.measureText(text)
            .width;

    ctx.fillText(
        text,
        (
            SCREEN_WIDTH -
            width
        ) / 2,
        y
    );
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

function update() {

    if (
        gamehubPaused ||
        !gameReady
    ) {
        return;
    }

    if (!gameOver) {

        bird.move();

        floor.move();

        let addPipe = false;

        const removePipes = [];

        for (
            const pipe
            of pipes
        ) {

            pipe.move();

            /*
            |--------------------------------------------------------------------------
            | COLISÃO
            |--------------------------------------------------------------------------
            */

            if (
                pipe.collides(bird)
            ) {

                gameOver = true;

                gameOverTime =
                    Date.now();
            }


            /*
            |--------------------------------------------------------------------------
            | PASSOU PELO CANO
            |--------------------------------------------------------------------------
            */

            if (
                !pipe.passed &&
                bird.x >
                pipe.x + PIPE_W
            ) {

                pipe.passed = true;

                addPipe = true;
            }


            /*
            |--------------------------------------------------------------------------
            | REMOVE CANO
            |--------------------------------------------------------------------------
            */

            if (
                pipe.x +
                PIPE_W <
                0
            ) {

                removePipes.push(
                    pipe
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NOVO PONTO
        |--------------------------------------------------------------------------
        */

        if (
            addPipe &&
            !gameOver
        ) {

            pipes.push(
                new Pipe(
                    SCREEN_WIDTH +
                    50
                )
            );

            /*
             * NÃO existe mais score++.
             *
             * O servidor incrementa.
             */
            registerCheckpoint();
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE CANOS ANTIGOS
        |--------------------------------------------------------------------------
        */

        for (
            const pipe
            of removePipes
        ) {

            const index =
                pipes.indexOf(pipe);

            if (index > -1) {

                pipes.splice(
                    index,
                    1
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COLISÃO COM CHÃO / TETO
        |--------------------------------------------------------------------------
        */

        if (
            bird.y +
            BIRD_H >
            FLOOR_Y ||

            bird.y <
            0
        ) {

            gameOver = true;

            gameOverTime =
                Date.now();
        }
    }
}


/*
|--------------------------------------------------------------------------
| DRAW
|--------------------------------------------------------------------------
*/

function draw() {

    ctx.clearRect(
        0,
        0,
        SCREEN_WIDTH,
        SCREEN_HEIGHT
    );

    ctx.drawImage(
        bgImage,
        0,
        0,
        SCREEN_WIDTH,
        SCREEN_HEIGHT
    );

    for (
        const pipe
        of pipes
    ) {

        pipe.draw();
    }

    bird.draw();

    floor.draw();


    /*
    |--------------------------------------------------------------------------
    | SCORE
    |--------------------------------------------------------------------------
    */

    ctx.shadowColor =
        "rgba(0,0,0,0.8)";

    ctx.shadowBlur = 5;

    ctx.fillStyle =
        "#ffffff";

    ctx.font =
        "bold 38px Arial";

    const scoreText =
        `Pontuação: ${score}`;

    const scoreWidth =
        ctx.measureText(
            scoreText
        ).width;

    ctx.fillText(
        scoreText,
        (
            SCREEN_WIDTH -
            scoreWidth
        ) / 2,
        55
    );

    ctx.shadowBlur = 0;


    /*
    |--------------------------------------------------------------------------
    | GAME OVER
    |--------------------------------------------------------------------------
    */

    if (gameOver) {

        /*
        |--------------------------------------------------------------------------
        | AVISA GAMEHUB
        |--------------------------------------------------------------------------
        */

        if (!deathNotified) {

            deathNotified = true;

            window.parent.postMessage(
                {
                    type:
                        "gamehub:gameover",

                    score: score
                },

                window.location.origin
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SALVA PARTIDA
        |--------------------------------------------------------------------------
        */

        if (!scoreSaved) {

            scoreSaved = true;

            finishServerSession()
                .catch(
                    console.error
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TELA GAME OVER
        |--------------------------------------------------------------------------
        */

        ctx.fillStyle =
            "rgba(0,0,0,0.5)";

        ctx.fillRect(
            0,
            SCREEN_HEIGHT / 2 - 85,
            SCREEN_WIDTH,
            150
        );

        ctx.shadowColor =
            "rgba(0,0,0,0.9)";

        ctx.shadowBlur = 8;

        drawCenteredText(
            "GAME OVER",
            SCREEN_HEIGHT / 2 - 15,
            "#ff3333",
            60
        );

        const remaining =
            Math.max(
                0,
                1 -
                Math.floor(
                    (
                        Date.now() -
                        gameOverTime
                    ) /
                    1000
                )
            );

        drawCenteredText(

            remaining > 0

                ? `Pergunta em ${remaining}...`

                : "Responda para continuar",

            SCREEN_HEIGHT / 2 + 45,

            "#ffffff",

            30
        );

        ctx.shadowBlur = 0;
    }
}


/*
|--------------------------------------------------------------------------
| LOOP 60 FPS
|--------------------------------------------------------------------------
*/

let lastTime = 0;
let accumulator = 0;

function gameLoop(timestamp) {

    requestAnimationFrame(
        gameLoop
    );

    /*
     * Primeira execução.
     */
    if (!lastTime) {
        lastTime = timestamp;
    }

    const elapsed =
        timestamp -
        lastTime;

    lastTime =
        timestamp;

    accumulator +=
        Math.min(
            elapsed,
            100
        );

    while (
        accumulator >=
        FRAME_DURATION
    ) {

        update();

        accumulator -=
            FRAME_DURATION;
    }

    draw();
}


/*
|--------------------------------------------------------------------------
| CONTROLES
|--------------------------------------------------------------------------
*/

function jumpAction() {

    if (
        gameReady &&
        !gameOver &&
        !gamehubPaused
    ) {

        bird.jump();
    }
}


document.addEventListener(
    "keydown",
    (event) => {

        if (
            event.code ===
            "Space"
        ) {

            event.preventDefault();

            jumpAction();
        }
    }
);


canvas.addEventListener(
    "click",
    jumpAction
);


canvas.addEventListener(
    "touchstart",
    (event) => {

        event.preventDefault();

        jumpAction();
    },
    {
        passive: false
    }
);


/*
|--------------------------------------------------------------------------
| COMUNICAÇÃO COM O GAMEHUB
|--------------------------------------------------------------------------
*/

window.addEventListener(
    "message",
    async (event) => {

        if (
            event.origin !==
            window.location.origin
        ) {
            return;
        }

        if (!event.data) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | PAUSAR
        |--------------------------------------------------------------------------
        */

        if (
            event.data.type ===
            "gamehub:pause"
        ) {

            gamehubPaused = true;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTINUAR
        |--------------------------------------------------------------------------
        */

        if (
            event.data.type ===
            "gamehub:continue"
        ) {

            if (gameOver) {

                /*
                 * Garante que a partida anterior
                 * terminou antes de criar outra.
                 */
                await finishServerSession();

                await resetGame();
            }

            gamehubPaused = false;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | REINICIAR
        |--------------------------------------------------------------------------
        */

        if (
            event.data.type ===
            "gamehub:restart"
        ) {

            if (gameOver) {

                await finishServerSession();
            }

            await resetGame();

            gamehubPaused = false;
        }
    }
);


/*
|--------------------------------------------------------------------------
| CARREGAMENTO
|--------------------------------------------------------------------------
*/

function imageReady(image) {

    return new Promise(
        resolve => {

            if (
                image.complete &&
                image.naturalWidth > 0
            ) {

                resolve();

                return;
            }

            image.onload =
                () => resolve();

            image.onerror =
                () => resolve();
        }
    );
}


async function bootstrap() {

    /*
    |--------------------------------------------------------------------------
    | ESPERA AS IMAGENS
    |--------------------------------------------------------------------------
    */

    await Promise.all([
        imageReady(bgImage),
        imageReady(floorImage),
        imageReady(pipeImage),

        ...birdFrames.map(
            imageReady
        )
    ]);


    /*
    |--------------------------------------------------------------------------
    | CRIA SESSÃO ANTES DO JOGO COMEÇAR
    |--------------------------------------------------------------------------
    */

    const started =
        await resetGame();

    if (!started) {

        ctx.fillStyle =
            "#ffffff";

        ctx.font =
            "bold 22px Arial";

        ctx.fillText(
            "Não foi possível iniciar a partida.",
            45,
            SCREEN_HEIGHT / 2
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INICIA LOOP
    |--------------------------------------------------------------------------
    */

    requestAnimationFrame(
        gameLoop
    );
}


bootstrap().catch(
    error => {

        console.error(
            "Erro ao iniciar João Bird:",
            error
        );
    }
);