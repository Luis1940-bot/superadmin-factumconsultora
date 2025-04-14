<?php
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: https://tenkiweb.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$cliente = $_GET['cliente'] ?? 'Desconocido';
$id = $_GET['id'] ?? 0;

$cssUrl = BASE_URL . "/api/ordenar/ordenar.css?v=" . time();
$jsUrl = BASE_URL . "/api/ordenar/ordenar.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
$cssPrompt = '/js/modules/ui/prompt.css?v=' . time();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>📋 Ordenar Control</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>" nonce="<?= $nonce ?>">
  <link rel="stylesheet" href="<?= $cssPrompt ?>" nonce="<?= $nonce ?>">
  <link rel="icon" href="<?= $favicon ?>" />
</head>

<body>
  <h1>🎛️ Panel de Reportes</h1>
  <p>Seleccione un reporte para visualizar sus controles</p>

  <div class="select-wrapper">
    <select id="selectReporte">
      <option value="">-- Seleccionar Reporte --</option>
    </select>
  </div>

  <div class="div-sadmin-buttons">
    <button class="button-selector-sadmin" id="btnRecargar">🔁 Recargar</button>
    <button class="button-selector-sadmin" id="btnSalir">🚪 Cerrar</button>
  </div>

  <table id="dataTable">
    <thead>
      <tr>
        <th>ID Control</th>
        <th>Control</th>
        <th>Nombre</th>
        <th>Detalle</th>
        <th>Orden</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>