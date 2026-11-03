# CM 2.0 Dashboard (Crazy Moon)
PHP 8 + MySQL, mobile-first. Admin signs in; team uses a secret link (no sign-in).

## Run locally
    cp config.local.sample.php config.php
    php -S localhost:8000
Open http://localhost:8000/install.php (install key `local`), create the admin, then delete nothing locally.
Team link locally: http://localhost:8000/index.php?k=local-team-key-123

## Deploy to Hostinger (Git)
1. Push this repo to GitHub.
2. hPanel > Advanced > Git: add the repo, set install path (e.g. `public_html/cm2`), deploy. Enable auto-deploy (webhook) if wanted.
3. hPanel > Databases: create MySQL DB + user.
4. In File Manager create `cm2/config.php` from `config.sample.php` (DB creds, long random `install_key` and `team_key`). It is git-ignored, so deploys never overwrite it.
5. Visit `/cm2/install.php`, create the admin, then delete `install.php` on the server.
6. Tap Team to add members and copy the team link.

Schema changes: edit `schema.sql` and run the new SQL once in phpMyAdmin (install.php only runs on an empty database).
