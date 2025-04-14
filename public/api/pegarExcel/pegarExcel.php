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

function generarCodigoAlfabetico(string $reporte, int $orden): string
{
  $reporte = mb_convert_encoding($reporte, 'UTF-8', mb_detect_encoding($reporte, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
  $reporte = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $reporte);
  $palabras = preg_split('/[\s-]+/u', $reporte);
  $codigoBase = '';
  foreach ($palabras as $palabra) {
    $codigoBase .= strtolower(mb_substr($palabra, 0, 2, 'UTF-8'));
  }
  $codigoBase = substr($codigoBase, 0, 6);
  $ordenStr = str_pad((int) $orden, 4, "0", STR_PAD_LEFT);
  $hash = substr(md5($reporte . $orden), 0, 5);
  return strtolower(substr($codigoBase . $ordenStr . $hash, 0, 15));
}

$idLTYreporte = isset($_GET['idLTYreporte']) ? intval($_GET['idLTYreporte']) : 0;
$nombreReporte = "Desconocido";
$ultimoOrden = 0;
$datos = [];
$nombreCliente = "Desconocido";
$idCliente = 0;

if ($idLTYreporte > 0) {
  $mysqli = new mysqli($host, $user, $password, $dbname, $port);
  if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
  }
  mysqli_set_charset($mysqli, "utf8mb4");

  $query = "
    SELECT c.idLTYcontrol, c.control, c.detalle, c.tipodato, c.tpdeobserva, c.orden, r.nombre AS nombre_reporte, c.idLTYcliente AS id_cliente, t.cliente AS nombre_cliente
    FROM LTYcontrol c
    INNER JOIN LTYreporte r ON c.idLTYreporte = r.idLTYreporte
    INNER JOIN LTYcliente t ON c.idLTYcliente = t.idLTYcliente
    WHERE c.idLTYreporte = $idLTYreporte
    ORDER BY c.orden ASC";

  $result = $mysqli->query($query);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $datos[] = $row;
      $ultimoOrden = max($ultimoOrden, $row['orden']);
      $nombreReporte = $row['nombre_reporte'];
      $nombreCliente = $row['nombre_cliente'];
      $idCliente = $row['id_cliente'];
    }
  } else {
    error_log("⚠️ No hay registros para idLTYreporte: $idLTYreporte");
  }

  $mysqli->close();
}
$cssUrl = BASE_URL . "/api/pegarExcel/pegarExcel.css?v=" . time();
$jsUrl = BASE_URL . "/api/pegarExcel/pegarExcel.js?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
$crypto = BASE_URL . "/api/pegarExcel/crypto-js.min.js?v=" . time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pegar desde Excel</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body>
  <div class="container">
    <h2>📋 Pegar Datos desde Excel</h2>

    <form id="reporteForm" method="GET">
      <label for="idLTYreporte">Ingrese ID del Reporte:</label>
      <input type="number" id="idLTYreporte" name="idLTYreporte" value="<?= htmlspecialchars($idLTYreporte) ?>" />
      <button type="submit" class="btn">Buscar</button>
    </form>

    <h3>📑 Reporte: <?= htmlspecialchars($nombreReporte) ?></h3>
    <div class="cliente-info">
      <h3 id="idCliente" data-id="<?= htmlspecialchars($idCliente) ?>">ID Cliente: <?= htmlspecialchars($idCliente) ?></h3>

      <h3 id="nombreCliente">Cliente: <?= htmlspecialchars($nombreCliente) ?></h3>
    </div>

    <h4>🔢 Última Observación: <?= $ultimoOrden ?></h4>

    <h3>📚 Registros Existentes</h3>
    <table id="tablaExistente">
      <thead>
        <tr>
          <th>ID Control</th>
          <th>Control</th>
          <th>Detalle</th>
          <th>Tipo Dato</th>
          <th>Tp Observa</th>
          <th>Orden</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($datos as $dato): ?>
          <tr>
            <td><?= $dato['idLTYcontrol'] ?></td>
            <td><?= $dato['control'] ?></td>
            <td><?= $dato['detalle'] ?></td>
            <td><?= $dato['tipodato'] ?></td>
            <td><?= $dato['tpdeobserva'] ?></td>
            <td><?= $dato['orden'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>🆕 Nuevos Datos</h3>
    <div class="flex-container">
      <textarea id="campoInput" placeholder="Campos"></textarea>
      <textarea id="detalleInput" placeholder="Detalles"></textarea>
      <textarea id="tipoDatoInput" placeholder="Tipo Dato"></textarea>
      <textarea id="tpObservaInput" placeholder="Tp Observa"></textarea>
    </div>

    <button class="btn" id="procesarBtn">Procesar</button>
    <button class="btn" id="limpiarBtn">Limpiar</button>

    <h3>✅ Datos Procesados</h3>
    <table id="dataTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Campo</th>
          <th>Detalle</th>
          <th>Tipo Dato</th>
          <th>Tp Observa</th>
          <th>Orden</th>
          <th>Código</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <hr>
    <button class="btn" id="guardarBtn">Guardar en Base de Datos</button>
  </div>
  <div class="div-sadmin-buttons">
    <button type="button" id="cerrarBtn" class="button-selector-sadmin">🚪 Cerrar</button>
  </div>

  <script nonce="<?= $nonce ?>">
    window.ultimoOrdenJS = <?= json_encode($ultimoOrden) ?>;
    window.nombreReporteJS = <?= json_encode($nombreReporte) ?>;
  </script>
  <script src="<?= $crypto ?>"></script>
  <script nonce="<?= $nonce ?>" type="module" src="<?= $jsUrl ?>"></script>
  <script nonce="<?= $nonce ?>">
    document.addEventListener('DOMContentLoaded', () => {
      const cerrarBtn = document.getElementById('cerrarBtn');
      if (cerrarBtn) {
        cerrarBtn.addEventListener('click', () => {
          window.close();
        });
      }
    });
  </script>
</body>

</html>