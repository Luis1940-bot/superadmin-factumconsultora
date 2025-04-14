<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
mb_internal_encoding('UTF-8');
require_once dirname(dirname(dirname(__DIR__))) . '/ErrorLogger.php';
ErrorLogger::initialize(dirname(dirname(dirname(__DIR__))) . '/logs/error.log');
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
function writeJSON(array $objeto): array
{
  require_once dirname(dirname(dirname(__DIR__))) . '/config.php';
  /** @var string $baseDir */
  $baseDir = BASE_DIR;
  // $clienteNum = $objeto['num'];

  $clienteNum = isset($objeto['num']) && (is_int($objeto['num']) || is_string($objeto['num']))
    ? (string) $objeto['num']
    : '0';
  $clienteNum = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clienteNum);
  $directorio = $baseDir . '/models/App/' . $clienteNum . '/';
  if (!file_exists($directorio)) {
    mkdir($directorio, 0777, true);
  }
  $directorioPlano = $baseDir . '/assets/img/planos/' . $clienteNum . '/';
  if (!file_exists($directorioPlano)) {
    mkdir($directorioPlano, 0777, true);
  }
  $directorioImaenes = $baseDir . '/assets/Imagenes/' . $clienteNum . '/';
  if (!file_exists($directorioImaenes)) {
    mkdir($directorioImaenes, 0777, true);
  }
  $directorioImaenes = $baseDir . '/assets/Logos/' . $clienteNum . '/';
  if (!file_exists($directorioImaenes)) {
    mkdir($directorioImaenes, 0777, true);
  }
  $directorioImaenes = $baseDir . '/models/consultas/' . $clienteNum . '/';
  if (!file_exists($directorioImaenes)) {
    mkdir($directorioImaenes, 0777, true);
  }
  $file = $directorio . 'app.json';

  // Comprobar si el archivo app.json existe
  if (!file_exists($file)) {
    // Estructura inicial del archivo JSON
    $initialContent = [
      "planta" => $objeto['name'],
      "idiomas" => [
        "bienvenido" => ["Bienvenido", "Bem-vindo(Br)", "Welcome"],
        "abreviatura" => ["es", "bra", "en"]
      ],
      "menuSelectivo" => [
        "sin" => [
          "sinGuardar" => [],
          "sinGuardarCambio" => [],
          "sinGuardarComoNuevo" => [],
          "sinSalir" => [],
          "sinVolver" => [],
          "sinHacerFirmar" => []
        ],
        "con" => [
          "conExportar" => [],
          "conGuardar" => [],
          "conGuardarCambio" => [],
          "conGuardarComoNuevo" => [],
          "conSalir" => [],
          "conVolver" => [],
          "conHacerFirmar" => []
        ]
      ],
      "Menu" => [
        "name" => ["Controles", "Consultas"],
        "type" => ["", ""],
        "ruta" => ["Controles/index.php", "Consultas/index.php"]
      ],
      "Controles" => [
        "name" => ["Controles", "Imprimir control"],
        "type" => ["", ""],
        "ruta" => ["Controles/index.php", ""]
      ],
      "Ad" => [
        "name" => ["Reportes", "Controles", "Variables", "Areas"],
        "type" => ["", "", "", ""],
        "ruta" => [
          "ListReportes/index.php",
          "ListControles/index.php",
          "ListVariables/index.php",
          "ListAreas/index.php"
        ]
      ],
      "Sad" => [
        "name" => [
          "AuthUser",
          "RegisterPlant",
          "phpinfo",
          "hash",
          "pegarExcel",
          "verRepetidosControl",
          "Corregir Registros",
          "Ver Log"
        ],
        "type" => ["", "", "", "", "", "", "", ""],
        "ruta" => [
          "RegisterAuthUser/index.php",
          "RegisterPlant/index.php",
          "phpinfo.php",
          "hash.php",
          "ListControles/Routes/pegarExcel.php",
          "ListControles/Routes/verRepetidosControl.php",
          "Pages/Sadmin/Routes/get_records",
          "Pages/Sadmin/Routes/errorLog"
        ]
      ],
      "apps" => [
        "name" => ["SCG", "Admin", "Super Admin"],
        "type" => ["ctrl", "ctrl", "btn"],
        "ruta" => ["Menu/index.php", "Admin/index.php", "Sadmin/index.php"],
        "nivel" => [3, 4, 8]
      ],
    ];

    // Convertir el array a JSON y escribirlo en el archivo
    $jsonContent = json_encode($initialContent, JSON_PRETTY_PRINT);
    if ($jsonContent === false) {
      throw new RuntimeException("Error al codificar JSON.");
    }
    $jsonContent = str_replace('\/', '/', $jsonContent);
    if (file_put_contents($file, $jsonContent) === false) {
      throw new RuntimeException("Error al escribir el archivo JSON: $file");
    }
    // file_put_contents($file, json_encode($initialContent, JSON_PRETTY_PRINT));
    file_put_contents($file, $jsonContent);
  }

  $jsonData = file_get_contents($file);
  $jsonData = ($jsonData !== false) ? $jsonData : '{}';
  $currentData = json_decode($jsonData, true);
  // $currentData = json_decode(file_get_contents($jsonData), true);

  $response = [];
  // Guardar los datos actualizados en log.json
  if (file_put_contents($file, json_encode($currentData, JSON_PRETTY_PRINT))) {
    // echo json_encode(['success' => true]);
    $response = [
      'success' => true,
      'message' => 'Archivo JSON guardado correctamente.'
    ];
  } else {
    error_log("Error al crear el JSON.");
    // echo json_encode(['success' => false, 'message' => 'Failed tocreate to app.json']);
    $response = ['success' => false, 'message' => 'Failed to create app.json'];
  }
  return $response;
}

header("Content-Type: application/json; charset=utf-8");

$datos = file_get_contents("php://input");
// $datos = '{"ruta":"/escribirJSON","rax":"&new=Thu Jun 20 2024 18:04:48 GMT-0300 (hora estándar de Argentina)","objeto":{"name":"xxxxxxxxxx","num":4}}';
// Validar si $datos está vacío
if (empty($datos)) {
  $response = ['success' => false, 'message' => 'Faltan datos necesarios.'];
  echo json_encode($response);
  exit;
}

// Decodificar JSON de la solicitud
$data = json_decode($datos, true);

// Validar si la decodificación fue exitosa y si $data es un array
if (!is_array($data)) {
  echo json_encode(['success' => false, 'message' => 'Error al decodificar la cadena JSON principal']);
  exit;
}

// Verificar si 'objeto' está definido y es un array
$objeto = $data['objeto'] ?? [];

if (!is_array($objeto)) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos: falta "objeto" o su formato es incorrecto']);
  exit;
}


$objeto = array_combine(array_map('strval', array_keys($objeto)), array_values($objeto)) ?: [];

writeJSON($objeto);
