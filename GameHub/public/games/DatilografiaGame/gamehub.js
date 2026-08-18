const phrases=[
"A prática constante transforma esforço em habilidade.",
"Aprender jogando torna cada desafio uma nova descoberta.",
"Velocidade sem precisão não vence uma boa corrida.",
"O conhecimento cresce quando a curiosidade encontra disciplina.",
"Programar é ensinar o computador a resolver problemas passo a passo.",
"Cada erro corrigido é uma oportunidade de aprender melhor.",
"A leitura amplia ideias e a escrita organiza o pensamento.",
"Persistência é continuar mesmo quando o resultado demora a aparecer."
];
let idx=0,score=0,startTime=0,totalTyped=0,totalCorrect=0,running=false,lockedByQuestion=false;
const $=id=>document.getElementById(id),input=$("input");
$("total").textContent=phrases.length;

function render(){
 const p=phrases[idx],v=input.value;let html="";
 for(let i=0;i<p.length;i++){const c=p[i],cl=i<v.length?(v[i]===c?"ok":"bad"):(i===v.length?"next":"");html+=`<span class="${cl}">${c===" "?"&nbsp;":c.replace(/&/g,"&amp;").replace(/</g,"&lt;")}</span>`}
 $("phrase").innerHTML=html;$("phase").textContent=idx+1;$("progress").style.width=(idx/phrases.length*100)+"%";
}
function stats(){const mins=Math.max((Date.now()-startTime)/60000,.01);$("wpm").textContent=Math.round(totalCorrect/5/mins);$("accuracy").textContent=(totalTyped?Math.round(totalCorrect/totalTyped*100):100)+"%";$("score").textContent=score}
function reset(){idx=0;score=0;totalTyped=0;totalCorrect=0;startTime=Date.now();running=true;lockedByQuestion=false;input.disabled=false;input.value="";$("message").textContent="Digite a frase.";render();stats();input.focus()}
function wordAt(text,pos){
 let start=pos;while(start>0&&text[start-1]!==" ")start--;
 let end=pos;while(end<text.length&&text[end]!==" ")end++;
 return {word:text.slice(start,end),start};
}
$("start").onclick=reset;
input.addEventListener("input",()=>{
 if(!running||lockedByQuestion)return;
 const p=phrases[idx],v=input.value;totalTyped++;
 const pos=v.length-1;
 if(pos>=0&&v[pos]!==p[pos]){
   const correct=wordAt(p,Math.max(0,pos));
   const previous=wordAt(p,Math.max(0,correct.start-2));
   input.value=p.slice(0,previous.start);
   lockedByQuestion=true;running=false;input.disabled=true;
   $("message").textContent=`Erro. Palavra correta: ${correct.word}`;
   render();stats();
   window.parent.postMessage({type:"gamehub:typing-error",expected:correct.word,previous:previous.word},"*");
   return;
 }
 if(pos>=0){totalCorrect++;score+=10}
 render();stats();
 if(v===p){
   score+=250;idx++;input.value="";
   if(idx>=phrases.length){idx=0;$("message").textContent="Nova volta iniciada.";startTime=Date.now()}
   else $("message").textContent="✓ Fase concluída!";
   render();stats();
 }
});
render();input.disabled=true;
window.addEventListener("message",e=>{
 if(!e.data)return;
 if(e.data.type==="gamehub:pause"){running=false;input.disabled=true}
 if(e.data.type==="gamehub:continue"){running=true;input.disabled=false;input.focus()}
 if(e.data.type==="gamehub:restart"){reset()}
});
