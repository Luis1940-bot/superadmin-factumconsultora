<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$idLTYreporte = isset($_GET['idLTYreporte']) ? intval($_GET['idLTYreporte']) : 0;
$dbname = $_GET['id'] ?? null;

if ($idLTYreporte <= 0 || !$dbname) {
  echo json_encode(["error" => "Datos incompletos"]);
  exit;
}

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(["error" => "Fallo la conexión"]);
  exit;
}

mysqli_set_charset($mysqli, "utf8mb4");

$query = "
SELECT c.idLTYcontrol, c.control, c.detalle, c.tipodato, c.tpdeobserva, c.orden,
       r.nombre AS nombre_reporte,
       c.idLTYcliente AS id_cliente,
       (SELECT cliente FROM LTYcliente WHERE idLTYcliente = c.idLTYcliente LIMIT 1) AS nombre_cliente
FROM LTYcontrol c
INNER JOIN LTYreporte r ON r.idLTYreporte = c.idLTYreporte
WHERE c.idLTYreporte = $idLTYreporte
ORDER BY c.orden ASC;
";

$result = $mysqli->query($query);

$registros = [];
$nombreCliente = '';
$idCliente = 0;

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $registros[] = $row;
    $nombreCliente = $row['nombre_cliente'];
    $idCliente = $row['id_cliente'];
  }
}

$mysqli->close();

echo json_encode([
  "registros" => $registros,
  "nombreCliente" => $nombreCliente,
  "idCliente" => $idCliente
]);
