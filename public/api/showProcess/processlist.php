<?php
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: https://factumconsultora.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die("Error de conexión a la base de datos.");
}
mysqli_set_charset($mysqli, "utf8mb4");

// Geolocalización con cache en memoria (solo durante ejecución)
function geolocalizarIp($ip, &$geoCache)
{
  if (isset($geoCache[$ip])) return $geoCache[$ip];
  $url = "http://ipwhois.app/json/$ip";
  $response = @file_get_contents($url);
  if ($response === false) return $geoCache[$ip] = 'Desconocido';
  $data = json_decode($response, true);
  return $geoCache[$ip] = ($data['country'] ?? 'N/A') . ' - ' . ($data['city'] ?? 'N/A');
}

$onlySleep = isset($_GET['sleep']) && $_GET['sleep'] === '1';
$filterIp = $_GET['ip'] ?? null;

$result = $mysqli->query("SHOW FULL PROCESSLIST");
$processes = [];
$ipCounts = [];
$geoCache = [];

while ($row = $result->fetch_assoc()) {
  $hostFull = $row['Host'];
  $ipOnly = explode(':', $hostFull)[0];

  if ($filterIp && strpos($hostFull, $filterIp) !== 0) {
    continue;
  }

  if ($onlySleep) {
    if (strtolower($row['Command']) === 'sleep' && intval($row['Time']) > 60) {
      $row['__ip'] = $ipOnly;
      $processes[] = $row;
      $ipCounts[$ipOnly] = ($ipCounts[$ipOnly] ?? 0) + 1;
    }
  } else {
    $row['__ip'] = $ipOnly;
    $processes[] = $row;
    $ipCounts[$ipOnly] = ($ipCounts[$ipOnly] ?? 0) + 1;
  }
}

$cssUrl = BASE_URL . "/api/showProcess/processlist.css?v=" . time();
$jsUrl = BASE_URL . "/api/showProcess/processlist.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";


// Configuración de alerta
$umbral = 10;
$ipAlertas = array_filter($ipCounts, fn($count) => $count > $umbral);
// Al final de processlist.php
file_put_contents(
  __DIR__ . '/logs/processlist_' . date('Ymd_H') . '.log',
  json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'total' => count($processes),
    'sleep' => count(array_filter($processes, fn($p) => strtolower($p['Command']) === 'sleep')),
    'ips' => $ipCounts,
  ]) . PHP_EOL,
  FILE_APPEND
);
if (!is_writable(__DIR__ . '/logs')) {
  echo "<pre style='color:red'>🚫 La carpeta /logs no tiene permisos de escritura.</pre>";
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>MySQL Processlist</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>
<?php



?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>📈 Historial de procesos</title>
  <!-- <script src="/lib/chart.js" nonce="<?= $nonce ?>"></script> -->

</head>

</html>

<body>
  <h1>🧠 MySQL Processlist <?= $onlySleep ? '(Sleep prolongados)' : '' ?><?= $filterIp ? " — IP filtrada: $filterIp" : '' ?></h1>

  <p class="total-procesos">
    Total procesos activos: <strong><?= count($processes) ?></strong>
  </p>

  <?php if (!empty($ipAlertas)): ?>
    <div class="clase-alerta">
      🚨 ALERTA: Se detectaron IPs con más de <?= $umbral ?> conexiones activas.
    </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>IP</th>
        <th>Host</th>
        <th>BD</th>
        <th>Comando</th>
        <th>Tiempo</th>
        <th>Estado</th>
        <th>Consulta</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($processes as $proc):
        $ip = $proc['__ip'];
        $geo = geolocalizarIp($ip, $geoCache);
      ?>
        <tr>
          <td><?= $proc['Id'] ?></td>
          <td><?= $proc['User'] ?></td>
          <td title="<?= $geo ?>">
            <a href="?ip=<?= $ip ?>"><?= $ip ?></a>
          </td>
          <td><?= $proc['Host'] ?></td>
          <td><?= $proc['db'] ?></td>
          <td><?= $proc['Command'] ?></td>
          <td class="<?= intval($proc['Time']) > 60 ? 'tiempo-alto' : '' ?>">
            <?= $proc['Time'] ?>
          </td>
          <td><?= $proc['State'] ?></td>
          <td title="<?= htmlspecialchars($proc['Info'] ?? '') ?>">
            <?= htmlspecialchars(mb_strimwidth($proc['Info'] ?? '', 0, 80, '...')) ?>
          </td>
          <td>
            <?php if (strtolower($proc['Command']) === 'sleep'): ?>
              <button class="btn-kill" data-id="<?= $proc['Id'] ?>">💀 KILL</button>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="div-sadmin-buttons">
    <a href="process_export.php" target="_blank" title="Exportar procesos">
      📥 Exportar
    </a>
    <a href="process_historial.php" target="_blank" title="Gráfico historial">
      📈 Historial
    </a>
  </div>
  <div class="div-sadmin-buttons">
    <button id="btnRecargar">🔄 Ver todos</button>
    <button id="btnSleep">🛌 Solo Sleep prolongados</button>
    <button id="btnKillMasivo">💥 KILL todos los Sleep > 60s</button>
    <button id="btnCerrar">🚪 Cerrar</button>
  </div>

  <?php if (count($ipCounts) > 1): ?>
    <h3>📊 Conexiones por IP</h3>
    <table>
      <thead>
        <tr>
          <th>IP</th>
          <th>Conexiones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ipCounts as $ip => $count): ?>
          <tr class="<?= $count > $umbral ? 'alerta-ip' : '' ?>">
            <td><a href="?ip=<?= $ip ?>"><?= $ip ?></a></td>
            <td><?= $count ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>