<?php
require __DIR__.'/lib.php';
$u=me(); if(!$u) json_out(['error'=>'auth'],401);
$pdo=db(); $now=date('Y-m-d H:i:s');
$TYPES=['milestone','task','risk','blocker','decision']; $STAT=['todo','doing','blocked','done'];
if($_SERVER['REQUEST_METHOD']==='GET'){
  $tl=''; if($u['role']==='admin'){ $tl=(!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']),'/').'/index.php?k='.urlencode(cfg()['team_key']??''); }
  json_out([
    'team_link'=>$tl,'me'=>$u,'csrf'=>csrf(),'target'=>cfg()['target_date']??'','today'=>date('Y-m-d'),
    'users'=>$pdo->query('SELECT id,name,role FROM users ORDER BY name')->fetchAll(),
    'ws'=>$pdo->query('SELECT * FROM workstreams ORDER BY sort,id')->fetchAll(),
    'items'=>$pdo->query('SELECT * FROM items ORDER BY priority,due_date,id')->fetchAll(),
    'activity'=>$pdo->query('SELECT a.what,a.at,u.name FROM activity a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 15')->fetchAll(),
  ]);
}
$in=json_decode(file_get_contents('php://input'),true)?:[];
if(!hash_equals(csrf(),(string)($in['csrf']??''))) json_out(['error'=>'csrf'],403);
function log_act($pdo,$u,$id,$what){ $pdo->prepare('INSERT INTO activity(item_id,user_id,what,at) VALUES(?,?,?,?)')->execute([$id,$u['id'],mb_substr($what,0,250),date('Y-m-d H:i:s')]); }
function dt($v){ return preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$v)?$v:null; }
function nid($v){ return ($v===''||$v===null||$v===0||$v==='0')?null:(int)$v; }
$a=$in['action']??'';
if($a==='save_item'){
  $t=in_array($in['type']??'',$TYPES)?$in['type']:'task'; $s=in_array($in['status']??'',$STAT)?$in['status']:'todo';
  $title=trim((string)($in['title']??'')); if($title==='') json_out(['error'=>'title'],400);
  $title=mb_substr($title,0,200); $p=max(1,min(3,(int)($in['priority']??2)));
  $row=[(int)$in['workstream_id'],$t,$title,(string)($in['notes']??''),$s,nid($in['owner_id']??null),dt($in['due_date']??null),nid($in['depends_on']??null),$p];
  if(!empty($in['id'])){
    $pdo->prepare('UPDATE items SET workstream_id=?,type=?,title=?,notes=?,status=?,owner_id=?,due_date=?,depends_on=?,priority=?,updated_at=? WHERE id=?')->execute(array_merge($row,[$now,(int)$in['id']]));
    log_act($pdo,$u,$in['id'],"Edited: $title");
  } else {
    $pdo->prepare('INSERT INTO items(workstream_id,type,title,notes,status,owner_id,due_date,depends_on,priority,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute(array_merge($row,[$now,$now]));
    log_act($pdo,$u,$pdo->lastInsertId(),"Added $t: $title");
  }
  json_out(['ok'=>1]);
}
if($a==='set_status'){
  $s=in_array($in['status']??'',$STAT)?$in['status']:null; if(!$s) json_out(['error'=>'status'],400);
  $pdo->prepare('UPDATE items SET status=?,updated_at=? WHERE id=?')->execute([$s,$now,(int)$in['id']]);
  $t=$pdo->prepare('SELECT title FROM items WHERE id=?'); $t->execute([(int)$in['id']]);
  log_act($pdo,$u,$in['id'],"Marked $s: ".$t->fetchColumn()); json_out(['ok'=>1]);
}
if($a==='delete_item'){
  if($u['role']!=='admin') json_out(['error'=>'forbidden'],403);
  $pdo->prepare('DELETE FROM items WHERE id=?')->execute([(int)$in['id']]);
  $pdo->prepare('UPDATE items SET depends_on=NULL WHERE depends_on=?')->execute([(int)$in['id']]);
  log_act($pdo,$u,null,'Deleted item #'.(int)$in['id']); json_out(['ok'=>1]);
}
if($a==='add_user'){
  if($u['role']!=='admin') json_out(['error'=>'forbidden'],403);
  $n=mb_substr(trim((string)$in['name']),0,80); if($n==='') json_out(['error'=>'Name required'],400);
  $pdo->prepare('INSERT INTO users(name,email,pass_hash,role) VALUES(?,?,?,?)')->execute([$n,'member-'.bin2hex(random_bytes(6)).'@local','','member']);
  json_out(['ok'=>1]);
}
json_out(['error'=>'unknown'],400);
