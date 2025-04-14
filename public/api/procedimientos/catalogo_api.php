<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

$dbName = $dbname;
$result = $mysqli->query("
  SELECT SPECIFIC_NAME, ROUTINE_TYPE 
  FROM information_schema.ROUTINES
  WHERE ROUTINE_SCHEMA = '$dbName'
  ORDER BY ROUTINE_TYPE, SPECIFIC_NAME
");

$baseApi = BASE_URL . "/api/procedimientos/api_rutina.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>📡 Catálogo de APIs MySQL</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/api/procedimientos/rutinas.css?v=<?= time() ?>">
</head>

<body class="hacker-mode">
  <h1>📚 Catálogo de Endpoints de Rutinas</h1>

  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Endpoint JSON</th>
        <th>Endpoint CSV</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()):
        $nombre = urlencode($row['SPECIFIC_NAME']);
        $tipo = $row['ROUTINE_TYPE'];
      ?>
        <tr>
          <td><?= htmlspecialchars($row['SPECIFIC_NAME']) ?></td>
          <td><?= $tipo ?></td>
          <td>
            <a target="_blank" href="<?= $baseApi ?>?nombre=<?= $nombre ?>&tipo=<?= $tipo ?>">
              JSON 🔗
            </a>
          </td>
          <td>
            <a target="_blank" href="<?= $baseApi ?>?nombre=<?= $nombre ?>&tipo=<?= $tipo ?>&formato=csv">
              CSV 📥
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="div-sadmin-buttons">
    <button onclick="window.close()">🚪 Cerrar</button>
  </div>
</body>

</html>
<!-- https://tusitio.com/api/procedimientos/api_rutina.php?nombre=mi_proc&tipo=PROCEDURE&params[]=2023-01-01&params[]=2023-02-01 -->