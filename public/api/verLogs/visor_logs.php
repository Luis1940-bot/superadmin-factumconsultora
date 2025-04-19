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
require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/private/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/private/config/datos_base.php";

$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/api/verLogs/visor_logs.css?v=" . time();
$jsUrl = BASE_URL . "/api/verLogs/visor_logs.js?v=" . time();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Visor de Logs</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <h2>🧾 Últimos errores registrados</h2>
  <div id="mensajeRegistros" class="warning"></div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Mensaje de Error</th>
      </tr>
    </thead>
    <tbody id="logTableBody">
      <!-- Se llenará desde JS -->
    </tbody>
  </table>

  <div class="div-sadmin-buttons">
    <button class="button-selector-sadmin" id="recargarBtn">🔄 Recargar</button>
    <button class="button-selector-sadmin" id="cerrarBtn">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>