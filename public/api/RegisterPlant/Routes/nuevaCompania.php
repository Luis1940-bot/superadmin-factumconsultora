<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
mb_internal_encoding('UTF-8');
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/private/lib/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/error.log');

/** 
 * @var array{timezone?: string} $_SESSION 
 */
if (isset($_SESSION['timezone']) && is_string($_SESSION['timezone'])) {
  date_default_timezone_set($_SESSION['timezone']);
} else {
  date_default_timezone_set('America/Argentina/Buenos_Aires');
}

/**
 * Agrega una nueva variable en la base de datos y devuelve el estado de la operación.
 *
 * @param array<string, mixed> $objeto Datos de la variable a agregar.
 * @return array{success: bool, message: string, id?: int, array?: array<int, array<int, mixed>>}
 */
function addCompania(array $objeto): array
{
  try {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/private/config/config.php';
    /** @var string $baseDir */
    $baseDir = BASE_DIR;
    include_once $baseDir . "/private/config/datos_base.php";
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

    $cliente = isset($objeto['cliente']) && is_string($objeto['cliente']) ? $objeto['cliente'] : '';
    $detalle = isset($objeto['detalle']) && is_string($objeto['detalle']) ? $objeto['detalle'] : '';
    $contacto = isset($objeto['contacto']) && is_string($objeto['contacto']) ? $objeto['contacto'] : '';
    $activo = isset($objeto['activo']) && is_string($objeto['activo']) ? $objeto['activo'] : '';
    $email = isset($objeto['email']) && is_string($objeto['email']) ? $objeto['email'] : '';

    $conn = mysqli_connect($host, $user, $password, $dbname);
    if (!$conn) {
      throw new RuntimeException('Error al conectar con la base de datos: ' . mysqli_connect_error());
    };
    if (!$conn->set_charset($charset)) {
      printf("Error al cargar el conjunto de caracteres utf8mb4: %s\n", $conn->error);
      exit();
    }

    $sql = "INSERT INTO LTYcliente (cliente, detalle, contacto, activo, email) VALUES (?, ?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param("sssss", $cliente, $detalle, $contacto, $activo, $email);

    if ($stmt->execute() === true) {
      $last_id = (int) $conn->insert_id; // id del nuevo cliente agregado
      $response = ['success' => true, 'message' => 'Se agregó la nueva compañía.', 'id' => $last_id];

      // Verificar si el usuario ya existe en la tabla `usuario`
      $checkUserSql = "SELECT * FROM usuario WHERE mail = ? AND idLTYcliente = ?";
      $checkStmt = $conn->prepare($checkUserSql);
      if ($checkStmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
      }
      $email = "luis@factumconsultora.com";
      $checkStmt->bind_param("si", $email, $last_id);
      $checkStmt->execute();
      $result = $checkStmt->get_result();

      // if ($result->num_rows === 0) {
      if ($result instanceof mysqli_result && $result->num_rows === 0) {
        // Insertar nuevo usuario si no existe
        $nombre = 'Luis';
        $pass = '5678';
        $area = 'Desarrollo';
        $puesto = 'Developer';
        $idtipousuario = 8;
        $activo = 's';
        $firma = 'LEG';
        $mi_cfg = 'd-es';
        $idLTYcliente = $last_id;
        $verificador = 1;
        $hash = hash('ripemd160', $pass);

        $userInsertSql = "INSERT INTO usuario (nombre, pass, area, puesto, idtipousuario, activo, mail, firma, mi_cfg, idLTYcliente, verificador) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $userStmt = $conn->prepare($userInsertSql);
        if ($userStmt === false) {
          die("Error al preparar la consulta: " . $conn->error);
        }
        $userStmt->bind_param("ssssissssii", $nombre, $hash, $area, $puesto, $idtipousuario, $activo, $email, $firma, $mi_cfg, $idLTYcliente, $verificador);

        if ($userStmt->execute() === true) {
          $response['message'] .= ' Usuario agregado correctamente.';
        } else {
          $response['message'] .= ' No se pudo agregar el usuario.';
        }

        $userStmt->close();
      } else {
        $response['message'] .= ' El usuario ya existe.';
      }

      $checkStmt->close();
    } else {
      $response = array('success' => false, 'message' => 'No se agregó la nueva compañía.');
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    return $response;
  } catch (\Throwable $e) {
    error_log("Error al guardar nuevo cliente. Error: " . $e);
    print "Error!: " . $e->getMessage() . "<br>";
    return ['success' => false, 'message' => "Error en la ejecución de la consulta: " . $e->getMessage()];
  }
}

header("Content-Type: application/json; charset=utf-8");

$datos = file_get_contents("php://input");

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

// ✅ Validar y asignar valores de manera segura
$objeto = isset($data['objeto']) && is_array($data['objeto'])
  ? array_filter($data['objeto'], fn($key) => is_string($key), ARRAY_FILTER_USE_KEY)
  : [];

// ✅ Ahora los parámetros tienen los tipos correctos
addCompania($objeto);
