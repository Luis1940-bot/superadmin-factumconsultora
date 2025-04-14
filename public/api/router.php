<?php
ob_start();
mb_internal_encoding('UTF-8');

// Config inicial
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 2) . '/logs/error.log');

// Zona horaria y sesión
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$domain = explode(':', $httpHost)[0];
$esLocalhost = in_array($domain, ['localhost', '127.0.0.1']);
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 14400,
    'path' => '/',
    'domain' => $domain,
    'secure' => !$esLocalhost,
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
  session_start();
}

date_default_timezone_set($_SESSION['timezone'] ?? 'America/Argentina/Buenos_Aires');

// Redirección HTTPS
if (!$esLocalhost && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
  header("Location: https://{$httpHost}{$requestUri}", true, 301);
  exit;
}

// CORS y tipo de contenido
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

header("Content-Type: application/json; charset=utf-8");

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = stripos($contentType, 'application/json') !== false;
$isMultipart = stripos($contentType, 'multipart/form-data') !== false;
$isFormUrlEncoded = stripos($contentType, 'application/x-www-form-urlencoded') !== false;

// Input
$data = [];
$ruta = null;

if ($isJson) {
  $rawInput = file_get_contents("php://input") ?: '{}';
  $data = json_decode($rawInput, true);
  $ruta = $data['ruta'] ?? null;
  // error_log("📥 JSON recibido: $rawInput");
} elseif ($isMultipart || $isFormUrlEncoded) {
  $data = $_POST;
  $ruta = $_POST['ruta'] ?? null;
  // error_log("📥 FormData recibido ($contentType)");
  // error_log("🗂️ POST: " . json_encode(array_keys($_POST)));
  // error_log("🖼️ FILES: " . json_encode(array_keys($_FILES)));
} else {
  echo json_encode(['success' => false, 'message' => 'Tipo de contenido no soportado']);
  exit;
}

if (!$ruta) {
  http_response_code(400);
  echo json_encode(['error' => 'Ruta no especificada']);
  exit;
}

// Resolución de rutas
function rutaInterna(string $rel): ?string
{
  return realpath(__DIR__ . '/' . ltrim($rel, '/'));
}

$rutasInternas = [
  '/checarEmail'  => 'AuthUser/Routes/checarEmail.php',
  '/nuevoAuth'    => 'AuthUser/Routes/ix.php',
  '/alertaEmail'  => '../../tools/sendAlertEmail.php',
  '/addListaCampos'  => 'pegarExcel/addListaCampos.php',
  '/updateControl'  => 'verRepetidosControl/updateControl.php',
  '/update_records'  => 'corregirRegistros/update_records.php',
  '/update_usuario'  => 'usuarios/update_usuario.php',
];

$rutasExternas = [
  '/addCompania'  => 'https://factumconsultora.com/tcontrol/Pages/RegisterPlant/Routes/nuevaCompania.php',
  '/escribeJSON'  => 'https://factumconsultora.com/tcontrol/Pages/RegisterPlant/Routes/escribeJSON.php',
  '/creaJSONapp'  => 'https://factumconsultora.com/tcontrol/Pages/RegisterPlant/Routes/creaJSONapp.php',
  '/subirImagen'  => 'https://factumconsultora.com/tcontrol/Routes/Imagenes/photo_upload.php',
  '/leerLogsExternos'  => 'https://factumconsultora.com/tcontrol/logs/error.log',
];

$rutas = array_map('rutaInterna', $rutasInternas) + $rutasExternas;
$destino = $rutas[$ruta] ?? null;

if (!$destino) {
  http_response_code(404);
  echo json_encode(['error' => 'Ruta no encontrada']);
  exit;
}

// Enviar a URL externa
if (filter_var($destino, FILTER_VALIDATE_URL)) {
  $ch = curl_init($destino);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);

  // Headers comunes
  $headers = [
    'User-Agent: Mozilla/5.0',
    'Referer: https://sadmin.factumconsultora.com',
    'Origin: https://sadmin.factumconsultora.com',
  ];

  if ($isJson) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  } elseif ($isMultipart) {
    $postFields = [];

    foreach ($_POST as $key => $value) {
      $postFields[$key] = $value;
    }

    foreach ($_FILES as $key => $file) {
      if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
        $postFields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
      }
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  } elseif ($isFormUrlEncoded) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  }

  if ($esLocalhost) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  }

  $response = curl_exec($ch);
  $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($response === false || curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en cURL', 'detalle' => $error]);
    exit;
  }

  curl_close($ch);
  http_response_code($statusCode);
  echo $response;
  if (ob_get_level()) ob_end_flush();
  exit;
}

// Incluir ruta local
if (!file_exists($destino)) {
  http_response_code(500);
  echo json_encode(['error' => 'Archivo de destino no encontrado']);
  if (ob_get_level()) ob_end_flush();
  exit;
}

require_once $destino;
if (ob_get_level()) ob_end_flush();
exit;
