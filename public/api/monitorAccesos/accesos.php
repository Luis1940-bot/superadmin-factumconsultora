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

$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/api/monitorAccesos/accesos.css?v=" . time();
$jsUrl = BASE_URL . "/api/monitorAccesos/accesos.js?v=" . time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>📋 Accesos al Sistema</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>" />
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <h1>🔐 Accesos al Sistema</h1>
  <input type="text" id="searchInput" placeholder="Buscar por IP, email o planta" />

  <div class="floating-buttons">
    <a href="export_accesos.php?format=csv" target="_blank">📥 Exportar</a>
    <button id="recargarBtn">🔄 Recargar</button>
    <button id="cerrarBtn">🚪 Cerrar</button>
  </div>

  <div class="chart-container">
    <canvas id="chartAccesos"></canvas>
  </div>
  <h2>🚨 Posibles Ataques por Fuerza Bruta</h2>
  <table id="fuerzaBrutaTable">
    <thead>
      <tr>
        <th>🌐 IP</th>
        <th>📧 Email</th>
        <th>❌ Fallos</th>
        <th>🕒 Último Intento</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
  <div id="bloqueAtaques" class="ataques-detectados"></div>
  <table id="accesosTable">
    <thead>
      <tr>
        <th>📆 Fecha</th>
        <th>📧 Email</th>
        <th>🏭 Planta</th>
        <th>🌐 IP</th>
        <th>🌎 Geo</th>
        <th>🧠 Navegador</th>
        <th>✅ Estado</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
  <script nonce="<?= $nonce ?>" src="../../lib/chart.js"></script>

</body>

</html>