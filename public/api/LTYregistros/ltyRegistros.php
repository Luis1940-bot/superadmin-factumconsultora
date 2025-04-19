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

require_once dirname(__DIR__, 3) . '/private/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
$charset = "utf8mb4";
$dbname = $_GET['id'] ?? null;
$allowedDbs = ['mc1000', 'mc2000', 'mc3000', 'mc4000', 'mc5000', 'mc6000'];
if (!in_array($dbname, $allowedDbs)) {
  die("❌ Base de datos no permitida.");
}
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die('❌ Error de conexión a la base de datos.');
}
mysqli_set_charset($mysqli, "utf8mb4");

$sql = "SELECT 
  lt.idLTYregistrocontrol, lt.fecha, lt.nuxpedido, lt.idusuario, lt.idLTYreporte,
  lt.horaautomatica, lt.supervisor, lt.observacion, lt.imagenes, 'mc1000' AS base, lt.objJSON
FROM {$dbname}.LTYregistrocontrol lt
WHERE lt.objJSON IS NOT NULL AND lt.objJSON != ''
ORDER BY lt.horaautomatica DESC
LIMIT 50;";

$result = $mysqli->query($sql);

$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/api/LTYregistros/ltyRegistros.css?v=" . time();
$jsUrl = BASE_URL . "/api/LTYregistros/ltyRegistros.js?v=" . time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Últimos Registros de Control</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>
  <h1>🧾 Últimos 50 Registros de LTYregistrocontrol</h1>
  <input type="text" id="registroSearch" placeholder="🔍 Buscar por Pedido" />

  <div class="div-sadmin-buttons">
    <button class="button-selector-sadmin" id="recargarBtn">🔄 Recargar</button>
    <button class="button-selector-sadmin" id="cerrarBtn">🚪 Cerrar</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>NuxPedido</th>
        <th>ID Usuario</th>
        <th>ID Reporte</th>
        <th>Hora Automática</th>
        <th>Supervisor</th>
        <th>Observación</th>
        <th>Imágenes</th>
        <th>ID Cliente</th>
        <th>Hora Manual</th>
        <th>newJSON</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['idLTYregistrocontrol']) ?></td>
          <td><?= htmlspecialchars($row['fecha']) ?></td>
          <td><?= htmlspecialchars($row['nuxpedido']) ?></td>
          <td><?= htmlspecialchars($row['idusuario']) ?></td>
          <td><?= htmlspecialchars($row['idLTYreporte']) ?></td>
          <td><?= htmlspecialchars($row['horaautomatica']) ?></td>
          <td><?= htmlspecialchars($row['supervisor']) ?></td>
          <td><?= htmlspecialchars($row['observacion']) ?></td>
          <td><?= htmlspecialchars($row['imagenes']) ?></td>
          <td><?= htmlspecialchars($dbname) ?></td>
          <td><?= htmlspecialchars($row['hora']) ?></td>
          <td>
            <div class="json-wrapper">
              <span class="json-toggle">➕ newJSON</span>
              <div class="json-container hidden" data-json='<?= htmlspecialchars($row['objJSON'], ENT_QUOTES) ?>'></div>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>


  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>