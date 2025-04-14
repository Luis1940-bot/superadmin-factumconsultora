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
  // Convertir caracteres a UTF-8 y limpiar caracteres especiales
  $reporte = mb_convert_encoding($reporte, 'UTF-8', mb_detect_encoding($reporte, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
  $reporte = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $reporte);
  $palabras = preg_split('/[\s-]+/u', $reporte);

  $codigoBase = '';
  foreach ($palabras as $palabra) {
    $codigoBase .= strtolower(mb_substr($palabra, 0, 2, 'UTF-8'));
  }

  // Limitar a 6 caracteres
  $codigoBase = substr($codigoBase, 0, 6);

  // Asegurar que el orden sea de 4 dígitos
  $ordenStr = str_pad((int) $orden, 4, "0", STR_PAD_LEFT);

  // Generar un hash único basado en el reporte y orden
  $hash = substr(md5($reporte . $orden), 0, 5);

  // Formar el código final de 15 caracteres
  return strtolower(substr($codigoBase . $ordenStr . $hash, 0, 15));
}


// 📌 Leer datos enviados desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
  echo json_encode(['success' => false, 'message' => 'Datos no válidos.']);
  exit;
}

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

// 📌 Si se proporciona un `idLTYcontrol`, actualizar solo ese registro
if (isset($data['idLTYcontrol']) && isset($data['nuevoCodigo'])) {
  $idLTYcontrol = intval($data['idLTYcontrol']);
  $nuevoCodigo = $mysqli->real_escape_string($data['nuevoCodigo']); // 🔹 Usar el código correcto recibido

  $sql = "UPDATE LTYcontrol SET control = '$nuevoCodigo' WHERE idLTYcontrol = $idLTYcontrol";
  if ($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Control actualizado correctamente.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $mysqli->error]);
  }
  exit;
}


// 📌 Si se proporciona un `idLTYreporte`, actualizar todos los registros de ese reporte
if (isset($data['idLTYreporte'])) {
  $idLTYreporte = intval($data['idLTYreporte']);

  // Obtener todos los registros de LTYcontrol que coincidan con `idLTYreporte`
  $query = "
        SELECT c.idLTYcontrol, r.nombre AS nombre_reporte, c.orden
        FROM LTYcontrol c
        INNER JOIN LTYreporte r ON c.idLTYreporte = r.idLTYreporte
        WHERE c.idLTYreporte = $idLTYreporte
    ";

  $result = $mysqli->query($query);

  if ($result->num_rows > 0) {
    $mysqli->begin_transaction();
    try {
      while ($row = $result->fetch_assoc()) {
        $idLTYcontrol = $row['idLTYcontrol'];
        $nombreReporte = $row['nombre_reporte'];
        $orden = $row['orden'];

        // Generar nuevo código
        $nuevoCodigo = generarCodigoAlfabetico($nombreReporte, $orden);
        $nuevoCodigo = mb_convert_encoding($nuevoCodigo, 'UTF-8', 'auto');
        $nuevoCodigo = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $nuevoCodigo);

        $sqlUpdate = "UPDATE LTYcontrol SET control = '$nuevoCodigo' WHERE idLTYcontrol = $idLTYcontrol";
        $mysqli->query($sqlUpdate);
      }
      $mysqli->commit();
      echo json_encode(['success' => true, 'message' => 'Todos los controles fueron actualizados correctamente.']);
    } catch (Exception $e) {
      $mysqli->rollback();
      echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'No se encontraron registros para este idLTYreporte.']);
  }
  exit;
}

// 📌 Si no se envió `idLTYcontrol` ni `idLTYreporte`, enviar error
echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID válido.']);
exit;
