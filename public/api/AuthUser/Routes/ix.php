<?php
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/error.log');
/** 
 * @var array{timezone?: string} $_SESSION 
 */
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}
// error_reporting(E_ALL); // Muestra todos los errores y warnings
// ini_set('display_errors', '1'); // Asegúrate de que los errores sean mostrados (no usar en producción)

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/private/config/config.php';
// include('datos.php');


function insertarCorreo(string $datos): void
{

  $dato_decodificado = urldecode($datos);
  $objeto_json = json_decode($dato_decodificado, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    $response = [
      'success' => false,
      'message' => 'Error al decodificar JSON: ' . json_last_error_msg()
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }


  include_once BASE_DIR . "/config/datos_base.php";
  // $host = "68.178.195.199"; 
  // $user = "developers";
  // $password = "6vLB#Q0bOVo4";
  // $dbname = "tc1000";
  // $port = 3306;
  // $charset = "utf-8";
  try {
    /** @var array{email?: string, plant?: string} $objeto_json */

    if (isset($objeto_json['email'], $objeto_json['plant'])) {
      $email = $objeto_json['email'];
      $plant = $objeto_json['plant'];
    } else {
      $response = [
        'success' => false,
        'message' => 'Faltan datos requeridos: email o plant'
      ];
      header('Content-Type: application/json');
      echo json_encode($response);
      exit;
    }

    $campos = "plant, mail";
    $interrogantes = "?, ?";

    /** @var string $host */
    /** @var string $dbname */
    /** @var string $user */
    /** @var string $password */
    /** @var PDO $pdo */

    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");

    // Preparar la consulta SQL.
    $sql = "INSERT INTO auth ($campos) VALUES ($interrogantes)";
    $stmt = $pdo->prepare($sql);

    // Vinculación de parámetros.
    $stmt->bindParam(1, $plant, PDO::PARAM_INT);
    $stmt->bindParam(2, $email, PDO::PARAM_STR);

    // Ejecución de la transacción.
    $pdo->beginTransaction();
    $stmt->execute();
    $lastInsertedId = $pdo->lastInsertId();
    // $cantidad_insert = $stmt->rowCount();
    $pdo->commit();

    // Respuesta dependiendo del resultado de la inserción.
    if ($lastInsertedId) {
      $response = [
        'success' => true,
        'message' => 'La operación fue exitosa!',
        'registros' => 1,
        'last_insert_id' => $lastInsertedId
      ];
    } else {
      $response = [
        'success' => false,
        'message' => 'Algo salió mal, no hay registros insertados'
      ];
    }
  } catch (PDOException $e) {
    error_log("Error al guardar nueva area. Error: " . $e);
    // En caso de error, revertir la transacción y mostrar el mensaje de error.
    /** @var PDO $pdo */
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    $response = [
      'success' => false,
      'message' => 'Error de base de datos: ' . $e->getMessage()
    ];
  } finally {
    // Cerrar la conexión a la base de datos.
    $pdo = null;
  }
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

header("Content-Type: application/json; charset=utf-8");
$datos = file_get_contents("php://input");
// $datos = '{"q":{"email":"eacancino@alpekpolyester.com","plant":15},"ruta":"/nuevoAuth","rax":"&new=Mon Nov 25 2024 11:48:44 GMT-0300 (hora estándar de Argentina)"}';
// $datos = '{"q":{"email":"luisglogista@gmail.com","plant":"15"},"ruta":"/nuevoAuth"}';

if (empty($datos)) {
  $response = array('success' => false, 'message' => 'Faltan datos necesarios.');
  echo json_encode($response);
  exit;
}
$data = json_decode($datos, true);

// error_log('JSON response: ' . json_encode($data));
/** @var array{q: mixed} $data */

$datos = json_encode($data['q']);

if ($datos === false) {
  echo "Error al codificar la cadena JSON.";
  exit;
}

insertarCorreo($datos);
