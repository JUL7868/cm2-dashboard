<?php require __DIR__.'/lib.php';
if(isset($_GET['k']) && !empty(cfg()['team_key']) && hash_equals((string)cfg()['team_key'],(string)$_GET['k'])){ set_ck('cm2_team',team_token()); header('Location: index.php'); exit; }
if(!me() && team_ok()){
  if(isset($_POST['who'])){ $s=db()->prepare("SELECT id FROM users WHERE id=? AND role='member'"); $s->execute([(int)$_POST['who']]); if($s->fetch()){ set_ck('cm2_uid',(string)(int)$_POST['who']); header('Location: index.php'); exit; } }
  $m=db()->query("SELECT id,name FROM users WHERE role='member' ORDER BY name")->fetchAll(); ?>
<!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><title>CM 2.0</title>
<body style="font:18px system-ui;background:#14110f;color:#f3ece2;padding:24px;max-width:420px;margin:auto"><h2>Who are you?</h2>
<form method=post style="display:grid;gap:10px"><?php foreach($m as $r){ ?><button name=who value="<?=(int)$r['id']?>" style="font:inherit;padding:16px;border-radius:12px;border:1px solid #3a322b;background:#1f1a16;color:inherit"><?=h($r['name'])?></button><?php } if(!$m) echo 'No team members yet. Ask John to add you.'; ?></form></body><?php exit; }
if(!me()){ header('Location: login.php'); exit; } ?>
<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#14110f">
<title>CM 2.0 Dashboard</title>
<style>
:root{--bg:#14110f;--card:#1e1915;--card2:#272019;--line:#3a322b;--tx:#f3ece2;--mut:#a39786;--acc:#e9b44c;--ok:#6fcf8f;--warn:#f0a35a;--bad:#ff7a6b;--doing:#6cb4ff}
@media(prefers-color-scheme:light){:root{--bg:#f6f1e9;--card:#fff;--card2:#f1eadf;--line:#ddd2c1;--tx:#2a221a;--mut:#7a6d5c;--acc:#b9770e;--ok:#2e9b57;--warn:#c9742a;--bad:#d4412f;--doing:#2b79c9}}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--tx);font:16px/1.4 system-ui,-apple-system,sans-serif;padding:0 16px calc(90px + env(safe-area-inset-bottom))}
header{display:flex;align-items:center;justify-content:space-between;padding:calc(14px + env(safe-area-inset-top)) 0 10px}
h1{font-size:20px;margin:0}h2{font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);margin:22px 0 8px}
button{font:inherit;color:inherit;cursor:pointer}.ghost{background:none;border:1px solid var(--line);border-radius:10px;padding:8px 12px}
.sum{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:12px;text-align:center}
.stat b{display:block;font-size:24px}.stat span{font-size:12px;color:var(--mut)}.stat.bad b{color:var(--bad)}.stat.warn b{color:var(--warn)}
.big{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px;margin-bottom:8px}
.bar{height:8px;background:var(--card2);border-radius:99px;overflow:hidden}.bar i{display:block;height:100%;background:var(--acc)}
.chips{display:flex;gap:8px;margin:12px 0}.chip{border:1px solid var(--line);background:none;border-radius:99px;padding:6px 14px;font-size:14px}.chip.on{background:var(--acc);color:#14110f;border-color:var(--acc);font-weight:600}
.ws{background:var(--card);border:1px solid var(--line);border-radius:16px;margin-bottom:12px;overflow:hidden}
.ws.sec{border-style:dashed}.wsh{width:100%;text-align:left;background:none;border:0;padding:14px}
.wsh .t{display:flex;justify-content:space-between;gap:8px;font-weight:700}.wsh .m{color:var(--mut);font-size:13px;margin:4px 0 8px}
.body{display:none;padding:0 14px 14px}.ws.open .body{display:block}.sub{font-size:12px;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin:14px 0 4px}
.row{display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-top:1px solid var(--line)}
.dot{flex:none;width:30px;height:30px;border-radius:50%;border:2px solid var(--mut);background:none;font-size:14px;line-height:1;padding:0}
.dot.doing{border-color:var(--doing);color:var(--doing)}.dot.blocked{border-color:var(--bad);color:var(--bad)}.dot.done{background:var(--ok);border-color:var(--ok);color:#14110f}
.txt{flex:1;min-width:0;cursor:pointer}.txt .n{word-wrap:break-word}.done-t .n{text-decoration:line-through;color:var(--mut)}
.meta{font-size:12px;color:var(--mut);margin-top:2px}.late{color:var(--bad);font-weight:600}
.tag{display:inline-block;font-size:11px;padding:1px 7px;border-radius:99px;border:1px solid var(--line);margin-right:4px}
.add{width:100%;margin-top:10px;border:1px dashed var(--line);background:none;border-radius:10px;padding:10px;color:var(--acc)}
.att .row:first-child{border-top:0}.empty{color:var(--mut);font-size:14px;padding:6px 0}
dialog{width:min(480px,94vw);max-height:92vh;border:1px solid var(--line);border-radius:16px;background:var(--card);color:var(--tx);padding:16px;margin:auto}
dialog::backdrop{background:#000a}dialog form{display:grid;gap:10px}label{font-size:12px;color:var(--mut);display:grid;gap:4px}
input,select,textarea{font:inherit;color:inherit;background:var(--card2);border:1px solid var(--line);border-radius:10px;padding:11px;width:100%}
.two{display:grid;grid-template-columns:1fr 1fr;gap:10px}.btns{display:flex;gap:8px}.btns button{flex:1;padding:12px;border-radius:10px;border:1px solid var(--line);background:none}
.btns .pri{background:var(--acc);color:#14110f;border:0;font-weight:700}.btns .del{color:var(--bad)}
.log div{font-size:13px;color:var(--mut);padding:4px 0}.fab{position:fixed;right:16px;bottom:calc(16px + env(safe-area-inset-bottom));width:56px;height:56px;border-radius:50%;border:0;background:var(--acc);color:#14110f;font-size:30px;box-shadow:0 4px 14px #0007}
.tl{border-left:2px solid var(--line);margin-left:6px;padding-left:14px}.tl .row{border:0;padding:6px 0}
</style></head><body>
<header><h1>Crazy Moon 2.0</h1><div><button class="ghost" id="langBtn">EN</button> <button class="ghost" id="teamBtn" hidden>Equipo</button> <a class="ghost" id="outBtn" style="text-decoration:none;color:inherit;display:inline-block" href="logout.php">Salir</a></div></header>
<div id="app">…</div>
<button class="fab" id="fab" aria-label="Add">+</button>
<dialog id="dlg"><form id="f"></form></dialog>
<dialog id="team"><form method="dialog" id="tf"></form></dialog>
<script>
const I18={
 es:{loading:'Cargando…',overall:'Avance general',msdone:'hitos y tareas completados',target:'Meta',daysleft:'días restantes',dayspast:'días de retraso',overdue:'Vencidos',blocked:'Bloqueados',decisions:'Decisiones',everyone:'Todos',mine:'Mías',attention:'Requiere atención',nothing_urgent:'Nada urgente.',workstreams:'Frentes de trabajo',also:'También en curso',timeline:'Línea de tiempo de hitos',no_ms:'Aún no hay hitos.',activity:'Actividad reciente',none_yet:'Aún nada.',nothing_here:'Nada aquí.',add_to:'+ Agregar a',edit_item:'Editar elemento',new_item:'Nuevo elemento',title:'Título',type:'Tipo',status:'Estado',owner:'Responsable',due:'Fecha límite',workstream:'Frente',priority:'Prioridad',high:'Alta',normal:'Normal',low:'Baja',waits:'Depende de',notes:'Notas',cancel:'Cancelar',del:'Eliminar',save:'Guardar',confirm_del:'¿Eliminar este elemento?',no_date:'Sin fecha',waits_on:'espera a',overdue_pre:'Vencido ',team:'Equipo',out:'Salir',team_link:'Enlace del equipo (sin iniciar sesión)',team_hint:'Envía este enlace a tu equipo. Cada quien elige su nombre una vez.',add_member:'Agregar miembro',name:'Nombre',close:'Cerrar',add:'Agregar',admin:'admin',
  milestone:'Hito',task:'Tarea',risk:'Riesgo',blocker:'Bloqueo',decision:'Decisión',milestones:'Hitos',tasks:'Tareas',risks:'Riesgos',blockers:'Bloqueos',decisionsH:'Decisiones',todo:'Pendiente',doing:'En curso',blocked_s:'Bloqueado',done:'Hecho',change_status:'Cambiar estado',err:'Error'},
 en:{loading:'Loading…',overall:'Overall progress',msdone:'milestones & tasks done',target:'Target',daysleft:'days left',dayspast:'days past',overdue:'Overdue',blocked:'Blocked',decisions:'Decisions',everyone:'Everyone',mine:'Mine',attention:'Needs attention',nothing_urgent:'Nothing urgent.',workstreams:'Workstreams',also:'Also in play',timeline:'Milestone timeline',no_ms:'No milestones yet.',activity:'Recent activity',none_yet:'None yet.',nothing_here:'Nothing here.',add_to:'+ Add to',edit_item:'Edit item',new_item:'New item',title:'Title',type:'Type',status:'Status',owner:'Owner',due:'Due',workstream:'Workstream',priority:'Priority',high:'High',normal:'Normal',low:'Low',waits:'Waits on',notes:'Notes',cancel:'Cancel',del:'Delete',save:'Save',confirm_del:'Delete this item?',no_date:'No date',waits_on:'waits on',overdue_pre:'Overdue ',team:'Team',out:'Out',team_link:'Team link (no sign-in)',team_hint:'Send this link to your team. They pick their name once.',add_member:'Add a member',name:'Name',close:'Close',add:'Add',admin:'admin',
  milestone:'Milestone',task:'Task',risk:'Risk',blocker:'Blocker',decision:'Decision',milestones:'Milestones',tasks:'Tasks',risks:'Risks',blockers:'Blockers',decisionsH:'Decisions',todo:'To do',doing:'Doing',blocked_s:'Blocked',done:'Done',change_status:'Change status',err:'Error'}};
let LANG='es';try{LANG=localStorage.getItem('cm2lang')||(/^en/i.test(navigator.language)?'en':'es')}catch(_){LANG='es'}
const t=k=>(I18[LANG]||I18.es)[k]||k;
const STAT_L=s=>t(s==='blocked'?'blocked_s':s);
let D=null,filter='all',openWs=[];try{openWs=JSON.parse(sessionStorage.getItem('openWs')||'[]')}catch(_){}
const $=s=>document.querySelector(s),esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const ST=['todo','doing','done'],ICON={todo:'',doing:'◐',blocked:'!',done:'✓'},TYPES=['milestone','task','blocker','risk','decision'],PLUR={milestone:'milestones',task:'tasks',blocker:'blockers',risk:'risks',decision:'decisionsH'};
function setLang(l){LANG=l;try{localStorage.setItem('cm2lang',l)}catch(_){}document.documentElement.lang=l;chrome();if(D)render()}
function chrome(){$('#teamBtn').textContent=t('team');$('#outBtn').textContent=t('out');$('#langBtn').textContent=LANG==='es'?'EN':'ES';if(!D)$('#app').textContent=t('loading')}
async function load(){const r=await fetch('api.php');if(r.status===401)return location='login.php';D=await r.json();render()}
async function post(o){o.csrf=D.csrf;const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(o)});const j=await r.json();if(!r.ok){alert(j.error||t('err'));return false}return true}
const uname=id=>(D.users.find(u=>u.id==id)||{}).name||'';
const late=i=>i.due_date&&i.status!=='done'&&i.due_date<D.today;
const act=i=>i.type==='milestone'||i.type==='task';
function fmt(d){if(!d)return'';const [y,m,dd]=d.split('-');return new Date(y,m-1,dd).toLocaleDateString(LANG==='es'?'es-MX':'en-US',{month:'short',day:'numeric'})}
function vis(i){return filter==='all'||i.owner_id==D.me.id}
function rowH(i){const dep=i.depends_on?D.items.find(x=>x.id==i.depends_on):null;
 return `<div class="row ${i.status==='done'?'done-t':''}"><button class="dot ${i.status}" data-s="${i.id}" aria-label="${t('change_status')}">${ICON[i.status]}</button>
 <div class="txt" data-e="${i.id}"><div class="n">${esc(i.title)}</div><div class="meta">${i.owner_id?esc(uname(i.owner_id))+' · ':''}${i.due_date?`<span class="${late(i)?'late':''}">${late(i)?t('overdue_pre'):''}${fmt(i.due_date)}</span> · `:''}${i.status==='blocked'?`<span class="late">${STAT_L('blocked')}</span> · `:''}${dep&&dep.status!=='done'?'⛓ '+t('waits_on')+' '+esc(dep.title.slice(0,30)):''}${i.notes?esc(i.notes.slice(0,80)):''}</div></div></div>`}
function prog(items){const a=items.filter(act);const d=a.filter(i=>i.status==='done').length;return{d,t:a.length,p:a.length?Math.round(100*d/a.length):0}}
const wsName=id=>esc((D.ws.find(w=>w.id==id)||{}).name);
function render(){
 const all=D.items,A=all.filter(act),done=A.filter(i=>i.status==='done').length,pct=A.length?Math.round(100*done/A.length):0;
 const over=all.filter(late),blk=all.filter(i=>i.status==='blocked'||(i.type==='blocker'&&i.status!=='done')),dec=all.filter(i=>i.type==='decision'&&i.status!=='done');
 let tgt='';if(D.target){const n=Math.ceil((new Date(D.target)-new Date(D.today))/864e5);tgt=`<div class="meta">${t('target')} ${fmt(D.target)} · ${n>=0?n+' '+t('daysleft'):Math.abs(n)+' '+t('dayspast')}</div>`}
 const att=[...new Map([...over,...blk,...dec].map(i=>[i.id,i])).values()].filter(vis).slice(0,8);
 let h=`<div class="big"><div style="display:flex;justify-content:space-between"><b>${t('overall')}</b><b>${pct}%</b></div><div class="bar"><i style="width:${pct}%"></i></div><div class="meta">${done} / ${A.length} ${t('msdone')}</div>${tgt}</div>
 <div class="sum"><div class="stat ${over.length?'bad':''}"><b>${over.length}</b><span>${t('overdue')}</span></div><div class="stat ${blk.length?'warn':''}"><b>${blk.length}</b><span>${t('blocked')}</span></div><div class="stat"><b>${dec.length}</b><span>${t('decisions')}</span></div></div>
 <div class="chips"><button class="chip ${filter==='all'?'on':''}" data-f="all">${t('everyone')}</button><button class="chip ${filter==='mine'?'on':''}" data-f="mine">${t('mine')}</button></div>
 <h2>${t('attention')}</h2><div class="big att">${att.length?att.map(i=>`<div class="meta" style="margin:8px 0 -6px">${wsName(i.workstream_id)} · <span class="tag">${t(i.type)}</span></div>`+rowH(i)).join(''):`<div class="empty">${t('nothing_urgent')}</div>`}</div>`;
 const prim=D.ws.filter(w=>w.tier==='primary'),sec=D.ws.filter(w=>w.tier!=='primary');
 h+=`<h2>${t('workstreams')}</h2>`+prim.map(wsH).join('');
 if(sec.length)h+=`<h2>${t('also')}</h2>`+sec.map(wsH).join('');
 const ms=all.filter(i=>i.type==='milestone').sort((a,b)=>(a.due_date||'9999').localeCompare(b.due_date||'9999'));
 h+=`<h2>${t('timeline')}</h2><div class="big tl">${ms.map(i=>`<div class="meta" style="margin-top:6px">${i.due_date?fmt(i.due_date):t('no_date')} · ${wsName(i.workstream_id)}</div>`+rowH(i)).join('')||`<div class="empty">${t('no_ms')}</div>`}</div>`;
 h+=`<h2>${t('activity')}</h2><div class="big log">${D.activity.map(a=>`<div>${esc(a.name||'')} · ${esc(a.what)} <span style="opacity:.6">${esc(a.at.slice(5,16))}</span></div>`).join('')||`<div class="empty">${t('none_yet')}</div>`}</div>`;
 $('#app').innerHTML=h;$('#teamBtn').hidden=D.me.role!=='admin';$('#outBtn').hidden=D.me.role!=='admin'}
function wsH(w){const its=D.items.filter(i=>i.workstream_id==w.id),p=prog(its),open=openWs.includes(w.id);
 const nxt=its.filter(i=>i.type==='milestone'&&i.status!=='done').sort((a,b)=>(a.due_date||'9999').localeCompare(b.due_date||'9999'))[0];
 const sect=TYPES.map(ty=>{const l=its.filter(i=>i.type===ty&&vis(i));return l.length?`<div class="sub">${t(PLUR[ty])}</div>`+l.map(rowH).join(''):''}).join('')||`<div class="empty">${t('nothing_here')}</div>`;
 return `<div class="ws ${w.tier!=='primary'?'sec':''} ${open?'open':''}"><button class="wsh" data-w="${w.id}"><div class="t"><span>${esc(w.name)}</span><span>${p.p}%</span></div><div class="m">${esc(w.blurb)}${nxt?' · '+esc(nxt.title.slice(0,40)):''}</div><div class="bar"><i style="width:${p.p}%"></i></div></button>
 <div class="body">${sect}<button class="add" data-add="${w.id}">${t('add_to')} ${esc(w.name)}</button></div></div>`}
function edit(id,wsid){const i=id?D.items.find(x=>x.id==id):{workstream_id:wsid||(D.ws[0]||{}).id,type:'task',status:'todo',priority:2};
 const o=(arr,v,f)=>arr.map(x=>`<option value="${f?x.id:x}" ${(f?x.id:x)==v?'selected':''}>${esc(f?x.name:x)}</option>`).join('');
 const ol=(arr,v,lab)=>arr.map(x=>`<option value="${x}" ${x==v?'selected':''}>${esc(lab(x))}</option>`).join('');
 $('#f').innerHTML=`<b>${id?t('edit_item'):t('new_item')}</b><label>${t('title')}<input name=title value="${esc(i.title)}" required maxlength=200></label>
 <div class=two><label>${t('type')}<select name=type>${ol(TYPES,i.type,x=>t(x))}</select></label><label>${t('status')}<select name=status>${ol(['todo','doing','blocked','done'],i.status,STAT_L)}</select></label></div>
 <div class=two><label>${t('owner')}<select name=owner_id><option value="">—</option>${o(D.users,i.owner_id,1)}</select></label><label>${t('due')}<input type=date name=due_date value="${i.due_date||''}"></label></div>
 <div class=two><label>${t('workstream')}<select name=workstream_id>${o(D.ws,i.workstream_id,1)}</select></label><label>${t('priority')}<select name=priority>${[1,2,3].map(n=>`<option value=${n} ${n==i.priority?'selected':''}>${t(['high','normal','low'][n-1])}</option>`).join('')}</select></label></div>
 <label>${t('waits')}<select name=depends_on><option value="">—</option>${D.items.filter(x=>x.id!=id).map(x=>`<option value=${x.id} ${x.id==i.depends_on?'selected':''}>${esc(x.title.slice(0,50))}</option>`).join('')}</select></label>
 <label>${t('notes')}<textarea name=notes rows=3>${esc(i.notes)}</textarea></label>
 <div class=btns><button type=button id=cx>${t('cancel')}</button>${id&&D.me.role==='admin'?`<button type=button class=del id=dl>${t('del')}</button>`:''}<button class=pri>${t('save')}</button></div>`;
 $('#dlg').showModal();$('#cx').onclick=()=>$('#dlg').close();
 if($('#dl'))$('#dl').onclick=async()=>{if(confirm(t('confirm_del'))&&await post({action:'delete_item',id})){$('#dlg').close();load()}};
 $('#f').onsubmit=async e=>{e.preventDefault();const o=Object.fromEntries(new FormData(e.target));o.action='save_item';o.id=id||0;if(await post(o)){$('#dlg').close();load()}}}
document.addEventListener('click',async e=>{const tg=e.target.closest('[data-s],[data-e],[data-w],[data-add],[data-f]');if(!tg)return;
 if(tg.dataset.s){const i=D.items.find(x=>x.id==tg.dataset.s);const n=i.status==='blocked'?'todo':ST[(ST.indexOf(i.status)+1)%3];if(await post({action:'set_status',id:i.id,status:n}))load()}
 else if(tg.dataset.e)edit(tg.dataset.e);
 else if(tg.dataset.add)edit(0,+tg.dataset.add);
 else if(tg.dataset.f){filter=tg.dataset.f;render()}
 else if(tg.dataset.w){const w=+tg.dataset.w;openWs=openWs.includes(w)?openWs.filter(x=>x!==w):[...openWs,w];try{sessionStorage.setItem('openWs',JSON.stringify(openWs))}catch(_){}tg.closest('.ws').classList.toggle('open')}});
$('#fab').onclick=()=>edit(0);
$('#langBtn').onclick=()=>setLang(LANG==='es'?'en':'es');
$('#teamBtn').onclick=()=>{$('#tf').innerHTML=`<b>${t('team')}</b><div class=meta>${D.users.map(u=>esc(u.name)+(u.role==='admin'?' ('+t('admin')+')':'')).join(', ')}</div><b style="margin-top:8px">${t('team_link')}</b><input readonly value="${esc(D.team_link)}" onclick="this.select()"><div class=meta>${t('team_hint')}</div><b style="margin-top:8px">${t('add_member')}</b><input id=un placeholder="${t('name')}"><div class=btns><button value=x>${t('close')}</button><button type=button class=pri id=ua>${t('add')}</button></div>`;
 $('#team').showModal();$('#ua').onclick=async()=>{if(await post({action:'add_user',name:$('#un').value})){$('#team').close();load()}}};
document.documentElement.lang=LANG;chrome();load();
</script></body></html>
