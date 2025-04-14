<?php
header('Content-Type: application/json; charset=utf-8');

// 🔐 Seguridad y encabezados
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce'");
require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

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
$query = "
  SELECT idusuario, email, planta, ip, navegador, creado_en AS fecha, 'ok' as estado 
  FROM log_accesos
  UNION ALL
  SELECT NULL as idusuario, email, planta, ip, navegador, fecha AS fecha, 'fail' as estado 
  FROM log_fallos_login
  ORDER BY fecha DESC
  LIMIT 100
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
$q1 = "
  SELECT ip, COUNT(*) as intentos
  FROM log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY ip
  HAVING intentos >= 5
";
$res1 = $mysqli->query($q1);
while ($row = $res1->fetch_assoc()) {
  $geo = obtenerDatosGeo($row['ip']);
  $row['geo'] = $geo['geo'];
  $ipsSospechosas[] = $row;
}

// 🔍 Fuerza bruta por email
$emailsSospechosos = [];
$q2 = "
  SELECT email, COUNT(*) as intentos
  FROM log_fallos_login
  WHERE fecha >= NOW() - INTERVAL 10 MINUTE
  GROUP BY email
  HAVING intentos >= 5
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
