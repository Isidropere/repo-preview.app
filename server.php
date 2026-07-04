<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a
// Laravel application without having to install a "real" web server software
// here.
$file = __DIR__.'/public'.$uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Max-Age: 86400");

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'gif'   => 'image/gif',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];

    $mimeType = $mimes[$ext] ?? null;

    if (!$mimeType && function_exists('mime_content_type')) {
        $mimeType = @mime_content_type($file);
    }

    if (!$mimeType) {
        $mimeType = 'application/octet-stream';
    }

    header("Content-Type: $mimeType");
    readfile($file);
    exit;
}

require_once __DIR__.'/public/index.php';
