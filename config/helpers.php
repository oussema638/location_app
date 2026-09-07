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

// ── Status / Etat display helpers ────────────────────────────────────────────

/**
 * Canonical ASCII key → human-readable French label.
 * Used wherever we need to show accented display text from an ASCII DB value.
 */
function statut_label(string $statut): string
{
    $labels = [
        'en attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'en cours'   => 'En cours',
        'terminee'   => 'Terminée',
        'annulee'    => 'Annulée',
    ];
    return $labels[$statut] ?? ucfirst($statut);
}

function etat_label(string $etat): string
{
    $labels = [
        'disponible'     => 'Disponible',
        'en location'    => 'En location',
        'en maintenance' => 'En maintenance',
        'endommage'      => 'Endommagé',
    ];
    return $labels[$etat] ?? ucfirst($etat);
}

/**
 * Strip accents + lowercase so any incoming variant maps to the ASCII key.
 * Handles mojibake, accented French, and mixed case.
 */
function normalize_status(string $raw): string
{
    $s = mb_strtolower(trim($raw), 'UTF-8');
    $s = str_replace('_', ' ', $s);
    $accents = [
        'à'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i',
        'ô'=>'o','ö'=>'o',
        'ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
    ];
    $s = strtr($s, $accents);
    return (string) preg_replace('/\s+/', ' ', $s);
}

/**
 * Resolve any status string (accented, mojibake, ASCII) to a canonical
 * ASCII DB key.  Returns the cleaned string as-is when already canonical.
 */
function canonical_statut(string $raw): string
{
    $norm = normalize_status($raw);
    // Exact match first (already clean)
    $all = ['en attente','confirmee','en cours','terminee','annulee',
            'disponible','en location','en maintenance','endommage'];
    if (in_array($norm, $all, true)) {
        return $norm;
    }
    // Fuzzy fallback for leftover variants
    if (str_contains($norm, 'attente'))     return 'en attente';
    if (str_contains($norm, 'confirm'))     return 'confirmee';
    if (str_contains($norm, 'cours'))       return 'en cours';
    if (str_contains($norm, 'termin'))      return 'terminee';
    if (str_contains($norm, 'annul'))       return 'annulee';
    if (str_contains($norm, 'disponible'))  return 'disponible';
    if (str_contains($norm, 'location'))    return 'en location';
    if (str_contains($norm, 'maintenance')) return 'en maintenance';
    if (str_contains($norm, 'endommag'))    return 'endommage';
    return $norm; // unknown — return as-is
}

/**
 * Render a coloured pill badge for a location statut.
 * Accepts any variant (accented, mojibake, ASCII) and always produces
 * the correct CSS class + the accented French display label.
 */
function statut_badge(string $statut): string
{
    $key = canonical_statut($statut);
    $classMap = [
        'en attente' => 'statut-en-attente',
        'confirmee'  => 'statut-confirmee',
        'en cours'   => 'statut-en-cours',
        'terminee'   => 'statut-terminee',
        'annulee'    => 'statut-annulee',
    ];
    $class   = $classMap[$key] ?? 'statut-unknown';
    $display = statut_label($key);
    return '<span class="badge ' . e($class) . '">' . e($display) . '</span>';
}

/**
 * Render a coloured pill badge for an equipment état.
 */
function etat_badge(string $etat): string
{
    $key = canonical_statut($etat);
    $classMap = [
        'disponible'     => 'etat-disponible',
        'en location'    => 'etat-en-location',
        'en maintenance' => 'etat-en-maintenance',
        'endommage'      => 'etat-endommage',
    ];
    $class   = $classMap[$key] ?? 'etat-unknown';
    $display = etat_label($key);
    return '<span class="badge ' . e($class) . '">' . e($display) . '</span>';
}
