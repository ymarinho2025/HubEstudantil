const c=document.getElementById('game'),x=c.getContext('2d'),scoreEl=document.getElementById('score'),over=document.getElementById('over');
const S=30,STEP=145;
const fresh=()=>[{x:60,y:300},{x:90,y:300},{x:120,y:300},{x:150,y:300},{x:180,y:300},{x:210,y:300},{x:240,y:300},{x:270,y:300}];
let snake=fresh(),dir='right',queued='right',food={x:420,y:300},score=0,dead=false,paused=false,sent=false,last=0;
const opposite={right:'left',left:'right',up:'down',down:'up'};
function foodPos(){do{food={x:Math.floor(Math.random()*20)*S,y:Math.floor(Math.random()*20)*S}}while(snake.some(q=>q.x===food.x&&q.y===food.y))}
function turn(d){if(dead||paused||d===opposite[dir])return;queued=d}
function step(){if(dead||paused)return;dir=queued;const h=snake.at(-1),n={x:h.x,y:h.y};if(dir==='right')n.x+=S;if(dir==='left')n.x-=S;if(dir==='up')n.y-=S;if(dir==='down')n.y+=S;
if(n.x<0||n.y<0||n.x>=600||n.y>=600||snake.some(q=>q.x===n.x&&q.y===n.y)){die();return}snake.push(n);if(n.x===food.x&&n.y===food.y){score+=10;scoreEl.textContent=score;foodPos()}else snake.shift()}
function die(){if(dead)return;dead=true;over.style.display='flex';if(!sent){sent=true;parent.postMessage({type:'gamehub:gameover',score},'*')}}
function reset(){snake=fresh();dir='right';queued='right';score=0;scoreEl.textContent='0';dead=false;paused=false;sent=false;over.style.display='none';foodPos();last=performance.now()}
function draw(){x.fillStyle='#050807';x.fillRect(0,0,600,600);x.strokeStyle='#122018';for(let i=0;i<=600;i+=S){x.beginPath();x.moveTo(i,0);x.lineTo(i,600);x.stroke();x.beginPath();x.moveTo(0,i);x.lineTo(600,i);x.stroke()}x.fillStyle='#ef3038';x.fillRect(food.x+2,food.y+2,S-4,S-4);snake.forEach((q,i)=>{x.fillStyle=i===snake.length-1?'#159447':'#20c568';x.fillRect(q.x+1,q.y+1,S-2,S-2)})}
function loop(t){if(t-last>=STEP){step();last=t}draw();requestAnimationFrame(loop)}
addEventListener('keydown',e=>{const m={ArrowRight:'right',ArrowLeft:'left',ArrowUp:'up',ArrowDown:'down'};if(m[e.key]){e.preventDefault();turn(m[e.key])}});
c.addEventListener('pointerdown',e=>{const r=c.getBoundingClientRect(),px=(e.clientX-r.left)*600/r.width,py=(e.clientY-r.top)*600/r.height,h=snake.at(-1),dx=px-h.x,dy=py-h.y;turn(Math.abs(dx)>Math.abs(dy)?(dx>0?'right':'left'):(dy>0?'down':'up'))});
addEventListener('message',e=>{if(!e.data)return;if(e.data.type==='gamehub:pause')paused=true;if(e.data.type==='gamehub:continue'){if(dead)reset();else paused=false}if(e.data.type==='gamehub:restart')reset()});
foodPos();requestAnimationFrame(loop);