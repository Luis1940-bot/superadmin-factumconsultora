<?php
session_start();

// ✅ Verificación de autenticación por token
if (!isset($_SESSION['superadmin_authenticated']) || $_SESSION['superadmin_authenticated'] !== true) {
  header('HTTP/1.1 403 Forbidden');
  echo '⛔️ Acceso denegado. Necesitás autenticarte.';
  exit;
}
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
include_once $baseDir . "/config/datos_base.php";
$baseDir = BASE_DIR;

// Ruta del flag
$flagPath = '/.reload-flag';

$mensaje = '';
$estado = file_exists($flagPath) ? 'ACTIVO' : 'INACTIVO';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['forzar'])) {
    file_put_contents($flagPath, 'reload');
    $mensaje = '✅ Recarga forzada activada.';
    $estado = 'ACTIVO';
  }

  if (isset($_POST['desactivar'])) {
    if (file_exists($flagPath)) {
      unlink($flagPath);
      $mensaje = '⚪️ Recarga forzada desactivada.';
      $estado = 'INACTIVO';
    } else {
      $mensaje = '🚫 No había recarga activa.';
    }
  }
}
$cssUrl = BASE_URL . "/api/recargaForzada/recarga-control.css?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Control de Recarga Remota</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
  <!-- <style>
    body {
      font-family: sans-serif;
      padding: 2rem;
      background-color: #f4f4f4;
    }

    h1 {
      color: #333;
    }

    .estado {
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    .boton {
      padding: 10px 20px;
      font-size: 16px;
      margin-right: 10px;
      border: none;
      cursor: pointer;
      border-radius: 5px;
      color: white;
    }

    .forzar {
      background-color: #e53935;
    }

    .desactivar {
      background-color: #555;
    }

    .mensaje {
      margin-top: 1rem;
      font-weight: bold;
      color: green;
    }
  </style> -->
</head>

<body>

  <h1>🛠 Panel de Recarga Remota</h1>
  <div class="estado">🔄 Estado actual: <strong><?= $estado ?></strong></div>

  <form method="post">
    <button class="boton forzar" type="submit" name="forzar">🔴 Forzar Recarga</button>
    <button class="boton desactivar" type="submit" name="desactivar">⚪️ Desactivar Recarga</button>
  </form>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

</body>

</html>