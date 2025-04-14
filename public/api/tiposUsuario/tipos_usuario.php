<?php
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

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

$query = "SELECT t.idtipousuario, t.tipo, t.detalle FROM tipousuario t ORDER BY t.idtipousuario;";
$result = $mysqli->query($query);

$cssUrl = BASE_URL . "/api/tiposUsuario/tipos_usuario.css?v=" . time();
$jsUrl = BASE_URL . "/api/tiposUsuario/tipos_usuario.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Tipos de Usuario</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <h1>📋 Tipos de Usuario</h1>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Detalle</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['idtipousuario'] ?></td>
          <td><?= htmlspecialchars($row['tipo']) ?></td>
          <td><?= htmlspecialchars($row['detalle']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="div-sadmin-buttons">
    <button id="btnRecargar">🔄 Recargar</button>
    <button id="btnCerrar">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>