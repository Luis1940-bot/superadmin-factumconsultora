<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
$baseDir = BASE_DIR;

$archivo = glob(__DIR__ . '/logs/processlist_*.log');
rsort($archivo);
$ultimos = array_slice($archivo, 0, 48); // Las últimas 48 horas
$data = [];

foreach ($ultimos as $file) {
  $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if (isset($entry['timestamp'])) {
      $data[] = [
        't' => $entry['timestamp'],
        'total' => $entry['total'],
        'sleep' => $entry['sleep']
      ];
    }
  }
}

$cssUrl = BASE_URL . "/api/showProcess/processlist.css?v=" . time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>📈 Historial de procesos</title>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
  <script src="/lib/chart.js"></script>
</head>

<body>
  <h2>📈 Historial de Procesos MySQL</h2>
  <canvas id="grafico" width="1000" height="400"></canvas>

  <script>
    const ctx = document.getElementById('grafico').getContext('2d');
    const datos = <?= json_encode($data) ?>;

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: datos.map(d => d.t),
        datasets: [{
            label: 'Total',
            data: datos.map(d => d.total),
            borderColor: 'lime',
            fill: false
          },
          {
            label: 'Sleep',
            data: datos.map(d => d.sleep),
            borderColor: 'red',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: '#0f0'
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#0f0'
            }
          },
          y: {
            ticks: {
              color: '#0f0'
            }
          }
        }
      }
    });
  </script>
</body>

</html>