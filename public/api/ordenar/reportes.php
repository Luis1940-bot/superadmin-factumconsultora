<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false]);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

$sql = "
  SELECT l.idLTYreporte, l.nombre, l.idLTYcliente, l2.cliente
  FROM LTYreporte l
  INNER JOIN LTYcliente l2 ON l2.idLTYcliente = l.idLTYcliente
  ORDER BY l.idLTYreporte ASC";

$res = $mysqli->query($sql);
$data = [];

while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}
echo json_encode(['success' => true, 'data' => $data]);
