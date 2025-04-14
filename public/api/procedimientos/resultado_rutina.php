<?php
header('Content-Type: text/html;charset=utf-8');

require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;
include_once $baseDir . "/config/datos_base.php";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
mysqli_set_charset($mysqli, "utf8mb4");

$nombre = $_GET['nombre'] ?? '';
$tipo = $_GET['tipo'] ?? 'PROCEDURE';
$params = $_GET['params'] ?? [];

$cssUrl = BASE_URL . "/api/procedimientos/resultado_rutina.css?v=" . time();
$favicon = BASE_URL . "/img/favicon.ico";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Resultado de <?= htmlspecialchars($nombre) ?></title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <link rel="icon" href="<?= $favicon ?>" type="image/x-icon" />
</head>

<body class="hacker-mode">
  <script>
    function exportarCSV() {
      const tabla = document.querySelector("table");
      if (!tabla) return alert("⚠️ No se encontró la tabla");

      let csv = "";
      tabla.querySelectorAll("tr").forEach(row => {
        const cols = Array.from(row.querySelectorAll("th, td")).map(col =>
          `"${col.textContent.replace(/"/g, '""')}"`
        );
        csv += cols.join(",") + "\n";
      });

      const blob = new Blob([csv], {
        type: "text/csv;charset=utf-8;"
      });
      const url = URL.createObjectURL(blob);

      const link = document.createElement("a");
      link.href = url;
      link.download = "resultado_rutina.csv";
      link.click();
      URL.revokeObjectURL(url);
    }

    function copiarTabla() {
      const tabla = document.querySelector("table");
      if (!tabla) return alert("⚠️ No hay tabla para copiar");

      const seleccion = window.getSelection();
      const rango = document.createRange();
      rango.selectNode(tabla);
      seleccion.removeAllRanges();
      seleccion.addRange(rango);

      try {
        document.execCommand("copy");
        alert("✅ Tabla copiada al portapapeles");
      } catch (err) {
        alert("❌ No se pudo copiar");
      }

      seleccion.removeAllRanges();
    }
  </script>

</body>

</html>
<?php
if (!$nombre) {
  echo "<p style='color: red;'>❌ Nombre de rutina no especificado.</p>";
  exit;
}

$nombreSQL = $mysqli->real_escape_string($nombre);
$tipo = strtoupper($tipo);
$paramSQL = '';

if (is_array($params)) {
  $sanitized = array_map(fn($v) => "'" . $mysqli->real_escape_string($v) . "'", $params);
  $paramSQL = implode(', ', $sanitized);
}

try {
  if ($tipo === 'FUNCTION') {
    $query = "SELECT $nombreSQL($paramSQL) AS resultado";
    $res = $mysqli->query($query);
  } else {
    $query = "CALL $nombreSQL($paramSQL)";
    $res = $mysqli->query($query);
  }

  if (!$res) {
    echo "<p style='color: red;'>❌ Error al ejecutar: " . htmlspecialchars($mysqli->error) . "</p>";
    exit;
  }

  echo "<h2>🧪 Resultado de <code>$nombreSQL($paramSQL)</code></h2>";
  echo '
  <div class="floating-actions">
    <button onclick="copiarTabla()" title="Copiar tabla al portapapeles">📋</button>
    <button onclick="exportarCSV()" title="Exportar a CSV">📤</button>
  </div>
';

  echo "<table border='1' cellpadding='6'>";
  echo "<thead><tr>";
  foreach ($res->fetch_fields() as $field) {
    echo "<th>" . htmlspecialchars($field->name) . "</th>";
  }
  echo "</tr></thead><tbody>";

  while ($row = $res->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $value) {
      echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
  }
  echo "</tbody></table>";
} catch (Exception $e) {
  echo "<pre style='color: red;'>❌ " . htmlspecialchars($e->getMessage()) . "</pre>";
}
