<?php
session_start();
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));

// Headers de seguridad
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce'; connect-src *; object-src 'none';");
header("Content-Security-Policy: default-src 'self'; connect-src 'self' https://factumconsultora.com; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce';");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
// header("X-Content-Type-Options: nosniff");
// header("X-Frame-Options: DENY");
// header("X-XSS-Protection: 1; mode=block");
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Access-Control-Allow-Credentials: true");

require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
$defaultJsonPath = $baseDir . '/config/app.json';

$id = $_GET['id'] ?? null;
$planta = null;
if (preg_match('/mc(\d+)000/', $id, $matches)) {
  $planta = (int)$matches[1]; // Te da 1 si es mc1000, 2 si mc2000, etc.
} else {
  echo "No se encontró nada, como en tu último intento de cocinar.";
}

$cliente = $_GET['cliente'] ?? null;
$jsonPathParam = $_GET['jsonPath'] ?? null;
$name = $_GET['name'] ?? null;
$remoteJsonUrl = null;

// 1. Si jsonPath viene vacío o null => config/app.json
if ($jsonPathParam === null || $jsonPathParam === '' || $jsonPathParam === 'null') {
  $jsonPathFinal = $defaultJsonPath;
  $data = json_decode(file_get_contents($jsonPathFinal), true);
} else {
  // 2. Si jsonPath tiene valor, es remoto
  $jsonPathParam = trim($jsonPathParam);
  $jsonPathParam = preg_replace('/[\r\n]+/', '', $jsonPathParam);
  // $relative = str_replace('xxx', $planta, $jsonPathParam);
  // $encodedPath = rawurlencode(ltrim($relative, '/'));
  $relative = str_replace('xxx', $planta, $jsonPathParam);
  $encodedPath = ltrim($relative, '/'); // no rawurlencode


  $isLocal = preg_match('/localhost|127\.0\.0\.1/i', $_SERVER['HTTP_HOST']);
  $remoteJsonUrl = $isLocal
    ? 'http://localhost:8000/api/proxy.php'
    : 'https://sadmin.factumconsultora.com/api/proxy.php';
  // $proxyBase = 'https://sadmin.factumconsultora.com/api/proxy.php';
  $proxyUrl = $remoteJsonUrl . '?file=' . $encodedPath;

  $jsonData = @file_get_contents($proxyUrl);


  $data = $jsonData ? json_decode($jsonData, true) : ['error' => 'No se pudo cargar JSON remoto.'];


  if ($jsonData === false) {
    $error = error_get_last();
    error_log("🔥 ERROR: " . $error['message']);
    $data = ['error' => 'No se pudo cargar JSON remoto.'];
  } else {
    $data = json_decode($jsonData, true);
  }


  $jsonPathFinal = "https://factumconsultora.com/scg-mccain{$relative}"; // Este es el *original* visible

}

$favicon = '/img/favicon.ico';
$cssUrl = '/api/editorJson/editor_json.css?v=' . time();
$jsUrl = '/api/editorJson/editor_json.js?v=' . time();
$cssPrompt = '/js/modules/ui/prompt.css?v=' . time();
$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>🧠 Editor de App JSON</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>" nonce="<?= $nonce ?>">
  <link rel="stylesheet" href="<?= $cssPrompt ?>" nonce="<?= $nonce ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>
  <h1>🧠 Editor Visual de App JSON</h1>

  <div class="div-sadmin-buttons">
    <button class="button-selector-sadmin" id="btnGuardar">💾 Guardar</button>
    <button class="button-selector-sadmin" id="btnAgregarBloque">➕ Nuevo Bloque</button>
    <button class="button-selector-sadmin" id="btnAgregarClave">➕ Clave a Todos</button>
    <button class="button-selector-sadmin" id="btnRecargar">🔄 Recargar</button>
    <button class="button-selector-sadmin" id="btnCerrar">🚪 Cerrar</button>
  </div>

  <div class="json-info-panel">
    <strong>Cliente:</strong> <?= isset($cliente) ? htmlspecialchars($cliente) : 'Desconocido' ?> |
    <strong>ID:</strong> <?= isset($id) ? htmlspecialchars($id) : 'N/A' ?> |
    <strong>Botón:</strong> <?= isset($name) ? htmlspecialchars($name) : 'Desconocido' ?> |
    <strong>Ruta:</strong>
    <?php
    if (!empty($jsonPathFinal) && strpos($jsonPathFinal, 'https://') === 0): ?>
      <a href="<?= htmlspecialchars($jsonPathFinal) ?>" target="_blank" rel="noopener noreferrer">
        <?= htmlspecialchars($jsonPathFinal) ?>
      </a>
    <?php else: ?>
      <?= htmlspecialchars($jsonPathFinal ?? 'No definida') ?>
    <?php endif; ?>
  </div>






  <div
    id="jsonContainer"
    data-json='<?= htmlspecialchars(json_encode($data), ENT_QUOTES) ?>'
    data-jsonpath="<?= htmlspecialchars($jsonPathFinal ?? '', ENT_QUOTES) ?>"
    data-isremote="<?= $remoteJsonUrl ? 'true' : 'false' ?>"
    data-remoteurl="<?= htmlspecialchars($proxyUrl ?? '', ENT_QUOTES) ?>"></div>

  <button id="scrollToTopBtn" title="Volver arriba">⬆️</button>
  <div id="jsonSearchBox" class="floating-search-box">
    <input type="text" id="searchInput" placeholder="🔍 Buscar..." />
    <span id="searchCounter" class="search-counter">0 de 0</span>

    <button id="prevMatch" title="Anterior">⬆️</button>
    <button id="nextMatch" title="Siguiente">⬇️</button>
    <button id="clearSearch" title="Limpiar">❌</button>
  </div>



  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>


</body>

</html>