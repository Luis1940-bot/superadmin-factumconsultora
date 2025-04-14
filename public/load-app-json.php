<?php
require_once dirname(__DIR__) . '/config/config.php';
/** @var string $baseUrl */
$baseUrl = BASE_DIR;
require_once $baseUrl . '/lib/ErrorLogger.php';
ErrorLogger::initialize($baseUrl . '/logs/error.log');
header('Content-Type: application/json');

$name = $_GET['file'] ?? 'app';
$allowed = ['app', 'log', 'config']; // lista blanca

if (!in_array($name, $allowed)) {
  echo json_encode(['error' => 'Archivo no permitido']);
  exit;
}

$path = BASE_DIR . "/config/{$name}.json";
// error_log("Buscando: " . $path);

if (file_exists($path)) {
  readfile($path);
} else {
  echo json_encode(['error' => 'Archivo no encontrado']);
}
