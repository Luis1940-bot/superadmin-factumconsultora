<?php
header('Content-Type: application/json; charset=utf-8');

// Inicializar logger
require_once dirname(__DIR__, 3) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/private/logs/logs/error.log');

// Configuración y conexión
require_once dirname(__DIR__, 3) . '/private/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

// Leer JSON del body
$data = json_decode(file_get_contents("php://input"), true);
// $json = '{"ruta":"/update_usuario","idusuario":"6","nombre":"LUIS GIMENEZ PRIETO MACHADO CASTELLANOS","area":"Factum","activo":"s","puesto":"Programador Consultor","mail":"luis@factumconsultora.com","verificador":"","cod_verificador":"","idtipousuario":"7","idLTYcliente":"1","dbName":"mc1000"}';
// $data = json_decode($json, true);
$dbname =  $data['dbName'];

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");



// Validar campos requeridos
$camposRequeridos = ['idusuario', 'nombre', 'area', 'activo', 'puesto', 'mail', 'verificador', 'idtipousuario', 'idLTYcliente'];
foreach ($camposRequeridos as $campo) {
  if (!isset($data[$campo])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Falta el campo '$campo'."]);
    exit;
  }
}

// Sanitizar y preparar datos
$idusuario = (int) $data['idusuario'];
$nombre = $mysqli->real_escape_string(trim($data['nombre']));
$area = $mysqli->real_escape_string(trim($data['area']));
$activo = $mysqli->real_escape_string(trim($data['activo']));
$puesto = $mysqli->real_escape_string(trim($data['puesto']));
$mail = $mysqli->real_escape_string(trim($data['mail']));
$verificador = (int) $data['verificador'];
// $cod_verificador = isset($data['cod_verificador']) && trim($data['cod_verificador']) !== ""
//   ? "'" . $mysqli->real_escape_string(trim($data['cod_verificador'])) . "'"
//   : "NULL";
$idtipousuario = (int) $data['idtipousuario'];
// $idLTYcliente = (int) $data['idLTYcliente'];

// Ejecutar UPDATE
$sql = "
  UPDATE usuarios SET
    nombre = '$nombre',
    area = '$area',
    activo = '$activo',
    puesto = '$puesto',
    mail = '$mail',
    verificador = $verificador,
    idtipousuario = $idtipousuario
  WHERE idusuario = $idusuario
";

if ($mysqli->query($sql)) {
  if ($mysqli->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => '✅ Usuario actualizado correctamente.']);
  } else {
    echo json_encode(['success' => false, 'message' => '⚠️ No se modificó ningún dato (puede que no haya cambios).']);
  }
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => '❌ Error al actualizar: ' . $mysqli->error]);
}

$mysqli->close();
