<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
/** @var string $baseDir */
$baseDir = BASE_DIR;

require_once dirname(__DIR__) . '/config/auth_token.php';

$favicon = BASE_URL . "/img/favicon.ico";
$cssUrl = BASE_URL . "/css/hacker-style.css?v=" . time();
$jsUrl = BASE_URL . "/js/hacker-login.js?v=" . time();
// Si ya está logueado
if (isset($_SESSION['superadmin_authenticated']) && $_SESSION['superadmin_authenticated'] === true) {
  // ¿Ya seleccionó cliente?
  if (isset($_SESSION['selected_client_id'])) {
    header('Location: dashboard.php');
    exit;
  } else {
    header('Location: select-client.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>SuperAdmin Access - Factum</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?= $cssUrl ?>" nonce="<?= $nonce ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon">
</head>

<body>
  <div id="console">
    <pre class="ascii-banner">
  ______      _____ _______ _    _ __  __ 
 |  ____/\   / ____|__   __| |  | |  \/  |
 | |__ /  \ | |       | |  | |  | | \  / |
 |  __/ /\ \| |       | |  | |  | | |\/| |
 | | / ____ \ |____   | |  | |__| | |  | |
 |_|/_/    \_\_____|  |_|   \____/|_|  |_|
                                                       
               
    SuperAdmin Console - Factum

    🧑‍💼 admin: <?php echo $emailAdmin; ?>  
    📆 creado: <?php echo $creadoEn; ?>
    </pre>
    <p class="access-info">🔒 Acceso restringido - token requerido</p>
    <div id="log">🔐 Ingresá tu clave:</div>
    <br />
    <input type="password" id="inputField" autofocus placeholder="Token secreto..." />
  </div>

  <script src="js/hacker-login.js?v=<?= time(); ?>" type="module"></script>
</body>

</html>