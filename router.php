<?php

/**
 * Routeur principal PHP (corrigé)
 * php -S 0.0.0.0:8080 router.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * DEBUG (optionnel)
 */
// file_put_contents(__DIR__ . '/router.log', $uri . PHP_EOL, FILE_APPEND);

/**
 * HOME
 */
if ($uri === '/') {
    header('Location: /public/scrutin.php');
    exit;
}

/**
 * =========================
 * 🔥 ROUTE API (FIX IMPORTANT)
 * =========================
 */
$apiRoot = '/evoting/api';

if (strpos($uri, '/api/') === 0 || $uri === '/api' || strpos($uri, $apiRoot . '/') === 0 || $uri === $apiRoot) {
    if (strpos($uri, '/api') === 0) {
        // Normalise les appels /api/... vers /evoting/api/... pour la logique interne
        $path = preg_replace('#^/api#', $apiRoot, $uri, 1);
    } else {
        $path = $uri;
    }

    // on injecte un PATH propre pour api.php
    $_SERVER['REQUEST_URI'] = $path;

    require __DIR__ . '/app/api.php';
    exit;
}

/**
 * =========================
 * FICHIERS STATIQUES
 * =========================
 */
$file = __DIR__ . $uri;

if (is_file($file)) {
    return false;
}

if (is_dir($file)) {
    return false;
}

/**
 * =========================
 * 404 PROPRE JSON FRIENDLY
 * =========================
 */
http_response_code(404);

if (str_starts_with($uri, '/api')) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "API endpoint not found",
        "uri" => $uri
    ]);
} else {
    header('Content-Type: text/html');
    echo "<h1>404 - Not Found</h1>";
    echo "<p>URI demandée : {$uri}</p>";
}