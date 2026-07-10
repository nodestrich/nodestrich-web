<?php
declare(strict_types=1);

$GLOBALS['APP_CONFIG'] = [
    'site_name' => 'Nodestrich',
    'site_url' => 'https://nodestrich.com',
    'amboss_api_key' => getenv('AMBOSS_API_KEY') ?: '',
    'signal_invite_url' => getenv('SIGNAL_INVITE_URL') ?: '',
    'cache_dir' => ROOT_PATH . '/cache',
    'debug' => (getenv('APP_DEBUG') ?: '') === '1',
];

$localConfig = ROOT_PATH . '/config.local.php';
if (is_file($localConfig)) {
    $local = require $localConfig;
    if (is_array($local)) {
        $GLOBALS['APP_CONFIG'] = array_replace($GLOBALS['APP_CONFIG'], $local);
    }
}

function app_config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['APP_CONFIG'][$key] ?? $default;
}
