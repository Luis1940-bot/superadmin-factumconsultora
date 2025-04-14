<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');

require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

$query = "
  SELECT l.nuxpedido, l.fecha, l.hora, l.idusuario, l.idLTYreporte, 
         l2.idLTYcliente AS idClienteReporte, l.idLTYcliente AS idClienteRegistro
  FROM LTYregistrocontrol l 
  INNER JOIN LTYreporte l2 ON l2.idLTYreporte = l.idLTYreporte 
  WHERE l.idLTYcliente = 0 
  ORDER BY l.horaautomatica DESC";

$result = $mysqli->query($query);
$records = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $records[] = $row;
  }
}

echo json_encode(['success' => true, 'data' => $records]);
exit;
