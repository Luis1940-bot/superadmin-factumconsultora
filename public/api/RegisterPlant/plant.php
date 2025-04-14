<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) {
  session_start();
};
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https: example.com; script-src 'self' 'nonce-$nonce' cdn.example.com; style-src 'self' 'nonce-$nonce' cdn.example.com; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;");


header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

header("Access-Control-Allow-Origin: https://tenkiweb.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once dirname(dirname(dirname(__DIR__))) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/logs/error.log');
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
  <title>Registro de Planta</title>
  <link rel="stylesheet" href="<?php echo BASE_URL ?>/api/RegisterPlant/plant.css?v=<?= time(); ?>" />
  <link rel="icon" href="<?php echo BASE_URL ?>/img/favicon.ico" type="image/x-icon" />
</head>

<body>
  <main>
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">
      NUEVO CLIENTE<?= htmlspecialchars($cliente) ?>
    </h1>
    <p id="cliente-id" data-id="<?= htmlspecialchars($clienteId) ?>">
      ID Cliente | ID: <?= htmlspecialchars($clienteId) ?>
    </p>
    <p>Completa los datos de un NUEVO CLIENTE</p>
    <div class="div-plant">
      <label for="id" class="label-plant">ID</label>
      <input id="id" type="text" class="input-plant" disabled>

      <label for="cliente" class="label-plant">Compañía-Sitio</label>
      <input id="cliente" type="text" class="input-plant">
      <span class="span-plant">Campo obligatorio.</span>

      <label for="detalle" class="label-plant">Detalle</label>
      <textarea id="detalle" class="textarea-plant" rows="3" cols="50"></textarea>

      <label for="contacto" class="label-plant">Contacto</label>
      <input id="contacto" type="text" class="input-plant">
      <span class="span-plant">Campo obligatorio.</span>

      <label for="email" class="label-plant">Email de contacto</label>
      <input id="email" type="email" class="input-plant">
      <span class="span-plant">Campo obligatorio.</span>

      <label for="situacion" class="label-plant">Situación</label>
      <select id="situacion" class="select-plant" disabled>
        <option value=""></option>
        <option value="s" selected>Activo</option>
        <option value="n">No activo</option>
      </select>

      <label for="logo" class="label-plant">Logo (PNG)</label>
      <button id="idLogo" class="button-logo">Seleccionar Logo</button>
      <span id="idSpanLogo" class="span-logo"></span>
      <input id="logo" type="file" name="logo" class="input-file" accept="image/png">
      <img id="idImgLogo" class="thumbnail" src="#" alt="Sin logo.">

      <button id="idRegisterButton" class="button-plant">Registrar</button>
    </div>
  </main>
  <!-- <script type="module" src="<?= BASE_URL ?>/api/RegisterPlant/Controllers/nuevaCompania.js?v=<?= time(); ?>"></script> -->
  <script type="module" src="<?= BASE_URL ?>/api/RegisterPlant/plant.js?v=<?= time(); ?>"></script>
</body>

</html>