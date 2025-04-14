<?php
// ver_parametros.php

require_once dirname(__DIR__, 3) . '/config/config.php';
include_once BASE_DIR . "/config/datos_base.php";

// Conexión a la base de datos
$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($mysqli->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Error de conexión a la base de datos.']);
  exit;
}
mysqli_set_charset($mysqli, "utf8mb4");

// Obtener el nombre de la rutina de la URL y escapar adecuadamente
$nombre = $_GET['id'] ?? '';
$nombre = $mysqli->real_escape_string($nombre);

// Consulta de parámetros desde information_schema
$sql = "
  SELECT PARAMETER_NAME, DATA_TYPE, DTD_IDENTIFIER, ORDINAL_POSITION, PARAMETER_MODE 
  FROM information_schema.PARAMETERS 
  WHERE SPECIFIC_NAME = '$nombre' 
    AND SPECIFIC_SCHEMA = '$dbname'
  ORDER BY ORDINAL_POSITION
";

$result = $mysqli->query($sql);
$params = [];

// Inicializamos contadores
$in = 0;
$out = 0;
$inout = 0;

while ($row = $result->fetch_assoc()) {
  // Si PARAMETER_MODE es NULL, asumimos 'IN'
  $modo = strtoupper($row['PARAMETER_MODE'] ?? 'IN');

  if ($modo === 'IN') {
    $in++;
  } elseif ($modo === 'OUT') {
    $out++;
  } elseif ($modo === 'INOUT') {
    $inout++;
  }

  // Agregar el parámetro, usando un alias por defecto si falta el nombre
  $params[] = [
    'PARAMETER_NAME'   => $row['PARAMETER_NAME'] ? $row['PARAMETER_NAME'] : null,
    'DATA_TYPE'        => $row['DATA_TYPE'],
    'DTD_IDENTIFIER'   => $row['DTD_IDENTIFIER'],
    'ORDINAL_POSITION' => $row['ORDINAL_POSITION'],
    'PARAMETER_MODE'   => $modo
  ];
}

// Preparar la salida JSON
header('Content-Type: application/json');
echo json_encode([
  'params' => $params,
  'conteo' => [
    'IN'    => $in,
    'OUT'   => $out,
    'INOUT' => $inout
  ]
]);
