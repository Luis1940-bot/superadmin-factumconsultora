<?php
require_once __DIR__ . '/private/config/auth_token.php';

header('Content-Type: application/json');

$headers = getallheaders();
$clientToken = $headers['Authorization'] ?? '';

if (str_starts_with($clientToken, 'Bearer ')) {
  $clientToken = substr($clientToken, 7);
}

if ($clientToken !== $token) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$logFile = __DIR__ . '/../models/log.json';

if (!file_exists($logFile)) {
  http_response_code(500);
  echo json_encode(['error' => 'Archivo log.json no encontrado']);
  exit;
}

$data = json_decode(file_get_contents($logFile), true);
echo json_encode($data);
