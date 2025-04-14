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

require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$charset = "utf8mb4";
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die('❌ Error de conexión a la base de datos.');
}
mysqli_set_charset($mysqli, "utf8mb4");

$sql = "SELECT idLTYregistrocontrol, fecha, nuxpedido, idusuario, idLTYreporte, horaautomatica, supervisor, observacion, imagenes, idLTYcliente, hora, newJSON
FROM LTYregistrocontrol
ORDER BY horaautomatica DESC
LIMIT 50;
";
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
          <td><?= htmlspecialchars($row['idLTYcliente']) ?></td>
          <td><?= htmlspecialchars($row['hora']) ?></td>
          <td>
            <div class="json-wrapper">
              <span class="json-toggle">➕ newJSON</span>
              <div class="json-container hidden" data-json='<?= htmlspecialchars($row['newJSON'], ENT_QUOTES) ?>'></div>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>


  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>