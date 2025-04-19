<?php
require_once dirname(__DIR__, 3) . '/private/config/config.php';
include_once BASE_DIR . "/private/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

$nombre = $_POST['nombre'] ?? '';
$tipo = $_POST['tipo'] ?? 'PROCEDURE';
$params = $_POST['params'] ?? [];

$nombre = $mysqli->real_escape_string($nombre);

try {
  if ($tipo === 'FUNCTION') {
    $paramList = implode(', ', array_map(fn($v) => "'" . $mysqli->real_escape_string($v) . "'", $params));
    $query = "SELECT $nombre($paramList) AS resultado";
    $res = $mysqli->query($query);
    $data = $res->fetch_assoc();
    echo "<pre>Resultado: " . htmlspecialchars($data['resultado']) . "</pre>";
  } else {
    $paramList = implode(', ', array_map(fn($v) => "'" . $mysqli->real_escape_string($v) . "'", $params));
    $mysqli->query("CALL $nombre($paramList)");
    echo "<pre>Procedimiento ejecutado correctamente ✅</pre>";
  }
} catch (Exception $e) {
  echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
