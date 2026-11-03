<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
session_start();
function cfg(){ static $c; if(!$c){ $f=__DIR__.'/config.php'; $c=is_file($f)?require $f:null; if(!$c){http_response_code(500);exit('Missing config.php');} } return $c; }
function db(){ static $p; if(!$p){ $c=cfg(); $p=new PDO($c['db_dsn'],$c['db_user']??null,$c['db_pass']??null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); } return $p; }
function team_token(){ return hash('sha256',(cfg()['team_key']??'').'|cm2'); }
function team_ok(){ return !empty(cfg()['team_key']) && isset($_COOKIE['cm2_team']) && hash_equals(team_token(),(string)$_COOKIE['cm2_team']); }
function set_ck($n,$v){ setcookie($n,$v,['expires'=>time()+86400*365,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]); $_COOKIE[$n]=$v; }
function me(){
  if(!empty($_SESSION['uid'])){ $s=db()->prepare('SELECT id,name,email,role FROM users WHERE id=?'); $s->execute([$_SESSION['uid']]); return $s->fetch()?:null; }
  if(team_ok() && !empty($_COOKIE['cm2_uid'])){ $s=db()->prepare("SELECT id,name,email,role FROM users WHERE id=? AND role='member'"); $s->execute([(int)$_COOKIE['cm2_uid']]); return $s->fetch()?:null; }
  return null; }
function csrf(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function h($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function json_out($d,$code=200){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($d); exit; }
