<?php
/**
 * Router para o servidor embutido do PHP:
 * php -S localhost:8080 -t public public/router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}
require_once __DIR__ . '/index.php';
