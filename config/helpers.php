<?php

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function view(string $path, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $file = APP_ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path) . '.php';
    if (!is_file($file)) {
        http_response_code(404);
        echo 'Vue introuvable.';
        return;
    }
    require $file;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function has_role(string ...$roles): bool
{
    $user = current_user();
    return $user !== null && in_array($user['role'], $roles, true);
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Veuillez vous connecter pour continuer.');
        redirect('login');
    }
}

function require_staff(): void
{
    require_login();
    if (!has_role('agent', 'responsable')) {
        flash('error', 'Accès réservé au personnel.');
        redirect('');
    }
}

function require_responsable(): void
{
    require_login();
    if (!has_role('responsable')) {
        flash('error', 'Accès réservé au responsable.');
        redirect('admin');
    }
}

function days_between(string $start, string $end): int
{
    $from = new DateTime($start);
    $to = new DateTime($end);
    $days = (int) $from->diff($to)->days;
    return max(1, $days);
}

function excerpt(?string $value, int $width = 110): string
{
    $value = (string) $value;
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($value, 0, $width, '…', 'UTF-8');
    }
    return strlen($value) > $width ? substr($value, 0, $width) . '…' : $value;
}
