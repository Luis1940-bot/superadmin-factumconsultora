<?php
session_start();
require_once dirname(__DIR__) . '/private/config/config.php';
/** @var string $baseUrl */
$baseUrl = BASE_URL;

if (!isset($_SESSION['superadmin_authenticated'])) {
  header('Location: index.php');
  exit;
}

if (!isset($_SESSION['selected_client_id'])) {
  header('Location: select-client.php');
  exit;
}

$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/css/dashboard.css?v=" . time();
$jsUrl = BASE_URL . "/js/hacker-login.js?v=" . time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Dashboard - <?= htmlspecialchars($cliente) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon">
</head>

<body>
  <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
  <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
  ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  <div class="button-group">
    <div class="div-sadmin-buttons" id="div-sadmin-buttons">
      <!-- Botones dinámicos se insertarán aquí -->
    </div>

    <!-- Botones estáticos -->
    <form action="logout.php" method="post">
      <button type="submit" class="button-selector-sadmin">🚪 Cerrar sesión</button>
    </form>

    <form action="select-client.php" method="post">
      <button type="submit" class="button-selector-sadmin">🔄 Cambiar cliente</button>
    </form>


  </div>

  <script src="js/dashboard.js?v=<?= time(); ?>" type="module"></script>


</body>

</html>