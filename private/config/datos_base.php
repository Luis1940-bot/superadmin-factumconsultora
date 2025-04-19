<?php
// config/datos_base.php
$host = "190.228.29.59";
$user = "fmc_oper2023";
$password = "0uC6jos0bnC8";
$port     = 3306;
// $dbname   = "mc1000";
$charset  = "utf8mb4";

// 📡 Conexión PDO
try {
  $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
  $conexion = new PDO($dsn, $user, $password);
  $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // 🛑 Manejo de errores
  die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}
