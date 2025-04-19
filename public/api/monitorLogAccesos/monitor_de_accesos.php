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
$cssUrl = BASE_URL . "/api/monitorLogAccesos/monitor_de_accesos.css?v=" . time();
$jsUrl = BASE_URL . "/api/monitorLogAccesos/monitor_de_accesos.js?v=" . time();

// Obtener parámetros
$cliente = $_GET['cliente'] ?? 'Desconocido';
$idCliente = $_GET['id'] ?? null;

if (!$idCliente) {
  die('Falta el parámetro "id".');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>👁️ Monitor de Accesos</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>" nonce="<?= $nonce ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon">
</head>

<body>
  <h1>👁️ Monitor de Accesos</h1>

  <div class="info-panel">
    <strong>Cliente:</strong> <?= htmlspecialchars($cliente) ?> |
    <strong>ID:</strong> <?= htmlspecialchars($idCliente) ?>
  </div>


  <div class="date-inputs">
    <label>Desde:
      <input type="date" id="desdeInput" />
    </label>
    <label>Hasta:
      <input type="date" id="hastaInput" />
    </label>
  </div>
  <div class="controls">
    <button class="btn" id="btnRecargar">🔄 Recargar</button>
    <button class="btn" id="btnCerrar">🚪 Cerrar</button>
  </div>

  <div id="chartContainer" data-idcliente="<?= $idCliente ?>"></div>
  <div id="chartLinea"></div>
  <div id="chartDensidad"></div>
  <div id="chartHeatmap"></div>
  <div id="chartDistribucion"></div>
  <table id="tablaAccesos"></table>
  <div id="tablaSinIngresos" style="margin-top: 2rem;"></div>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>