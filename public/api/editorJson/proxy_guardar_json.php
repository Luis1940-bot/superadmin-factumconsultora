<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(dirname(dirname(__DIR__))) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/logs/logs/error.log');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

$input = file_get_contents('php://input');
$input = json_decode($input, true);

$ruta = trim($input['ruta'] ?? '');
$contenido = $input['contenido'] ?? null;



if (!$ruta || !$contenido) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
  exit;
}
array_walk_recursive($contenido, function (&$value) {
  if (is_string($value)) {
    $value = preg_replace('/\s+/', ' ', trim($value));
  }
});


// Verificar que sea una URL válida del dominio factumconsultora.com
if (!preg_match('/^https:\/\/factumconsultora\.com\/scg-mccain\/.+\.json$/', $ruta)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'URL no permitida']);
  exit;
}

// Armar POST hacia el API receptor remoto
$apiUrl = 'https://factumconsultora.com/scg-mccain/Routes/guardar_json_remoto.php';
$archivoRelativo = str_replace('https://factumconsultora.com/scg-mccain/', '', $ruta);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'archivo' => $archivoRelativo,
  'contenido' => $contenido
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'User-Agent: Mozilla/5.0',
  'Referer: https://sadmin.factumconsultora.com',
  'Origin: https://sadmin.factumconsultora.com',
]);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
  echo json_encode(['success' => false, 'message' => 'Error en cURL', 'detalle' => curl_error($ch)]);
  curl_close($ch);
  error_log("Error en cURL" .  curl_error($ch));
  exit;
}

curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
  error_log("success");
  echo json_encode(['success' => true]);
} else {
  error_log("el servidor remoto devolvió un error " . $httpCode . '  ///  ' . $response);
  echo json_encode([
    'success' => false,
    'message' => 'El servidor remoto devolvió un error',
    'codigo' => $httpCode,
    'respuesta' => $response,
  ]);
}
