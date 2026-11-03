<?php
// Copy to config.php and fill in from Hostinger hPanel > Databases > MySQL.
return [
  'db_dsn'  => 'mysql:host=localhost;dbname=YOUR_DB;charset=utf8mb4',
  'db_user' => 'YOUR_DB_USER',
  'db_pass' => 'YOUR_DB_PASSWORD',
  'target_date' => '2026-12-01', // CM 2.0 target date, YYYY-MM-DD
  'team_key' => 'CHANGE_ME_LONG_RANDOM', // secret in the team link; team needs no sign-in
  'install_key' => 'CHANGE_ME', // required to run install.php; delete install.php after setup
];
