<?php
header('Access-Control-Allow-Origin: *'); // Permitir acceso externo
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

$nombre = $_GET['nombre'] ?? '';
$tipo = strtoupper($_GET['tipo'] ?? 'PROCEDURE');
$formato = $_GET['formato'] ?? 'json'; // json o csv
$params = $_GET['params'] ?? [];

if (!$nombre) {
  http_response_code(400);
  echo json_encode(["error" => "Nombre de rutina no especificado"]);
  exit;
}

$nombreSQL = $mysqli->real_escape_string($nombre);
$paramSQL = '';

if (is_array($params)) {
  $sanitized = array_map(fn($v) => "'" . $mysqli->real_escape_string($v) . "'", $params);
  $paramSQL = implode(', ', $sanitized);
}

$query = ($tipo === 'FUNCTION')
  ? "SELECT $nombreSQL($paramSQL) AS resultado"
  : "CALL $nombreSQL($paramSQL)";

$result = $mysqli->query($query);

if (!$result) {
  http_response_code(500);
  echo json_encode(["error" => $mysqli->error]);
  exit;
}

$rows = [];
$fields = $result->fetch_fields();
while ($row = $result->fetch_assoc()) {
  $rows[] = $row;
}

// ➤ CSV
if ($formato === 'csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header("Content-Disposition: attachment; filename=\"$nombre.csv\"");

  $out = fopen('php://output', 'w');
  fputcsv($out, array_column($fields, 'name'));

  foreach ($rows as $row) {
    fputcsv($out, $row);
  }

  fclose($out);
  exit;
}

// ➤ JSON (default)
echo json_encode($rows);
