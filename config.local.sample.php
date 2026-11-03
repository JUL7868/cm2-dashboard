<?php
// Local testing: copy to config.php. Uses a SQLite file, no MySQL needed.
return ['db_dsn'=>'sqlite:'.__DIR__.'/local.db','db_user'=>null,'db_pass'=>null,
  'target_date'=>'2026-12-01','team_key'=>'local-team-key-123','install_key'=>'local'];
