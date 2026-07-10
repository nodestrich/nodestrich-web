<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONTENT_PATH', ROOT_PATH . '/content');

require APP_PATH . '/config.php';
require APP_PATH . '/helpers.php';
require APP_PATH . '/content.php';
require APP_PATH . '/data.php';
require APP_PATH . '/api.php';
require APP_PATH . '/pages.php';
require APP_PATH . '/layout.php';
