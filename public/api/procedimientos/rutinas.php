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

$dbName = $dbname;
$tipo = $_GET['tipo'] ?? 'PROCEDURE';
$tipoLabel = $tipo === 'FUNCTION' ? 'Funciones' : 'Procedimientos';
$buscar = $_GET['buscar'] ?? '';
$buscarCond = $buscar ? "AND SPECIFIC_NAME LIKE '%$buscar%'" : '';

// QUERY ACTUALIZADA con COMMENT y DEFINER
$query = "
  SELECT SPECIFIC_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, CREATED, LAST_ALTERED, DEFINER, ROUTINE_COMMENT
  FROM information_schema.ROUTINES
  WHERE ROUTINE_TYPE = '$tipo' AND ROUTINE_SCHEMA = '$dbName'
  $buscarCond
  ORDER BY SPECIFIC_NAME ASC;
";

$result = $mysqli->query($query);

$cssUrl = BASE_URL . "/api/procedimientos/rutinas.css?v=" . time();
$jsUrl = BASE_URL . "/api/procedimientos/rutinas.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title><?= $tipoLabel ?> MySQL</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <h1>⚙️ <?= $tipoLabel ?> en <?= htmlspecialchars($dbName) ?></h1>

  <div class="div-sadmin-buttons">
    <a href="catalogo_api.php" target="_blank">
      <button class="button-selector-sadmin">📚 Ver Catálogo de API</button>
    </a>
    <a href="editor_rutina.php" target="_blank">
      <button class="button-selector-sadmin">🧪 Editor Visual (Tipo Postman)</button>
    </a>
    <a href="?tipo=PROCEDURE"><button>🧱 Ver Procedimientos</button></a>
    <a href="?tipo=FUNCTION"><button>🔧 Ver Funciones</button></a>
  </div>

  <div class="form-busqueda">
    <input type="text" id="filtroRutinas" placeholder="🔍 Filtrar rutinas por nombre...">
  </div>

  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Retorno</th>
        <th>Definido por</th>
        <th>Comentario</th>
        <th>Creado</th>
        <th>Modificado</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['SPECIFIC_NAME']) ?></td>
          <td><?= $row['ROUTINE_TYPE'] ?></td>
          <td><?= $row['DTD_IDENTIFIER'] ?></td>
          <td><?= htmlspecialchars($row['DEFINER']) ?></td>
          <td title="<?= htmlspecialchars($row['ROUTINE_COMMENT']) ?>">
            <?= mb_strimwidth(htmlspecialchars($row['ROUTINE_COMMENT']), 0, 40, '...') ?>
          </td>
          <td><?= $row['CREATED'] ?></td>
          <td><?= $row['LAST_ALTERED'] ?></td>
          <td>
            <a class="btn-ver" href="ver_rutina.php?tipo=<?= $row['ROUTINE_TYPE'] ?>&id=<?= urlencode($row['SPECIFIC_NAME']) ?>" target="_blank">
              👁 Ver
            </a>
            <button class="btn-parametros" data-id="<?= htmlspecialchars($row['SPECIFIC_NAME']) ?>">📋 Parámetros</button>
            <button class="btn-test" data-id="<?= htmlspecialchars($row['SPECIFIC_NAME']) ?>" data-tipo="<?= $row['ROUTINE_TYPE'] ?>">🧪 Ejecutar</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="div-sadmin-buttons">
    <button id="btnRecargar">🔄 Recargar</button>
    <button id="btnCerrar">🚪 Cerrar</button>
  </div>

  <!-- Modal Parámetros -->
  <div id="modalParametros" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModalParametros">&times;</span>
      <h2 id="paramModalTitulo"></h2>
      <div id="paramModalCuerpo"></div>
    </div>
  </div>

  <!-- Modal Ejecutar -->
  <div id="modalTest" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModalTest">&times;</span>
      <h2 id="testModalTitulo"></h2>
      <div id="testModalCuerpo"></div>
      <div id="testResultado" class="margen-top">></div>
    </div>
  </div>

  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
</body>

</html>