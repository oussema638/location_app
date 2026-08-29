<?php

define('APP_NAME', 'location_app');
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'public');

$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim($scriptName, '/');
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

define('BASE_PATH', $basePath);
define('BASE_URL', $basePath);

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'location_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('ROLES', ['client', 'agent', 'responsable']);
define('EQUIPEMENT_ETATS', ['disponible', 'en location', 'en maintenance', 'endommagé']);
define('LOCATION_STATUTS', ['en attente', 'confirmée', 'en cours', 'terminée', 'annulée']);
