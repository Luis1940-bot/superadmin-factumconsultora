<?php
$format = $_GET['format'] ?? 'csv';
require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/private/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

$query = "
  SELECT email, planta, ip, navegador, fecha, 'ok' as estado FROM log_accesos
  UNION ALL
  SELECT email, planta, ip, navegador, fecha, 'fail' as estado FROM log_fallos_login
  ORDER BY fecha DESC
";

$result = $mysqli->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

if ($format === 'json') {
  header("Content-Type: application/json");
  echo json_encode($data, JSON_PRETTY_PRINT);
} else {
  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=\"accesos.csv\"");
  $out = fopen("php://output", "w");
  fputcsv($out, array_keys($data[0]));
  foreach ($data as $line) fputcsv($out, $line);
  fclose($out);
}
