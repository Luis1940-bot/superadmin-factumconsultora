<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/ErrorLogger.php';
// Inicializar el logger con la ruta deseada
ErrorLogger::initialize(dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/error.log');

/**
 * Realiza la consulta de usuario en la base de datos.
 *
 * @param int $planta ID de la planta asociada al usuario.
 * @param string $email Correo electrónico del usuario.
 * @return array{success: bool, res?: array<string, mixed>, message?: string}
 */

function consultar(int $planta, string $email): array
{
  try {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/config/config.php';
    /** @var string $baseDir */
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
    // $dbnameLogin = 'tc1000';

    $cnn = new PDO("mysql:host={$host};dbname={$dbname};port={$port};charset=utf8", $user, $password);
    $sql = "SELECT id, plant, mail FROM auth
                WHERE mail=?  and plant=?;";
    $query = $cnn->prepare($sql);
    $query->bindParam(1, $email, PDO::PARAM_STR);
    $query->bindParam(2, $planta, PDO::PARAM_INT);

    $query->execute();
    $data = $query->fetch(PDO::FETCH_ASSOC);
    $query->closeCursor();
    $cnn = null;

    if (is_array($data)) {
      $response = [
        'id' => $data['id'] ?? 0,
        'mail' => $data['mail'] ?? '',
        'plant' => $data['plant'] ?? 0
      ];

      $finalResponse = ['success' => true, 'res' => $response];
      header('Content-Type: application/json');
      echo json_encode($finalResponse);
      return $finalResponse;
    } else {
      $emailStr = is_string($email) ? $email : json_encode($email);
      $plantaStr = is_int($planta) ? (string) $planta : json_encode($planta);

      error_log("No autorizado: $emailStr, planta: $plantaStr");

      $response = ['success' => false, 'message' => 'Este correo no está autorizado, consulte con el Super Admin o con el Desarrollador.'];
      echo json_encode($response);
      return $response;
    }
  } catch (Throwable $e) {
    error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    $errorResponse = ['success' => false, 'message' => "Error en la ejecución: " . $e->getMessage()];
    echo json_encode($errorResponse);
    return $errorResponse;
  }
}

header("Content-Type: application/json; charset=utf-8");
$datos = file_get_contents("php://input");
// $datos = '{"planta":15,"email":"luisconsultor@outlook.com","ruta":"/auth","rax":"&new=Sat Jan 18 2025 10:50:18 GMT-0300 (hora estándar de Argentina)"}'
// $datos = '{"ruta":"/checarEmail","planta":"15","email":"xxx@gmail.com"}';

// ✅ Manejo de datos vacíos
if (empty($datos)) {
  echo json_encode(['success' => false, 'message' => 'Faltan datos necesarios.']);
  exit;
}

// ✅ Decodificar JSON de manera segura
$data = json_decode($datos, true);

// ✅ Verificar si la decodificación fue exitosa y que $data es un array
if (!is_array($data)) {
  error_log("Error decoding JSON: " . json_encode($datos));
  echo json_encode(['success' => false, 'message' => 'Error al decodificar la cadena JSON']);
  exit;
}

// ✅ Manejo seguro del timezone
$timezone = isset($data['timezone']) && is_string($data['timezone']) ? $data['timezone'] : 'America/Argentina/Buenos_Aires';
$GLOBALS['timezone'] = $timezone;
date_default_timezone_set($timezone);

// ✅ Validar y asignar valores de manera segura
$planta = isset($data['planta']) && is_numeric($data['planta']) ? (int) $data['planta'] : 0;
$email = isset($data['email']) && is_string($data['email']) ? $data['email'] : '';

consultar($planta, $email);
