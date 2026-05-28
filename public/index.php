<?php
// hedhi nektebha bch ken fi halet aandi fonction feha parametre int w ena dakhalt string yaatini erreur (ne cast pas)
declare(strict_types=1);

// bch najem nra les erreur aal ecran
ini_set('display_errors', '1');
error_reporting(E_ALL);

// hedhhi bch nssahel aala rohi nok3edch nekteb fll path ll koll 
// __DIR__ dossier mtaa ll fichier index php
//str_replace('\\', '/') = convertit les \ en / ll  Windows
//dirname(__DIR__) dossier parent
define('BASE_PATH', str_replace('\\', '/', dirname(__DIR__)));


require_once BASE_PATH . '/config/constants.php';

// session start ken mouch mawjouda nsetiw les parametre mta cookies
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,// par defaut nhotha des que nssaker le navigateur
        'path'     => '/',//valde sur tout le site
    ]);
    session_start();
}

// connection bd
require_once BASE_PATH . '/config/db_connect.php';


require_once BASE_PATH . '/app/Core/router.php';
// nlanci ll router
dispatch($routes, $pdo);