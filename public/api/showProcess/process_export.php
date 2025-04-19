<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="processlist_export_' . date('Ymd_His') . '.csv"');

require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/private/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die("Error de conexión a la base de datos.");
}
mysqli_set_charset($mysqli, "utf8mb4");

$result = $mysqli->query("SHOW FULL PROCESSLIST");

$salida = fopen('php://output', 'w');
fputcsv($salida, ['ID', 'User', 'Host', 'DB', 'Command', 'Time', 'State', 'Info']);

while ($row = $result->fetch_assoc()) {
  fputcsv($salida, [
    $row['Id'],
    $row['User'],
    $row['Host'],
    $row['db'],
    $row['Command'],
    $row['Time'],
    $row['State'],
    mb_strimwidth($row['Info'] ?? '', 0, 100, '...')
  ]);
}
fclose($salida);
exit;
