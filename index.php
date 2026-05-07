<?php
/**
 * TaskFlow Pro — Front Controller
 */

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('ROOT_PATH',   __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('MODEL_PATH',  ROOT_PATH . '/models');
define('CTRL_PATH',   ROOT_PATH . '/controllers');
define('VIEW_PATH',   ROOT_PATH . '/views');

require CONFIG_PATH . '/config.php';
require CONFIG_PATH . '/database.php';

// Models — order matters: dependencies before dependents
require MODEL_PATH . '/UserModel.php';
require MODEL_PATH . '/CategoryModel.php';
require MODEL_PATH . '/TagModel.php';
require MODEL_PATH . '/TaskModel.php';
require MODEL_PATH . '/DailyLogModel.php';    // must be before AnalyticsModel
require MODEL_PATH . '/AnalyticsModel.php';

// ---- Session ----
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

// ---- Global helpers ----
function auth(): ?array { return $_SESSION['user'] ?? null; }

function requireAuth(): void
{
    if (!auth()) { header('Location: ' . APP_URL . '/login'); exit; }
}

function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---- Router ----
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = parse_url(APP_URL, PHP_URL_PATH);
$path   = '/' . ltrim(str_replace($base, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

$key = $method . ' ' . $path;

function ctrl(string $name): object
{
    static $cache = [];
    if (!isset($cache[$name])) {
        require_once CTRL_PATH . "/{$name}.php";
        $cache[$name] = new $name();
    }
    return $cache[$name];
}

$routes = [
    'GET /login'             => fn() => ctrl('AuthController')->showLogin(),
    'POST /login'            => fn() => ctrl('AuthController')->login(),
    'GET /register'          => fn() => ctrl('AuthController')->showRegister(),
    'POST /register'         => fn() => ctrl('AuthController')->register(),
    'GET /logout'            => fn() => ctrl('AuthController')->logout(),

    'GET /'                  => fn() => ctrl('DashboardController')->index(),
    'GET /dashboard'         => fn() => ctrl('DashboardController')->index(),
    'GET /api/dashboard'     => fn() => ctrl('DashboardController')->apiData(),

    'GET /tasks'             => fn() => ctrl('TaskController')->index(),
    'GET /tasks/board'       => fn() => ctrl('TaskController')->board(),
    'GET /tasks/create'      => fn() => ctrl('TaskController')->create(),
    'POST /tasks/create'     => fn() => ctrl('TaskController')->store(),
    'GET /tasks/edit'        => fn() => ctrl('TaskController')->edit(),
    'POST /tasks/update'     => fn() => ctrl('TaskController')->update(),
    'POST /tasks/delete'     => fn() => ctrl('TaskController')->delete(),
    'POST /tasks/status'     => fn() => ctrl('TaskController')->updateStatus(),

    'GET /api/tasks'         => fn() => ctrl('TaskController')->apiList(),
    'POST /api/tasks'        => fn() => ctrl('TaskController')->apiCreate(),
    'PUT /api/tasks'         => fn() => ctrl('TaskController')->apiUpdate(),
    'DELETE /api/tasks'      => fn() => ctrl('TaskController')->apiDelete(),

    'GET /log'               => fn() => ctrl('LogController')->index(),
    'POST /log/save'         => fn() => ctrl('LogController')->save(),

    'GET /analytics'         => fn() => ctrl('AnalyticsController')->index(),
    'GET /api/analytics'     => fn() => ctrl('AnalyticsController')->apiData(),

    'GET /reports'           => fn() => ctrl('ReportController')->index(),
    'GET /reports/export'    => fn() => ctrl('ReportController')->export(),

    'GET /profile'           => fn() => ctrl('ProfileController')->index(),
    'POST /profile/update'   => fn() => ctrl('ProfileController')->update(),
    'POST /profile/password' => fn() => ctrl('ProfileController')->changePassword(),

    'GET /job-import'          => fn() => ctrl('JobImportController')->showImport(),
    'POST /job-import/parse'   => fn() => ctrl('JobImportController')->parse(),
    'POST /job-import/save'    => fn() => ctrl('JobImportController')->saveBulk(),
];

$publicRoutes = ['GET /login', 'POST /login', 'GET /register', 'POST /register'];

if (!in_array($key, $publicRoutes) && !auth()) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

if (isset($routes[$key])) {
    ($routes[$key])();
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
    <title>404 — ' . APP_NAME . '</title>
    <style>body{font-family:sans-serif;text-align:center;padding:80px;background:#0f0f1a;color:#ccc}
    h1{font-size:5rem;margin:0;color:#6C63FF}a{color:#6C63FF;text-decoration:none}</style>
    </head><body><h1>404</h1><p>Page not found.</p>
    <a href="' . APP_URL . '">← Back to Dashboard</a></body></html>';
}