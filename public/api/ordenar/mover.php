<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$input = json_decode(file_get_contents('php://input'), true);
// error_log("JSON recibido: " . file_get_contents('php://input'));

// $input = '{"idLTYcontrol":"7804","nuevoOrden":29}';
// $input = json_decode($input, true);
$id = intval($input['idLTYcontrol'] ?? 0);
$nuevo = intval($input['nuevoOrden'] ?? 0);



if (!$id || !$nuevo) {
  echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
  exit;
}

// Conexión
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

// Obtener idReporte y orden actual
$res = $mysqli->query("SELECT idLTYreporte, orden FROM LTYcontrol WHERE idLTYcontrol = $id");
$row = $res->fetch_assoc();
if (!$row) {
  echo json_encode(['success' => false, 'message' => 'Control no encontrado']);
  exit;
}

$idReporte = intval($row['idLTYreporte']);
$ordenActual = intval($row['orden']);

if ($ordenActual === $nuevo) {
  echo json_encode(['success' => true, 'message' => 'Ya está en esa posición']);
  exit;
}

// Comenzar transacción
$mysqli->begin_transaction();

try {
  if ($nuevo < $ordenActual) {
    // Shift descendente: mover los que están entre el nuevo y el actual hacia abajo
    $sql = "UPDATE LTYcontrol 
            SET orden = orden + 1 
            WHERE idLTYreporte = ? 
              AND orden >= ? 
              AND orden < ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $idReporte, $nuevo, $ordenActual);
    $stmt->execute();
  } else {
    // Shift ascendente (por si alguna vez se implementa en el futuro)
    $sql = "UPDATE LTYcontrol 
            SET orden = orden - 1 
            WHERE idLTYreporte = ? 
              AND orden <= ? 
              AND orden > ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $idReporte, $nuevo, $ordenActual);
    $stmt->execute();
  }

  // Mover el registro original al nuevo orden
  $stmt = $mysqli->prepare("UPDATE LTYcontrol SET orden = ? WHERE idLTYcontrol = ?");
  $stmt->bind_param('ii', $nuevo, $id);
  $stmt->execute();

  $mysqli->commit();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $mysqli->rollback();
  error_log("error al mover el registro: "  .  $e->getMessage());
  echo json_encode(['success' => false, 'message' => 'Error al mover el registro']);
}
