<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Emulates Apache's mod_rewrite for PHP built-in web server.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the requested file exists in public/, serve it directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Otherwise route through Laravel front controller
require_once __DIR__ . '/public/index.php';
