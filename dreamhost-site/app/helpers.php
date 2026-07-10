<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path): string
{
    if ($path === '') {
        return '/';
    }

    return '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function is_active_path(string $currentPath, string $targetPath): bool
{
    if ($targetPath === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $targetPath || str_starts_with($currentPath, rtrim($targetPath, '/') . '/');
}

function send_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function redirect_to(string $target, int $status = 302): void
{
    http_response_code($status);
    header('Location: ' . $target);
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }

    try {
        return (new DateTimeImmutable($date))->format('M j, Y');
    } catch (Throwable) {
        return $date;
    }
}

function read_json_cache(string $name, int $ttlSeconds): mixed
{
    $path = cache_path($name);
    if (!is_file($path)) {
        return null;
    }

    if (time() - filemtime($path) > $ttlSeconds) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    return json_decode($raw, true);
}

function write_json_cache(string $name, mixed $data): void
{
    $path = cache_path($name);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    file_put_contents($path, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function cache_path(string $name): string
{
    $safeName = preg_replace('/[^a-z0-9_.-]/i', '-', $name) ?: 'cache.json';
    return rtrim((string) app_config('cache_dir'), '/') . '/' . $safeName;
}

function http_json(string $url, array $options = []): array
{
    $method = strtoupper((string) ($options['method'] ?? 'GET'));
    $headers = $options['headers'] ?? [];
    $body = $options['body'] ?? null;
    $timeout = (int) ($options['timeout'] ?? 20);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'Nodestrich/1.0',
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        if ($raw === false || $status < 200 || $status >= 300) {
            throw new RuntimeException($error ?: 'HTTP ' . $status . ' from ' . $url);
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON from ' . $url);
        }

        return $decoded;
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body ?? '',
            'timeout' => $timeout,
        ],
    ]);

    $raw = file_get_contents($url, false, $context);
    if ($raw === false) {
        throw new RuntimeException('Failed to fetch ' . $url);
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid JSON from ' . $url);
    }

    return $decoded;
}
