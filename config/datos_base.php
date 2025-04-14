<?php
// config/datos_base.php
$host     = "34.174.211.66";
$port     = 3306;
$user     = "uumwldufguaxi";
$password = "5lvvumrslp0v";
$dbname   = "db5i8ff3wrjzw3";
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
