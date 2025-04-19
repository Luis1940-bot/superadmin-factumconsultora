<?php
session_start();
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
include_once $baseDir . "/config/datos_base.php";
$charset = "utf8mb4";
$dbname = $_GET['id'] ?? null;
$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/api/corregirRegistros/get_records.css?v=" . time();
$jsUrl = BASE_URL . "/api/corregirRegistros/get_records.js?v=" . time();
$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Actualizar Registros</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>
  <h2>Registros a Actualizar</h2>
  <table id="recordsTable">
    <thead>
      <tr>
        <th>NuxPedido</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>ID Usuario</th>
        <th>ID Reporte</th>
        <th>ID Cliente Reporte</th>
        <th>ID Cliente Registro</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
  <div id="mensajeRegistros"></div>

  <div class="div-sadmin-buttons">
    <button id="updateButton">Corregir Registros</button>
    <button class="button-selector-sadmin" id="recargarBtn">🔄 Recargar</button>
    <button class="button-selector-sadmin" id="cerrarBtn">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script nonce="<?= $nonce ?>" src="<?= $jsUrl ?>" type="module"></script>
</body>

</html>