<?php
// proxy.php

$allowedPaths = [
  'models/log.json',
  'config/config.json'
];

$path = $_GET['file'] ?? '';
$path = str_replace('..', '', $path); // Básica sanitización para evitar payasadas

if (!in_array($path, $allowedPaths)) {
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
