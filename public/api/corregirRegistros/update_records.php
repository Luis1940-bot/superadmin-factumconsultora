<?php
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

require_once dirname(__DIR__, 3) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(__DIR__, 3) . '/logs/logs/error.log');
require_once dirname(__DIR__, 3) . '/config/config.php';

$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";
/** @var string $charset */
/** @var string $dbname */
/** @var string $host */
/** @var int $port */
/** @var string $password */
/** @var string $user */
/** @var PDO $pdo */
// $host = "34.174.211.66";
// $user = "uumwldufguaxi";
// $password = "5lvvumrslp0v";
// $dbname = "db5i8ff3wrjzw3";
// $port = 3306;
$charset = "utf8mb4";
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}

mysqli_set_charset($mysqli, "utf8mb4");

$sqlUpdate = "
    UPDATE LTYregistrocontrol l
    INNER JOIN LTYreporte l2 ON l2.idLTYreporte = l.idLTYreporte
    SET l.idLTYcliente = l2.idLTYcliente
    WHERE l.idLTYcliente = 0
";

if ($mysqli->query($sqlUpdate)) {
  echo json_encode(['success' => true, 'message' => 'Registros actualizados correctamente.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error en la actualización: ' . $mysqli->error]);
}
