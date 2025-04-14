<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Dashboard - <?= htmlspecialchars($cliente) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
  <link rel='shortcut icon' type='image/x-icon' href='<?php echo $baseUrl ?>/img/favicon.ico'>
</head>

<body>
  <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
  <p id="cliente-id" data-id="<?= $clienteId ?>">🔍 Herramientas activas para la organización ID: <?= $clienteId ?></p>
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

  <script type="module" src="js/dashboard.js?v=<?= time(); ?>"></script>


</body>

</html>