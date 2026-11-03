<?php
require __DIR__.'/lib.php';
$c=cfg(); $msg='';
$done=false;
try{ $done=(int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn()>0; }catch(Throwable $e){}
if($done){ exit('Already installed. Delete install.php from the server.'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$c['install_key'],(string)($_POST['key']??'')) || $c['install_key']==='CHANGE_ME' || strlen((string)($c['team_key']??''))<12){ $msg='Wrong install key, or team_key in config.php is missing/too short (12+ characters).'; }
  elseif(strlen($_POST['pass']??'')<10){ $msg='Password needs 10+ characters.'; }
  else{
    $pdo=db(); $sqlite=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite';
    $ddl=file_get_contents(__DIR__.'/schema.sql');
    if($sqlite){ $ddl=str_replace('INT AUTO_INCREMENT PRIMARY KEY','INTEGER PRIMARY KEY AUTOINCREMENT',$ddl); $ddl=preg_replace('/,\s*INDEX \([^)]*\)(, INDEX \([^)]*\))*/','',$ddl); }
    foreach(array_filter(array_map('trim',explode(';',$ddl))) as $q) $pdo->exec($q);
    $now=date('Y-m-d H:i:s');
    $pdo->prepare('INSERT INTO users(name,email,pass_hash,role) VALUES(?,?,?,?)')->execute([trim($_POST['name']),strtolower(trim($_POST['email'])),password_hash($_POST['pass'],PASSWORD_DEFAULT),'admin']);
    $ws=[['Brewery relocation & permit','Move quote, permit, location commitment','primary'],
         ['Construction & investors','Build progress and investor communication','primary'],
         ['Zerezotla spaces & PR','Tenant outreach, paid campaign, PR plan','primary'],
         ['Product & distribution','Gin trials, distributor/importer search, brand','secondary']];
    $i=0; foreach($ws as $w){ $pdo->prepare('INSERT INTO workstreams(name,blurb,tier,sort) VALUES(?,?,?,?)')->execute([$w[0],$w[1],$w[2],$i++]); }
    // [workstream, type, title, status, notes]
    $seed=[
     [1,'task','Mario to get brewery relocation quote from Ismael (~4.5 km move)','done','Requested 2026-11-02'],
     [1,'task','Mario to open permit conversation with Max','done','Requested 2026-11-02; awaiting outcome'],
     [1,'blocker','Permit outcome unknown','blocked','Paty has been warned the permit could cause a delay. Nothing more to do until Mario/Max report back.'],
     [1,'decision','Clarify our commitment to the result location if we do not obtain the permit','todo','On hold until sit-down with Paty and Agustin'],
     [1,'milestone','Permit decision received','todo',''],
     [1,'milestone','Sit-down with Paty and Agustin','todo','After Mario/Max update'],
     [2,'task','Plan construction update post for investors','todo',''],
     [2,'milestone','Investor construction update published','todo',''],
     [3,'task','Build campaign to market the remaining Zerezotla spaces','todo','Paty approved the plan 2026-11-03'],
     [3,'decision','Approve paid campaign (needs Alma)','todo','John suggests paid'],
     [3,'task','Receive PR campaign and budget from Diego','doing','Prompted 2026-11-03'],
     [3,'milestone','Zerezotla campaign live','todo',''],
     [4,'task','Order remaining ingredients for Gin Trials','todo',''],
     [4,'task','Schedule "importer wanted" posts (LinkedIn first)','doing','Brainstorming in ChatGPT; migrate to Trillion/Claude'],
     [4,'task','Complete Cinco de Mayo brand paperwork with Alma','todo',''],
     [4,'milestone','Gin trials complete','todo',''],
    ];
    foreach($seed as $s){ $pdo->prepare('INSERT INTO items(workstream_id,type,title,notes,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?)')->execute([$s[0],$s[1],$s[2],$s[4],$s[3],$now,$now]); }
    exit('Installed. <b>Delete install.php now</b>, then <a href="login.php">sign in</a>.');
  }
}
?><!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><body style="font:16px system-ui;max-width:420px;margin:24px auto;padding:0 16px">
<h2>Install CM 2.0 dashboard</h2><p style="color:#b00"><?=h($msg)?></p>
<form method=post style="display:grid;gap:10px">
<input name=key placeholder="install_key from config.php" required>
<input name=name placeholder="Your name" required>
<input name=email type=email placeholder="Email" required>
<input name=pass type=password placeholder="Password (10+ chars)" required>
<button style="padding:12px">Install</button></form>
