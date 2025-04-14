<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
mb_internal_encoding('UTF-8');
require_once dirname(dirname(dirname(__DIR__))) . '/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/logs/error.log');
/** 
 * @var array{timezone?: string} $_SESSION 
 */
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}

/**
 * Agrega una nueva variable en la base de datos y devuelve el estado de la operación.
 *
 * @param array<string, mixed> $objeto Datos de la variable a agregar.
 * @return array{success: bool, message: string, id?: int, array?: array<int, array<int, mixed>>}
 */

function writeJSON(array $objeto): array
{
  try {
    require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
    /** @var string $baseDir */
    $baseDir = BASE_DIR;
    $file = $baseDir . '/models/log.json';
    // $currentData = json_decode(file_get_contents($file), true);
    $jsonData = file_get_contents($file);
    $currentData = is_string($jsonData) ? json_decode($jsonData, true) : null;

    if (!is_array($currentData)) {
      $currentData = [];
    }

    // Asegurarse de que 'plantas' existe y es un array
    if (!isset($currentData['plantas']) || !is_array($currentData['plantas'])) {
      $currentData['plantas'] = [];
    }

    // Agregar el nuevo elemento a 'plantas'
    $currentData['plantas'][] = $objeto;

    // Guardar los datos actualizados en log.json
    if (file_put_contents($file, json_encode($currentData, JSON_PRETTY_PRINT))) {
      $response = ['success' => true, 'message' => 'Archivo JSON actualizado correctamente.'];
      // echo json_encode(['success' => true]);
    } else {
      $response = ['success' => false, 'message' => 'Error al escribir en log.json'];
      // echo json_encode(['success' => false, 'message' => 'Failed to write to log.json']);
    }
    return $response;
  } catch (\Throwable $e) {
    error_log("Error al escribir el JSON. Error: " . $e);
    print "Error!: " . $e->getMessage() . "<br>";
    return ['success' => false, 'message' => 'Error al escribir el JSON.'];
  }
}

header("Content-Type: application/json; charset=utf-8");

$datos = file_get_contents("php://input");
// $datos = '{"ruta":"/escribirJSON","rax":"&new=Thu Jun 20 2024 18:04:48 GMT-0300 (hora estándar de Argentina)","objeto":{"name":"mccain-balcarce","num":3}}';
// ✅ Manejo de datos vacíos
if (empty($datos)) {
  echo json_encode(['success' => false, 'message' => 'Faltan datos necesarios.']);
  exit;
}

// ✅ Decodificar JSON de manera segura
$data = json_decode($datos, true);

// ✅ Verificar si la decodificación fue exitosa y que $data es un array
if (!is_array($data)) {
  error_log("Error decoding JSON: " . json_encode($datos));
  echo json_encode(['success' => false, 'message' => 'Error al decodificar la cadena JSON']);
  exit;
}

// ✅ Validar y asignar valores de manera segura
$objeto = isset($data['objeto']) && is_array($data['objeto'])
  ? array_filter($data['objeto'], fn($key) => is_string($key), ARRAY_FILTER_USE_KEY)
  : [];

// ✅ Ahora los parámetros tienen los tipos correctos
writeJSON($objeto);
