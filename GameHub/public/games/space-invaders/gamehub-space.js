const c=document.getElementById('game'),g=c.getContext('2d'),scoreEl=document.getElementById('score'),livesEl=document.getElementById('lives'),levelEl=document.getElementById('level');
const W=c.width,H=c.height;let keys={},bullets=[],enemyBullets=[],aliens=[],stars=[],score=0,lives=3,level=1,paused=false,over=false,lastShot=0,alienDir=1,lastEnemyShot=0;
const ship={x:W/2-32,y:H-75,w:64,h:32,speed:7};
for(let i=0;i<100;i++)stars.push({x:Math.random()*W,y:Math.random()*H,s:Math.random()*2+1});
function formation(){aliens=[];const rows=Math.min(5,3+Math.floor((level-1)/2)),cols=8,gapX=78,gapY=52,startX=(W-(cols-1)*gapX)/2;for(let r=0;r<rows;r++)for(let q=0;q<cols;q++)aliens.push({x:startX+q*gapX-22,y:95+r*gapY,w:44,h:28,alive:true});alienDir=1}
function shoot(){const now=performance.now();if(paused||over||now-lastShot<240)return;lastShot=now;bullets.push({x:ship.x+ship.w/2-3,y:ship.y,w:6,h:15,v:-10})}
function enemyShoot(){const a=aliens.filter(a=>a.alive);if(!a.length)return;const q=a[Math.floor(Math.random()*a.length)];enemyBullets.push({x:q.x+q.w/2-3,y:q.y+q.h,w:6,h:13,v:4+level*.25})}
function hit(a,b){return a.x<b.x+b.w&&a.x+a.w>b.x&&a.y<b.y+b.h&&a.y+a.h>b.y}
function end(){if(over)return;over=true;paused=true;parent.postMessage({type:'gamehub:gameover',score},'*')}
function reset(){score=0;lives=3;level=1;scoreEl.textContent=score;livesEl.textContent=lives;levelEl.textContent=level;bullets=[];enemyBullets=[];ship.x=W/2-32;over=false;paused=false;formation()}
function update(t){
 if(paused)return;
 if(keys.ArrowLeft||keys.a)ship.x-=ship.speed;if(keys.ArrowRight||keys.d)ship.x+=ship.speed;ship.x=Math.max(35,Math.min(W-35-ship.w,ship.x));
 bullets.forEach(b=>b.y+=b.v);enemyBullets.forEach(b=>b.y+=b.v);bullets=bullets.filter(b=>b.y>-30);enemyBullets=enemyBullets.filter(b=>b.y<H+30);
 let edge=false;const speed=.7+level*.14;aliens.filter(a=>a.alive).forEach(a=>{a.x+=alienDir*speed;if(a.x<35||a.x+a.w>W-35)edge=true});if(edge){alienDir*=-1;aliens.filter(a=>a.alive).forEach(a=>a.y+=18)}
 bullets.forEach(b=>aliens.forEach(a=>{if(a.alive&&hit(b,a)){a.alive=false;b.y=-100;score+=10;scoreEl.textContent=score}}));
 enemyBullets.forEach(b=>{if(hit(b,ship)){b.y=H+100;lives--;livesEl.textContent=lives;if(lives<=0)end()}});
 if(aliens.some(a=>a.alive&&a.y+a.h>=ship.y))end();
 if(!aliens.some(a=>a.alive)){level++;levelEl.textContent=level;bullets=[];enemyBullets=[];formation()}
 if(t-lastEnemyShot>Math.max(450,1100-level*70)){lastEnemyShot=t;enemyShoot()}
}
function draw(){
 g.clearRect(0,0,W,H);g.fillStyle='#fff';stars.forEach(s=>{g.globalAlpha=.25+s.s/3;g.fillRect(s.x,s.y,s.s,s.s)});g.globalAlpha=1;
 // ship
 g.fillStyle='#40d9ff';g.beginPath();g.moveTo(ship.x+ship.w/2,ship.y);g.lineTo(ship.x+ship.w,ship.y+ship.h);g.lineTo(ship.x,ship.y+ship.h);g.closePath();g.fill();g.fillStyle='#fff';g.fillRect(ship.x+ship.w/2-4,ship.y+10,8,15);
 aliens.filter(a=>a.alive).forEach(a=>{g.fillStyle='#a855f7';g.fillRect(a.x+5,a.y,a.w-10,a.h);g.fillRect(a.x,a.y+7,a.w,a.h-14);g.fillStyle='#fff';g.fillRect(a.x+10,a.y+8,5,5);g.fillRect(a.x+a.w-15,a.y+8,5,5)});
 g.fillStyle='#59f0ff';bullets.forEach(b=>g.fillRect(b.x,b.y,b.w,b.h));g.fillStyle='#ff4e73';enemyBullets.forEach(b=>g.fillRect(b.x,b.y,b.w,b.h));
}
function loop(t){update(t);draw();requestAnimationFrame(loop)}
addEventListener('keydown',e=>{keys[e.key]=true;if(e.code==='Space'){e.preventDefault();shoot()}});
addEventListener('keyup',e=>keys[e.key]=false);
c.addEventListener('pointerdown',e=>{const r=c.getBoundingClientRect(),px=(e.clientX-r.left)*W/r.width;if(px<ship.x)keys.ArrowLeft=true;else if(px>ship.x+ship.w)keys.ArrowRight=true;shoot();setTimeout(()=>{keys.ArrowLeft=false;keys.ArrowRight=false},120)});
addEventListener('message',e=>{if(!e.data)return;if(e.data.type==='gamehub:pause')paused=true;if(e.data.type==='gamehub:continue'){if(over)reset();else paused=false}if(e.data.type==='gamehub:restart')reset()});
formation();requestAnimationFrame(loop);