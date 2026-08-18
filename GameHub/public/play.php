<?php
$pdo=require __DIR__.'/../src/db.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../src/educational_catalog.php';
$user=gamehub_require_user('/login.php',$pdo);
$catalog=gamehub_educational_catalog();
$slug=$_GET['game']??'';
if(!isset($catalog[$slug])){http_response_code(404);echo 'Jogo não encontrado.';exit;}
$game=$catalog[$slug];
$questions=$game['questions'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($game['title'])?> | GameHub Educacional</title>
<link rel="stylesheet" href="/css/style.css">
<style>
.edu-shell{min-height:100vh;display:flex;flex-direction:column}.edu-main{width:min(1540px,calc(100% - 24px));margin:0 auto;padding:20px 0 30px;flex:1}.edu-head{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;margin-bottom:18px}.edu-kicker{font-family:var(--hud);font-size:10px;letter-spacing:2px;color:var(--brand);text-transform:uppercase}.edu-title{margin:5px 0 8px;font-family:var(--hud);font-size:clamp(24px,4vw,42px);text-transform:uppercase}.edu-meta{display:flex;gap:8px;flex-wrap:wrap}.edu-badge{padding:8px 11px;border-radius:999px;border:1px solid var(--line);background:rgba(255,255,255,.05);font-size:12px;font-weight:800;color:var(--muted)}
.edu-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:16px;align-items:start}.game-stage{position:relative;min-height:760px;border:1px solid rgba(0,245,200,.32);border-radius:26px;background:rgba(2,5,15,.82);box-shadow:var(--shadow),0 0 38px rgba(0,245,200,.13);overflow:hidden;display:flex;align-items:center;justify-content:center;padding:10px}.game-window{width:100%;height:calc(100vh - 245px);min-height:720px;max-height:900px;border-radius:18px;overflow:hidden;background:#000;border:1px solid var(--line);position:relative}.game-window iframe{width:100%;height:100%;border:0;background:#000;display:block;overflow:hidden}.game-lock{position:absolute;inset:0;z-index:5;background:linear-gradient(145deg,rgba(5,8,22,.97),rgba(17,12,36,.97));display:flex;align-items:center;justify-content:center;padding:24px}.challenge-card{width:min(620px,100%);border:1px solid var(--line);border-radius:24px;background:rgba(255,255,255,.06);padding:28px;box-shadow:0 25px 80px rgba(0,0,0,.5)}.challenge-card h2{margin:0 0 12px;font-size:17px}.question{font-size:21px;font-weight:800;line-height:1.4;margin:18px 0}.answers{display:grid;grid-template-columns:1fr 1fr;gap:10px}.answer{font-family:var(--font);font-size:14px;text-transform:none;letter-spacing:0;background:rgba(255,255,255,.06);color:#fff;border:1px solid var(--line);box-shadow:none}.answer.correct{background:rgba(32,240,162,.18);border-color:var(--success);color:#b8ffe4}.answer.wrong{background:rgba(255,107,145,.16);border-color:var(--danger);color:#ffd1dd}.feedback{min-height:24px;margin-top:14px;font-size:13px;font-weight:700;color:var(--muted)}.side{display:flex;flex-direction:column;gap:14px}.edu-panel{border:1px solid var(--line);border-radius:20px;background:rgba(255,255,255,.055);padding:18px;backdrop-filter:blur(14px)}.edu-panel h3{font-family:var(--hud);font-size:12px;letter-spacing:1px;text-transform:uppercase;margin:0 0 10px;color:#fff}.edu-panel p{font-size:13px;line-height:1.6;color:var(--muted);margin:0}.edu-actions{display:grid;gap:9px}.edu-actions .btn{width:100%}.progress-line{height:5px;background:rgba(255,255,255,.07);border-radius:999px;overflow:hidden;margin-top:12px}.progress-line span{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--brand),var(--brand2));transition:.3s}.question-timer{font-family:var(--hud);font-size:13px;color:var(--brand);margin:10px 0}
.question-timer.danger{color:#ff6b91}.hidden{display:none!important}.unlock-note{color:var(--brand);font-family:var(--hud);font-size:10px;letter-spacing:1px;margin-bottom:8px}.game-window:after{content:'GAMEHUB EDUCACIONAL';position:absolute;right:12px;bottom:8px;font-family:var(--hud);font-size:8px;letter-spacing:1px;color:rgba(255,255,255,.28);pointer-events:none}
@media(max-width:960px){.edu-layout{grid-template-columns:1fr}.side{display:grid;grid-template-columns:1fr 1fr}.game-window{height:78vh;min-height:650px}}@media(max-width:620px){.edu-head{grid-template-columns:1fr}.edu-head>.btn{justify-self:start}.answers{grid-template-columns:1fr}.side{grid-template-columns:1fr}.game-stage{padding:5px;min-height:620px}.game-window{min-height:600px;height:76vh}.challenge-card{padding:20px}.question{font-size:18px}}
</style>
</head>
<body class="edu-shell">
<div class="marquee-bar"><span class="marquee-inner">★ GAMEHUB EDUCACIONAL &nbsp;★ APRENDER JOGANDO &nbsp;★ <?=htmlspecialchars(mb_strtoupper($game['area']))?> &nbsp;★ DESAFIO PEDAGÓGICO &nbsp;★ GAMEHUB EDUCACIONAL &nbsp;</span></div>
<header class="topbar"><div class="brand"><span class="brand-icon">G</span><span class="brand-text">GameHub</span></div><div class="actions"><span><?=htmlspecialchars($user['name'] ?? 'Aluno')?></span><a class="btn secondary" href="/home.php">← Jogos</a></div></header>
<main class="edu-main">
<section class="edu-head"><div><div class="edu-kicker">// MISSÃO EDUCACIONAL</div><h1 class="edu-title"><?=$game['icon']?> <?=htmlspecialchars($game['title'])?></h1><div class="edu-meta"><span class="edu-badge">📘 <?=htmlspecialchars($game['area'])?></span><span class="edu-badge">🎯 <?=htmlspecialchars($game['skill'])?></span></div></div><button id="newChallengeTop" class="btn secondary">Novo desafio</button></section>
<section class="edu-layout">
<div class="game-stage"><div class="game-window"><iframe id="gameFrame" src="about:blank" data-src="<?=htmlspecialchars($game['route'])?>" scrolling="no" allow="autoplay; fullscreen" title="<?=htmlspecialchars($game['title'])?>"></iframe><div id="gameLock" class="game-lock"><div class="challenge-card"><div class="unlock-note">🧠 +10 PONTOS DE QI</div><h2>Desafio rápido de <?=htmlspecialchars($game['area'])?></h2><div id="questionTimer" class="question-timer hidden"></div><div id="question" class="question"></div><div id="answers" class="answers"></div><div id="feedback" class="feedback">Responda corretamente para continuar a partida e ganhar 10 pontos de QI.</div><div class="progress-line"><span id="progress"></span></div></div></div></div></div>
<aside class="side"><div class="edu-panel"><h3>Objetivo pedagógico</h3><p><?=htmlspecialchars($game['mission'])?></p></div><div class="edu-panel"><h3>Como jogar</h3><p><?=htmlspecialchars($game['instructions'])?></p></div><div class="edu-panel"><h3>Desafio extra</h3><p><?php if(in_array($slug,['paint','piano','pixel-art'],true)):?>A cada 1:00 minuto surge uma pergunta educacional. O jogo fica pausado enquanto você responde.<?php elseif($slug==='datilografia'):?>Ao errar uma letra, a palavra correta é mostrada e surge uma pergunta educacional. Ela não concede QI e o progresso reinicia.<?php else:?>Ao morrer ou encerrar uma rodada, surge uma pergunta. Cada resposta correta vale +10 pontos de QI.<?php endif;?></p></div><div class="edu-panel edu-actions"><button id="newChallenge" class="btn">🧠 Treinar pergunta</button><a class="btn secondary" href="/home.php">← Jogos</a></div></aside>
</section>
</main>
<script>
const questions=<?=json_encode($questions,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const gameSlug=<?=json_encode($slug)?>, gameRoute=<?=json_encode($game['route'])?>;
const timedGames=['paint','piano','pixel-art'];
let index=Math.floor(Math.random()*questions.length),waiting=false,currentMode='startup';
let creativeSeconds=60,creativeTimer=null,gameLoaded=false;
const qEl=document.getElementById('question'),answers=document.getElementById('answers'),
feedback=document.getElementById('feedback'),lock=document.getElementById('gameLock'),
frame=document.getElementById('gameFrame'),progress=document.getElementById('progress'),
timerEl=document.getElementById('questionTimer'),globalTimer=document.getElementById('globalGameTimerValue');
function pauseGame(){if(gameLoaded)frame.contentWindow.postMessage({type:'gamehub:pause'},location.origin)}
function resumeGame(restart=false){if(!gameLoaded){frame.src=gameRoute;gameLoaded=true}else frame.contentWindow.postMessage({type:restart?'gamehub:restart':'gamehub:continue'},location.origin);frame.focus()}
function esc(s){return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
function renderQuestion(mode='normal',payload={}){
 if(waiting)return;waiting=true;currentMode=mode;pauseGame();lock.classList.remove('hidden');progress.style.width='0';
 const q=questions[index];qEl.textContent=q.q;answers.innerHTML='';
 feedback.innerHTML=mode==='typing-error'?'⌨️ Palavra correta: <strong>'+esc(payload.expected||'')+'</strong><br>Responda para reiniciar. Sem pontos de QI.':(mode==='startup'?'🎓 Responda para iniciar o jogo. Resposta correta vale +10 QI.':'Resposta correta = +10 QI e o jogo continua.');
 q.a.forEach((t,i)=>{const b=document.createElement('button');b.className='answer';b.textContent=t;b.onclick=()=>answer(i,b);answers.appendChild(b)});
}
async function answer(i,b){
 if(!waiting)return;const q=questions[index];document.querySelectorAll('.answer').forEach(x=>x.disabled=true);
 if(i!==q.correct){b.classList.add('wrong');feedback.textContent='❌ Tente novamente. '+q.hint;setTimeout(()=>document.querySelectorAll('.answer').forEach(x=>{x.disabled=false;x.classList.remove('wrong')}),350);return}
 b.classList.add('correct');progress.style.width='100%';
 if(currentMode==='typing-error'){feedback.textContent='✅ Correto. Progresso reiniciado — sem QI.';waiting=false;setTimeout(()=>{lock.classList.add('hidden');resumeGame(true)},250);return}
 let total='';try{const r=await fetch('/api/qi.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({game:gameSlug})});const x=await r.json();if(x.ok)total=` Total: ${x.points} QI.`}catch(e){}
 feedback.textContent='✅ Correto! +10 QI.'+total;waiting=false;
 setTimeout(()=>{lock.classList.add('hidden');resumeGame(false);if(timedGames.includes(gameSlug))resetTimer()},250);
}
function manual(){if(waiting)return;index=(index+1)%questions.length;renderQuestion('extra')}
function resetTimer(){if(!timedGames.includes(gameSlug))return;clearInterval(creativeTimer);creativeSeconds=60;updateTimer();creativeTimer=setInterval(()=>{if(waiting)return;creativeSeconds--;updateTimer();if(creativeSeconds<=0){clearInterval(creativeTimer);index=(index+1)%questions.length;renderQuestion('timer')}},1000)}
function updateTimer(){if(globalTimer){const m=Math.floor(creativeSeconds/60),s=creativeSeconds%60;globalTimer.textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')}}
window.addEventListener('message',e=>{if(e.source!==frame.contentWindow)return;const d=e.data||{};if((d.type==='gamehub:gameover'||d.type==='gamehub:roundend')&&!waiting){index=(index+1)%questions.length;renderQuestion('death',d)}if(d.type==='gamehub:typing-error'&&!waiting){index=(index+1)%questions.length;renderQuestion('typing-error',d)}});
document.getElementById('newChallenge').onclick=manual;document.getElementById('newChallengeTop').onclick=manual;
updateTimer();renderQuestion('startup');
</script>
</body></html>
