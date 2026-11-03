<?php
require __DIR__.'/lib.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $_SESSION['tries']=($_SESSION['tries']??0)+1;
  if($_SESSION['tries']>8){ $err='Too many attempts. Try later.'; }
  else{
    $s=db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([strtolower(trim($_POST['email']??''))]); $u=$s->fetch();
    if($u && password_verify($_POST['pass']??'',$u['pass_hash'])){ session_regenerate_id(true); $_SESSION['uid']=$u['id']; $_SESSION['tries']=0; header('Location: index.php'); exit; }
    $err='Wrong email or password.';
  }
}
?><!doctype html><html><head><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>CM 2.0</title>
<style>body{font:16px system-ui;background:#14110f;color:#f3ece2;display:grid;place-items:center;min-height:100vh;margin:0}
form{width:min(360px,90vw);display:grid;gap:12px}input,button{font:inherit;padding:14px;border-radius:10px;border:1px solid #3a322b;background:#1f1a16;color:inherit}
button{background:#e9b44c;color:#14110f;font-weight:700;border:0}.e{color:#ff8a7a}</style></head><body>
<form method=post><h2 style="margin:0">Crazy Moon 2.0</h2><div class=e><?=h($err)?></div>
<input name=email type=email placeholder=Email autocomplete=username required><input name=pass type=password placeholder=Password autocomplete=current-password required><button>Sign in</button></form></body></html>
