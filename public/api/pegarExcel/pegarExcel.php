<?php
session_start();
header('Content-Type: text/html;charset=utf-8');

// Seguridad (CSP, HSTS, etc.)
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

// Config & Logging
require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
require_once dirname(__DIR__, 3) . '/private/config/config.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');

// URLs
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$cssUrl = BASE_URL . "/api/pegarExcel/pegarExcel.css?v=" . time();
$jsUrl = BASE_URL . "/api/pegarExcel/pegarExcel.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
$crypto = BASE_URL . "/api/pegarExcel/crypto-js.min.js?v=" . time();

// Cliente desde sesión
$cliente = $_SESSION['selected_client_name'] ?? 'Desconocido';
$clienteId = $_SESSION['selected_client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pegar desde Excel</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>

  <div class="container">
    <h2>📋 Pegar Datos desde Excel</h2>
    <form id="reporteForm">
      <label for="idLTYreporte">Ingrese ID del Reporte:</label>
      <input type="number" id="idLTYreporte" name="idLTYreporte" />
      <button type="submit" class="btn">Buscar</button>
    </form>

    <h3>📑 Reporte: <span id="reporteNombre">-</span></h3>

    <div class="cliente-info">
      <!-- <h3 id="idCliente">ID Cliente: <span>-</span></h3> -->
      <!-- <h3 id="nombreCliente">Cliente: <span>-</span></h3> -->
    </div>

    <h4>🔢 Última Observación: <span id="ultimoOrden">-</span></h4>

    <h3>📚 Registros Existentes</h3>
    <table id="tablaExistente">
      <thead>
        <tr>
          <th>ID Control</th>
          <th>Control</th>
          <th>Detalle</th>
          <th>Tipo Dato</th>
          <th>Tp Observa</th>
          <th>Orden</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <h3>🆕 Nuevos Datos</h3>
    <div class="flex-container">
      <textarea id="campoInput" placeholder="Campos"></textarea>
      <textarea id="detalleInput" placeholder="Detalles"></textarea>
      <textarea id="tipoDatoInput" placeholder="Tipo Dato"></textarea>
      <textarea id="tpObservaInput" placeholder="Tp Observa"></textarea>
    </div>

    <button class="btn" id="procesarBtn">Procesar</button>
    <button class="btn" id="limpiarBtn">Limpiar</button>

    <h3>✅ Datos Procesados</h3>
    <table id="dataTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Campo</th>
          <th>Detalle</th>
          <th>Tipo Dato</th>
          <th>Tp Observa</th>
          <th>Orden</th>
          <th>Código</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <hr>
    <button class="btn" id="guardarBtn">Guardar en Base de Datos</button>
  </div>

  <div class="div-sadmin-buttons">
    <button type="button" id="cerrarBtn" class="button-selector-sadmin">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>">
    window.baseCliente = "<?= "mc" . $clienteId . "000" ?>";
  </script>
  <script src="<?= $crypto ?>"></script>
  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
  <script nonce="<?= $nonce ?>">
    document.addEventListener('DOMContentLoaded', () => {
      const cerrarBtn = document.getElementById('cerrarBtn');
      if (cerrarBtn) {
        cerrarBtn.addEventListener('click', () => window.close());
      }
    });
  </script>
</body>

</html>