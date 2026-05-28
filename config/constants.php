<?php
// point d'entrée public/
define('BASE_URL', '');

// duree de vie de cookies
define('REMEMBER_ME_DURATION', 60 * 60 * 24 * 30);

// dotinsat_remember :hedha bch naamlou verification (double check)
define('COOKIE_REMEMBER_TOKEN', 'dotinsat_remember');
define('COOKIE_REMEMBER_USER',  'dotinsat_user_id');

// Rôles autorisés
define('ROLE_ETUDIANT',     'etudiant');
define('ROLE_PROFESSEUR',   'professeur');
define('ROLE_ADMIN',        'admin');

// lista mtaa les pages illi andou ll hak yhelhom menghir sans connection
define('PUBLIC_PAGES', ['login', 'do-login']);
