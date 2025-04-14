<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die("Conexión fallida.");
}
mysqli_set_charset($mysqli, "utf8mb4");

$procName = $_GET['id'] ?? '';
$procName = $mysqli->real_escape_string($procName);

$tipo = $_GET['tipo'] ?? 'PROCEDURE';
$result = $mysqli->query("SHOW CREATE $tipo `$procName`");

if (!$result || $result->num_rows === 0) {
  die("Procedimiento no encontrado.");
}
$data = $result->fetch_assoc();
$createSQL = $data['Create Procedure'];

header('Content-Type: application/sql');
header("Content-Disposition: attachment; filename=\"$procName.sql\"");
echo $createSQL;
