<?php
require_once dirname(__DIR__, 3) . '/private/config/config.php';
include_once BASE_DIR . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  http_response_code(500);
  echo "Error de conexión.";
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

if (isset($_GET['all']) && $_GET['all'] === '1') {
  $res = $mysqli->query("SHOW FULL PROCESSLIST");
  $matados = 0;

  while ($row = $res->fetch_assoc()) {
    if (
      strtolower($row['Command']) === 'sleep' &&
      intval($row['Time']) > 60 &&
      strtolower($row['User']) !== 'system user'
    ) {
      $mysqli->query("KILL {$row['Id']}");
      $matados++;
    }
  }

  echo "KILL masivo ejecutado. Procesos eliminados: $matados.";
  exit;
}

// Individual
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  echo "Parámetro inválido.";
  exit;
}

$id = intval($_GET['id']);
$info = $mysqli->query("SHOW FULL PROCESSLIST");
while ($row = $info->fetch_assoc()) {
  if ($row['Id'] == $id) {
    $command = strtolower($row['Command']);
    if (in_array($command, ['query', 'locked'])) {
      echo "❌ No se puede KILL un proceso en ejecución.";
      exit;
    }
    $mysqli->query("KILL $id");
    echo "✅ Proceso $id eliminado.";
    exit;
  }
}

echo "⚠️ Proceso no encontrado.";
