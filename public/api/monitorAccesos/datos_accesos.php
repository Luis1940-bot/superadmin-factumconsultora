<?php
header('Content-Type: application/json; charset=utf-8');

// 🔐 Seguridad y encabezados
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce'");
require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$dbname = $_GET['dbName'];
// 📡 Conexión DB
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

// 🛰 Geolocalización + Zona Horaria
function obtenerDatosGeo($ip)
{
  $api = "http://ipwhois.app/json/$ip";
  $info = @file_get_contents($api);
  if (!$info) {
    return ['geo' => 'Desconocido', 'timezone' => 'UTC'];
  }

  $data = json_decode($info, true);
  $geo = ($data['country'] ?? 'N/A') . ' - ' . ($data['city'] ?? 'N/A');
  $timezone = $data['timezone'] ?? 'UTC';

  return ['geo' => $geo, 'timezone' => $timezone];
}

// 🧠 Consulta principal: últimos accesos y fallos
// $query = "
//   SELECT idusuario, email, planta, ip, navegador, creado_en AS fecha, 'ok' as estado 
//   FROM log_accesos
//   UNION ALL
//   SELECT NULL as idusuario, email, planta, ip, navegador, fecha AS fecha, 'fail' as estado 
//   FROM log_fallos_login
//   ORDER BY fecha DESC
//   LIMIT 100
// ";
$query = "
SELECT * FROM (
  SELECT 'mc1000' AS base, idusuario, email, planta, ip, navegador, creado_en AS fecha, 'ok' AS estado FROM mc1000.log_accesos
  UNION ALL
  SELECT 'mc1000' AS base, NULL AS idusuario, email, planta, ip, navegador, fecha AS fecha, 'fail' AS estado FROM mc1000.log_fallos_login

  UNION ALL
  SELECT 'mc2000', idusuario, email, planta, ip, navegador, creado_en, 'ok' FROM mc2000.log_accesos
  UNION ALL
  SELECT 'mc2000', NULL, email, planta, ip, navegador, fecha, 'fail' FROM mc2000.log_fallos_login

  UNION ALL
  SELECT 'mc3000', idusuario, email, planta, ip, navegador, creado_en, 'ok' FROM mc3000.log_accesos
  UNION ALL
  SELECT 'mc3000', NULL, email, planta, ip, navegador, fecha, 'fail' FROM mc3000.log_fallos_login

  UNION ALL
  SELECT 'mc4000', idusuario, email, planta, ip, navegador, creado_en, 'ok' FROM mc4000.log_accesos
  UNION ALL
  SELECT 'mc4000', NULL, email, planta, ip, navegador, fecha, 'fail' FROM mc4000.log_fallos_login

  UNION ALL
  SELECT 'mc5000', idusuario, email, planta, ip, navegador, creado_en, 'ok' FROM mc5000.log_accesos
  UNION ALL
  SELECT 'mc5000', NULL, email, planta, ip, navegador, fecha, 'fail' FROM mc5000.log_fallos_login

  UNION ALL
  SELECT 'mc6000', idusuario, email, planta, ip, navegador, creado_en, 'ok' FROM mc6000.log_accesos
  UNION ALL
  SELECT 'mc6000', NULL, email, planta, ip, navegador, fecha, 'fail' FROM mc6000.log_fallos_login
) AS accesos_combinados
ORDER BY fecha DESC
LIMIT 100;
";


$result = $mysqli->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
  $geoData = obtenerDatosGeo($row['ip']);
  $row['geo'] = $geoData['geo'];
  $row['timezone'] = $geoData['timezone'];

  // 📅 Convertir la fecha a la zona horaria local
  try {
    $fechaUTC = new DateTime($row['fecha'], new DateTimeZone('UTC'));
    $fechaUTC->setTimezone(new DateTimeZone($row['timezone']));
    $row['fecha_local'] = $fechaUTC->format('Y-m-d H:i:s');
  } catch (Throwable $e) {
    $row['fecha_local'] = $row['fecha']; // Fallback
  }

  $data[] = $row;
}

// 🔍 Fuerza bruta por IP
$ipsSospechosas = [];
// $q1 = "
//   SELECT ip, COUNT(*) as intentos
//   FROM log_fallos_login
//   WHERE fecha >= NOW() - INTERVAL 10 MINUTE
//   GROUP BY ip
//   HAVING intentos >= 5
// ";
$q1 = "
SELECT * FROM (
  SELECT 'mc1000' AS base, ip, COUNT(*) AS intentos
  FROM mc1000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING intentos >= 5

  UNION ALL

  SELECT 'mc2000', ip, COUNT(*) FROM mc2000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc3000', ip, COUNT(*) FROM mc3000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc4000', ip, COUNT(*) FROM mc4000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc5000', ip, COUNT(*) FROM mc5000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc6000', ip, COUNT(*) FROM mc6000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING COUNT(*) >= 5
) AS fuerza_bruta_ips
ORDER BY intentos DESC;
";

$res1 = $mysqli->query($q1);
while ($row = $res1->fetch_assoc()) {
  $geo = obtenerDatosGeo($row['ip']);
  $row['geo'] = $geo['geo'];
  $ipsSospechosas[] = $row;
}

// 🔍 Fuerza bruta por email
$emailsSospechosos = [];
// $q2 = "
//   SELECT email, COUNT(*) as intentos
//   FROM log_fallos_login
//   WHERE fecha >= NOW() - INTERVAL 10 MINUTE
//   GROUP BY email
//   HAVING intentos >= 5
// ";
$q2 = "
SELECT * FROM (
  SELECT 'mc1000' AS base, email, COUNT(*) AS intentos
  FROM mc1000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING intentos >= 5

  UNION ALL

  SELECT 'mc2000', email, COUNT(*) FROM mc2000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc3000', email, COUNT(*) FROM mc3000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc4000', email, COUNT(*) FROM mc4000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc5000', email, COUNT(*) FROM mc5000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING COUNT(*) >= 5

  UNION ALL

  SELECT 'mc6000', email, COUNT(*) FROM mc6000.log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING COUNT(*) >= 5
) AS fuerza_bruta_emails
ORDER BY intentos DESC;
";

$res2 = $mysqli->query($q2);
while ($row = $res2->fetch_assoc()) {
  $emailsSospechosos[] = $row;
}

// 📤 Respuesta JSON
echo json_encode([
  'success' => true,
  'data' => $data,
  'ips_sospechosas' => $ipsSospechosas,
  'emails_sospechosos' => $emailsSospechosos
]);
exit;
