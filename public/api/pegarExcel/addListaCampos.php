<?php
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: https://factumconsultora.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once dirname(__DIR__, 3)
  . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3)
  . '/private/logs/logs/error.log');
require_once dirname(__DIR__, 3)
  . '/private/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

// Zona horaria
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
// $data = '{"ruta":"/addListaCampos","datos":[{"control":"repr2f000421d9e","nombre":"nombre","detalle":"coloque sus nombres","tipodato":"tx","tpdeobserva":"x","orden":3},{"control":"repr2f000535a0c","nombre":"apelido","detalle":"coloque sus apellidos","tipodato":"tx","tpdeobserva":"x","orden":4}],"ultimoID":"5412","idLTYcliente":1,"idLTYreporte":"332","bdCliente":"mc1000"}';
// $data = json_decode($data, true);


if (!$data || !isset($data['datos']) || !isset($data['ultimoID'])) {
  echo json_encode(['success' => false, 'message' => 'Datos no válidos.']);
  exit;
}
$dbname = $data['bdCliente'];
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

$mysqli->begin_transaction();

try {
  $ultimoID = intval($data['ultimoID']);
  $ultimoOrden = 0;
  $idLTYreporte = intval($data['idLTYreporte']);
  $idLTYcliente = intval($data['idLTYcliente']);
  $visible = 's';
  foreach ($data['datos'] as $registro) {
    $control = $mysqli->real_escape_string($registro['control']);
    $nombre = $mysqli->real_escape_string($registro['nombre']);
    $detalle = $mysqli->real_escape_string($registro['detalle']);
    $tipodato = $mysqli->real_escape_string($registro['tipodato']);
    $tpdeobserva = $mysqli->real_escape_string($registro['tpdeobserva']);
    $orden = intval($registro['orden']);

    $sqlInsert = "INSERT INTO LTYcontrol 
      (control, nombre, detalle, tipodato, tpdeobserva, orden, idLTYreporte, idLTYcliente, visible)
      VALUES ('$control', '$nombre', '$detalle', '$tipodato', '$tpdeobserva', $orden, $idLTYreporte, $idLTYcliente, '$visible')";

    $mysqli->query($sqlInsert);
    $ultimoOrden = $orden;
  }

  $nuevoOrden = $ultimoOrden + 1;
  $sqlUpdate = "UPDATE LTYcontrol SET orden = $nuevoOrden WHERE idLTYcontrol = $ultimoID";
  $mysqli->query($sqlUpdate);

  $mysqli->commit();

  echo json_encode(['success' => true, 'message' => 'Registros guardados correctamente.']);
} catch (Exception $e) {
  $mysqli->rollback();
  echo json_encode(['success' => false, 'message' => 'Error al guardar datos: ' . $e->getMessage()]);
}

$mysqli->close();
exit;
