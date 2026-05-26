<?php
// Point d'entrée unique : public/
define('BASE_URL',  '/DOTInsat/public');


// Durée du cookie "Se souvenir de moi" : 30 jours
define('REMEMBER_ME_DURATION', 60 * 60 * 24 * 30);

// Nom des cookies
define('COOKIE_REMEMBER_TOKEN', 'dotinsat_remember');
define('COOKIE_REMEMBER_USER',  'dotinsat_user_id');

// Rôles autorisés
define('ROLE_ETUDIANT',     'etudiant');
define('ROLE_PROFESSEUR',   'professeur');
define('ROLE_ADMIN',        'admin');

// Routes publiques (pas de redirection vers login)
define('PUBLIC_PAGES', ['login', 'do-login']);
