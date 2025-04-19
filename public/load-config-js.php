<?php

// load-config-json.php
require_once dirname(__DIR__) . '/private/config/config.php';

$host = $_SERVER['HTTP_HOST'];
$hostname = explode(':', $host)[0]; // Quita el puerto si viene (localhost:8000 → localhost)


if ($hostname === 'localhost' || $hostname === '127.0.0.1') {
  $baseUrl = 'http://localhost:8000';
} elseif ($hostname === 'sadmin.factumconsultora.com') {
  $baseUrl = 'https://sadmin.factumconsultora.com/';
} else {
  $baseUrl = 'https://factumconsultora.com/tcontrol';
}

header('Content-Type: application/json');
// echo json_encode(['baseUrl' => $baseUrl]);
echo json_encode([
  'baseUrl' => $baseUrl,
  'routes' => [
    // 'getLogData' => "$baseUrl/api/getLogData.php",
    // 'reloadFlag' => "$baseUrl/api/reload-flag.php",
    // agregá más endpoints acá
  ]
]);
