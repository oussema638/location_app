<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

session_start();

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once APP_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'helpers.php';
require_once APP_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}
$uri = '/' . trim($uri, '/');
if ($uri === '/') {
    $uri = '';
} else {
    $uri = trim($uri, '/');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$segments = $uri === '' ? [] : explode('/', $uri);
$route = $uri;

try {
    $auth = new AuthController();
    $equipement = new EquipementController();
    $location = new LocationController();

    switch (true) {
        case $route === '' || $route === 'home':
            $location->home();
            break;

        case $route === 'login':
            $auth->login();
            break;

        case $route === 'register':
            $auth->register();
            break;

        case $route === 'logout':
            $auth->logout();
            break;

        case $route === 'equipements':
            $equipement->catalogue();
            break;

        case preg_match('#^equipements/show/(\d+)$#', $route, $m) === 1:
            $equipement->show((int) $m[1]);
            break;

        case $route === 'mes-locations':
            $location->mesLocations();
            break;

        case preg_match('#^locations/create/(\d+)$#', $route, $m) === 1:
            $location->createForm((int) $m[1]);
            break;

        case $route === 'locations/store' && $method === 'POST':
            $location->store();
            break;

        case $route === 'locations/cancel' && $method === 'POST':
            $location->cancel();
            break;

        case preg_match('#^locations/pdf/(\d+)$#', $route, $m) === 1:
            $location->pdf((int) $m[1]);
            break;

        case $route === 'admin':
            $location->dashboard();
            break;

        case $route === 'admin/equipements':
            $equipement->adminIndex();
            break;

        // ── Admin equipment forms & actions (singular /equipement/) ──────────
        case $route === 'admin/equipement/add':
            $equipement->adminForm();
            break;

        case preg_match('#^admin/equipement/edit/(\d+)$#', $route, $m) === 1:
            $equipement->adminForm((int) $m[1]);
            break;

        case $route === 'admin/equipement/save' && $method === 'POST':
            $equipement->save();
            break;

        case $route === 'admin/equipement/delete' && $method === 'POST':
            $equipement->delete();
            break;

        // ── Legacy plural aliases (kept for backwards compatibility) ─────────
        case $route === 'admin/equipements/create':
            $equipement->adminForm();
            break;

        case preg_match('#^admin/equipements/edit/(\d+)$#', $route, $m) === 1:
            $equipement->adminForm((int) $m[1]);
            break;

        case $route === 'admin/equipements/save' && $method === 'POST':
            $equipement->save();
            break;

        case $route === 'admin/equipements/delete' && $method === 'POST':
            $equipement->delete();
            break;

        case $route === 'admin/categories':
            $equipement->categories();
            break;

        case $route === 'admin/locations':
            $location->adminIndex();
            break;

        case $route === 'admin/locations/update' && $method === 'POST':
            $location->updateStatut();
            break;

        case $route === 'admin/utilisateurs':
            $auth->utilisateurs();
            break;

        default:
            http_response_code(404);
            view('front/404');
            break;
    }
} catch (RuntimeException $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title></head><body>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
}
