<?php

$allowedPaths = [
  'models/log.json',
  'config/config.json'
];

$path = $_GET['file'] ?? '';
$path = str_replace('..', '', $path); // 🧼 Sanitización básica
$path = ltrim($path, '/'); // sin barra al principio, por las dudas

// ✅ Permitir si es una ruta exacta o si comienza con ciertos prefijos
$allowed = in_array($path, $allowedPaths) ||
  strpos($path, 'models/App/') === 0 ||
  strpos($path, 'models/consultas/') === 0 ||
  strpos($path, 'models/log.json') === 0;
if (!$allowed) {
  http_response_code(403);
  echo json_encode(["error" => "Access Denied"]);
  exit;
}


$remoteBase = 'https://factumconsultora.com/scg-mccain/';
$remoteUrl = $remoteBase . $path;

$context = stream_context_create([
  "http" => [
    "method" => "GET",
    "header" => "User-Agent: SuperAdmin-Proxy"
  ]
]);

$data = @file_get_contents($remoteUrl, false, $context);

if ($data === false) {
  http_response_code(500);
  echo json_encode(["error" => "Failed to fetch remote file"]);
  exit;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo $data;
