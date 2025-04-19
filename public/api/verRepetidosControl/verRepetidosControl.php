<?php
session_start();

// Headers generales
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));

header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: https://factumconsultora.com");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Logs & Config
require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/private/config/config.php';

// Variables
$inputData = json_decode(file_get_contents('php://input'), true);
$dbname = $_GET['id'] ?? ($inputData['id'] ?? null);

// Si es POST: API response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json; charset=utf-8');

  if (!$dbname) {
    echo json_encode(['success' => false, 'message' => 'Falta ID de base']);
    exit;
  }

  $baseDir = BASE_DIR;
  include_once $baseDir . "/config/datos_base.php";

  $mysqli = new mysqli($host, $user, $password, $dbname, $port);
  if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
  }

  mysqli_set_charset($mysqli, "utf8mb4");

  $sql = "
    SELECT c.control, c.idLTYcontrol, c.idLTYreporte, c.orden, r.nombre AS nombre_reporte
    FROM LTYcontrol c
    INNER JOIN LTYreporte r ON c.idLTYreporte = r.idLTYreporte
    WHERE c.control IN (
      SELECT control FROM LTYcontrol 
      GROUP BY control HAVING COUNT(*) > 1
    )
    ORDER BY c.idLTYreporte, c.orden, c.idLTYcontrol ASC";

  $datos = [];
  $result = $mysqli->query($sql);
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $datos[] = $row;
    }
  }

  $mysqli->close();
  echo json_encode(['success' => true, 'data' => $datos]);
  exit;
}

// Si es GET: HTML
$cssUrl = BASE_URL . "/api/verRepetidosControl/verRepetidosControl.css?v=" . time();
$jsUrl = "/api/verRepetidosControl/verRepetidosControl.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
$cliente = $_SESSION['selected_client_name'] ?? 'Desconocido';
$clienteId = $_SESSION['selected_client_id'] ?? '0';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registros Repetidos</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>

  <h2>🧠 Estamos rastreando códigos en la tabla <code>LTYcontrol</code> que puedan estar duplicados...</h2>

  <div class="acciones-panel">
    <button id="btnCargar" class="button-selector-sadmin">🔄 Cargar registros repetidos</button>
  </div>

  <input type="text" id="searchInput" placeholder="Buscar por ID LTYreporte">

  <table id="dataTable">
    <thead>
      <tr>
        <th>Control</th>
        <th>ID LTYcontrol</th>
        <th>ID LTYreporte</th>
        <th>Nombre Reporte</th>
        <th>Orden</th>
        <th>Nuevo Código</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <!-- Los datos serán insertados por JavaScript -->
    </tbody>
  </table>

  <div class="div-sadmin-buttons">
    <button class="button-selector-sadmin" id="cerrarBtn">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>" src="<?= $jsUrl ?>" type="module"></script>
  <script src="<?= BASE_URL ?>/api/pegarExcel/crypto-js.min.js"></script>
</body>

</html>