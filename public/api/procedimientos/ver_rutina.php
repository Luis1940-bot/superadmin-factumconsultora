<?php
session_start();
header('Content-Type: text/html;charset=utf-8');
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self';");

require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
$dbname = $_GET['dbName'] ?? null;

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die("Error de conexión a la base de datos.");
}
mysqli_set_charset($mysqli, "utf8mb4");

$procName = $_GET['id'] ?? '';
$procName = $mysqli->real_escape_string($procName);
$cliente = $_SESSION['selected_client_name'];
$clienteId = $_SESSION['selected_client_id'];
$tipo = $_GET['tipo'] ?? 'PROCEDURE';

$result = $mysqli->query("SHOW CREATE $tipo `$procName`");

if (!$result || $result->num_rows === 0) {
  die("❌ No se encontró el procedimiento.");
}
$data = $result->fetch_assoc();
$createSQL = $data['Create Procedure'];
?>
<?php
// ... (lo de siempre arriba)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Ver <?= $tipo ?> <?= htmlspecialchars($procName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/api/procedimientos/rutinas.css?v=<?= time() ?>">
</head>

<body>
  <div class="datos-cabecera">
    <h1 id="cliente-nombre" data-cliente="<?= htmlspecialchars($cliente) ?>">🎛️ Panel de <?= htmlspecialchars($cliente) ?></h1>
    <p id="cliente-id" data-id="<?= "mc" . $clienteId . "000" ?>">🔍 Herramientas activas para la base ID: <?= "mc" . $clienteId . "000" ?></p>
    ⚙️ Factum Admin Panel - v1.0 © <?= date('Y') ?>
  </div>
  <h1>👁 <?= $tipo ?>: <?= htmlspecialchars($procName) ?></h1>

  <pre id="sqlBlock"><?= htmlspecialchars($createSQL) ?></pre>

  <div class="div-sadmin-buttons">
    <button id="btnCopiar">📋 Copiar</button>
    <a href="exportar_rutina.php?tipo=<?= $tipo ?>&id=<?= urlencode($procName) ?>">
      <button id="btnExportar">📤 Descargar .sql</button>
    </a>
    <button id="btnCerrar">🚪 Cerrar</button>

  </div>

  <script nonce="<?= $nonce ?>">
    function copiar() {
      const texto = document.getElementById("sqlBlock").innerText;
      navigator.clipboard.writeText(texto).then(() => {
        alert("Copiado al portapapeles ✅");
      });
    }
  </script>
  <script nonce="<?= $nonce ?>">
    document.getElementById("btnCopiar")?.addEventListener("click", () => {
      const texto = document.getElementById("sqlBlock").innerText;
      navigator.clipboard.writeText(texto).then(() => {
        alert("Copiado al portapapeles ✅");
      });
    });

    document.getElementById("btnCerrar")?.addEventListener("click", () => {
      window.close();
    });
  </script>

</body>

</html>