<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['superadmin_authenticated'])) {
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['name'])) {
  $_SESSION['selected_client_id'] = $data['id'];
  $_SESSION['selected_client_name'] = $data['name'];

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
}
