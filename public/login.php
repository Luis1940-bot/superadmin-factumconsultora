<?php
session_start();
require_once dirname(__DIR__) . '/private/config/auth_token.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Validar si el token viene en la solicitud
if (!isset($input['token']) || !is_string($input['token'])) {
  echo json_encode(['success' => false, 'error' => 'Token inválido']);
  exit;
}

// Comparar token ingresado
if (hash_equals($token, $input['token'])) {
  $_SESSION['superadmin_authenticated'] = true;
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Token incorrecto']);
}
