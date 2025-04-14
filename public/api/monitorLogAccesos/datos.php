<?php
header('Content-Type: application/json; charset=utf-8');

// 🔐 Seguridad básica
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce'");

require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

// DB
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

// Parámetros de entrada
$id = $_GET['id'] ?? null;
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;
$desdeFull = "$desde 00:00:00";
$hastaFull = "$hasta 23:59:59";

if (!$id || !$desde || !$hasta) {
  echo json_encode(['error' => 'Faltan parámetros requeridos']);
  exit;
}

// Expande el rango para incluir el día completo
$desdeCompleto = "$desde 00:00:00";
$hastaCompleto = "$hasta 23:59:59";

try {
  // Gráfico de horas
  $stmt = $mysqli->prepare("SELECT HOUR(creado_en) AS hora, COUNT(*) AS ingresos
                            FROM log_accesos
                            WHERE planta = ? AND creado_en BETWEEN ? AND ?
                            GROUP BY HOUR(creado_en)
                            ORDER BY hora");
  $stmt->bind_param("iss", $id, $desdeCompleto, $hastaCompleto);
  $stmt->execute();
  $result = $stmt->get_result();
  $horas = [];
  while ($row = $result->fetch_assoc()) {
    $horas[] = $row;
  }
  $stmt->close();

  $completas = array_fill(0, 24, 0);
  foreach ($horas as $fila) {
    $completas[(int)$fila['hora']] = (int)$fila['ingresos'];
  }
  $horasFormateadas = [];
  foreach ($completas as $h => $c) {
    $horasFormateadas[] = ['hora' => (string)$h, 'ingresos' => $c];
  }

  // Resumen de accesos por usuario
  $sqlResumen = "
    SELECT 
      la.idusuario AS ID,
      u.nombre AS Usuario,
      u.area AS Área,
      u.puesto AS Puesto,
      COUNT(*) AS `Cantidad total ingresos`,
      COUNT(DISTINCT DATE(la.creado_en)) AS `Días activos`,
      DATEDIFF(?, ?) + 1 AS `Intervalo en días`,
      ROUND(COUNT(*) / (DATEDIFF(?, ?) + 1), 2) AS `Promedio por día`,
      MIN(la.creado_en) AS `Primer ingreso`,
      MAX(la.creado_en) AS `Último ingreso`,
      (
        SELECT HOUR(la2.creado_en)
        FROM log_accesos la2
        WHERE la2.idusuario = la.idusuario
          AND la2.planta = ?
          AND la2.creado_en BETWEEN ? AND ?
        GROUP BY HOUR(la2.creado_en)
        ORDER BY COUNT(*) DESC
        LIMIT 1
      ) AS `Hora más frecuente`
    FROM log_accesos la
    INNER JOIN usuario u ON u.idusuario = la.idusuario
    WHERE la.planta = ? AND la.creado_en BETWEEN ? AND ?
    GROUP BY la.idusuario, u.nombre, u.area, u.puesto
    ORDER BY `Cantidad total ingresos` DESC
  ";

  $stmtResumen = $mysqli->prepare($sqlResumen);
  $stmtResumen->bind_param(
    "ssssississ",
    $hasta,
    $desde,
    $hasta,
    $desde,
    $id,
    $desdeCompleto,
    $hastaCompleto,
    $id,
    $desdeCompleto,
    $hastaCompleto
  );

  $stmtResumen->execute();
  $res = $stmtResumen->get_result();

  $resumen = [];
  while ($row = $res->fetch_assoc()) {
    $resumen[] = $row;
  }
  $stmtResumen->close();
  // Línea: ingresos diarios
  $stmtLinea = $mysqli->prepare("
  SELECT DATE(creado_en) AS fecha, COUNT(*) AS ingresos
  FROM log_accesos
  WHERE planta = ? AND creado_en BETWEEN ? AND ?
  GROUP BY DATE(creado_en)
  ORDER BY fecha ASC
");
  $stmtLinea->bind_param("iss", $id, $desdeCompleto, $hastaCompleto);

  $stmtLinea->execute();
  $linea = $stmtLinea->get_result()->fetch_all(MYSQLI_ASSOC);

  // Heatmap: día de la semana (0-6) y hora
  $stmtHeatmap = $mysqli->prepare("
  SELECT WEEKDAY(creado_en) AS dia, HOUR(creado_en) AS hora, COUNT(*) AS ingresos
  FROM log_accesos
  WHERE planta = ? AND creado_en BETWEEN ? AND ?
  GROUP BY dia, hora
");
  $stmtHeatmap->bind_param("iss", $id, $desdeCompleto, $hastaCompleto);

  $stmtHeatmap->execute();
  $heatmap = $stmtHeatmap->get_result()->fetch_all(MYSQLI_ASSOC);

  // === Usuarios sin ingresos ===
  $stmtSin = $mysqli->prepare("
  SELECT u.idusuario, u.nombre, u.area, u.puesto
  FROM usuario u
  WHERE u.activo = 's'
    AND u.idusuario NOT IN (
      SELECT DISTINCT idusuario
      FROM log_accesos
      WHERE planta = ? AND creado_en BETWEEN ? AND ?
    )
  ORDER BY u.nombre
");
  $stmtSin->bind_param("iss", $id, $desdeCompleto, $hastaCompleto);
  $stmtSin->execute();
  $sinIngresos = $stmtSin->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtSin->close();


  // Agregalo a la respuesta:
  echo json_encode([
    'horas' => $horasFormateadas,
    'resumen' => $resumen,
    'linea' => $linea,
    'heatmap' => $heatmap,
    'sin_ingresos' => $sinIngresos
  ]);


  // echo json_encode(['horas' => $horasFormateadas, 'resumen' => $resumen]);
} catch (Exception $e) {
  echo json_encode([
    'error' => 'Error al ejecutar consulta',
    'detalle' => $e->getMessage()
  ]);
  exit;
}
