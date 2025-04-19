<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/private/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['ide'] ?? 0);
$dbname = $_GET['id'] ?? null;

if (!$dbname) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Falta el parámetro de base de datos.']);
  exit;
}
// ⚙️ Configuración conexión
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

$sql = "SELECT idLTYcontrol, control, nombre, detalle, orden FROM LTYcontrol WHERE idLTYreporte = $id ORDER BY orden ASC";
$res = $mysqli->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}
echo json_encode(['success' => true, 'data' => $data]);
