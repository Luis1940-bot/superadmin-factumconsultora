<?php
require_once dirname(__DIR__, 3) . '/private/config/config.php';

header('Content-Type: application/json');

$baseDir = BASE_DIR;
$jsonPath = $baseDir . '/private/config/app.json';
$data = file_get_contents('php://input');

if (json_decode($data) !== null) {
  file_put_contents($jsonPath, $data);
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'JSON inválido']);
}
