<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');

try {
    if (str_starts_with($path, '/api/')) {
        handle_api($path);
        exit;
    }

    if ($path === '/signal') {
        handle_signal_redirect();
        exit;
    }

    [$title, $description, $content] = route_page($path);
    render_layout($title, $description, $content, $path);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);

    $message = app_config('debug')
        ? '<pre class="error-debug">' . e((string) $exception) . '</pre>'
        : '<p class="muted">Something went wrong while loading this page.</p>';

    render_layout(
        'Server Error - Nodestrich',
        'Something went wrong while loading Nodestrich.',
        '<section class="container page-section"><h1>Server Error</h1>' . $message . '</section>',
        $path
    );
}
