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

require_once dirname(dirname(dirname(__DIR__))) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/logs/logs/error.log');
require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
/** @var string $baseDir */
$baseDir = BASE_DIR;
$cliente = $_GET['cliente'] ?? '';
$clienteId = $_GET['id'] ?? '';
/** 
 * @var array{timezone?: string} $_SESSION 
 */
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}



?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Factum | Autorizar Email</title>

  <link rel="stylesheet" href="<?php echo BASE_URL ?>/api/AuthUser/auth.css?v=<?php echo time(); ?>">
  <link rel="icon" href="<?php echo BASE_URL ?>/img/favicon.ico" type="image/x-icon" />
</head>

<body>
  <main>
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">
      📧 Autorizar Correo - <?= htmlspecialchars($cliente) ?>
    </h1>
    <p id="cliente-id" data-id="<?= htmlspecialchars($clienteId) ?>">
      ID Cliente | ID: <?= htmlspecialchars($clienteId) ?>
    </p>
    <p>Ingresá el email para validar el acceso</p>
    <div class=" div-sadmin-buttons">
      <div class="auth-group">
        <input type="email" id="input-email" class="input-email" placeholder="email@dominio.com" />
        <button id="btn-checar" class="button-selector-sadmin">Autorizar</button>
      </div>
      <p id="mensaje-validacion" class="mensaje-resultado"></p>
      <div class="auth-group">
        <button type="button" id="btn-cerrar" class="button-selector-sadmin">❌ Cerrar</button>
      </div>


    </div>
  </main>

  <script type="module" src="<?php echo BASE_URL; ?>/api/AuthUser/auth.js?v=<?php echo time(); ?>"></script>
</body>

</html>