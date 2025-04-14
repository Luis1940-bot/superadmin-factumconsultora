import getConfig from '../../js/modules/utils/get-config.js';
import {
  renderLineChart,
  renderHeatmap,
  renderCumulativeDensityChart,
  renderHourlyDistribution,
} from './grafico_linea_mapa_calor.js';

const container = document.getElementById('chartContainer');
// eslint-disable-next-line no-unused-vars
const idCliente = container.dataset.idcliente;

const dummyData = [
  { hora: '0', ingresos: 2 },
  { hora: '1', ingresos: 0 },
  { hora: '2', ingresos: 0 },
  { hora: '3', ingresos: 1 },
  { hora: '4', ingresos: 0 },
  { hora: '5', ingresos: 2 },
  { hora: '6', ingresos: 5 },
  { hora: '7', ingresos: 12 },
  { hora: '8', ingresos: 18 },
  { hora: '9', ingresos: 14 },
  { hora: '10', ingresos: 9 },
  { hora: '11', ingresos: 7 },
  { hora: '12', ingresos: 8 },
  { hora: '13', ingresos: 6 },
  { hora: '14', ingresos: 11 },
  { hora: '15', ingresos: 13 },
  { hora: '16', ingresos: 10 },
  { hora: '17', ingresos: 6 },
  { hora: '18', ingresos: 3 },
  { hora: '19', ingresos: 2 },
  { hora: '20', ingresos: 4 },
  { hora: '21', ingresos: 1 },
  { hora: '22', ingresos: 0 },
  { hora: '23', ingresos: 0 },
];

function renderChart(data) {
  const svgNS = 'http://www.w3.org/2000/svg';
  container.innerHTML = '';
  const chartTitle = document.createElement('h2');
  chartTitle.textContent = '📊 Ingresos por Hora Más Frecuente';
  chartTitle.style.textAlign = 'center';
  chartTitle.style.marginBottom = '1rem';
  container.appendChild(chartTitle);

  const chart = document.createElementNS(svgNS, 'svg');
  chart.setAttribute('width', '100%');
  chart.setAttribute('height', '400');
  chart.style.background = '#111';

  const maxY = Math.max(...data.map((d) => d.ingresos));
  const chartHeight = 300;
  const chartWidth = container.clientWidth - 40;
  const barWidth = chartWidth / data.length;

  data.forEach((d, i) => {
    const barHeight = (d.ingresos / maxY) * chartHeight;
    const rect = document.createElementNS(svgNS, 'rect');
    rect.setAttribute('x', i * barWidth + 20);
    rect.setAttribute('y', chartHeight - barHeight + 50);
    rect.setAttribute('width', barWidth - 4);
    rect.setAttribute('height', barHeight);
    rect.setAttribute('fill', '#0f0');
    chart.appendChild(rect);

    const label = document.createElementNS(svgNS, 'text');
    label.setAttribute('x', i * barWidth + barWidth / 2 + 20);
    label.setAttribute('y', chartHeight + 65);
    label.setAttribute('text-anchor', 'middle');
    label.setAttribute('font-size', '10');
    label.setAttribute('fill', '#0f0');
    label.textContent = d.hora;
    chart.appendChild(label);
  });

  container.appendChild(chart);
}

renderChart(dummyData);

// Botón cerrar
const btnCerrar = document.getElementById('btnCerrar');
btnCerrar?.addEventListener('click', () => {
  window.close();
});

const desdeInput = document.getElementById('desdeInput');
const hastaInput = document.getElementById('hastaInput');
const btnRecargar = document.getElementById('btnRecargar');
const tabla = document.getElementById('tablaAccesos');

function dataSinIngresos(data) {
  const sinContainer = document.getElementById('tablaSinIngresos');
  sinContainer.innerHTML = '<h2>🚫 Usuarios Sin Ingresos</h2>';

  const tablaSinIngresos = document.createElement('table');
  tablaSinIngresos.className = 'tabla-accesos';
  tablaSinIngresos.style.marginTop = '1rem';
  tablaSinIngresos.style.borderCollapse = 'collapse';
  tablaSinIngresos.style.width = '100%';
  const header = document.createElement('tr');
  ['ID', 'Nombre', 'Área', 'Puesto'].forEach((col) => {
    const th = document.createElement('th');
    th.textContent = col;
    header.appendChild(th);
  });
  tablaSinIngresos.appendChild(header);

  data.sin_ingresos.forEach((usuario) => {
    const tr = document.createElement('tr');
    Object.values(usuario).forEach((val) => {
      const td = document.createElement('td');
      td.textContent = val;
      tr.appendChild(td);
    });
    tablaSinIngresos.appendChild(tr);
  });

  sinContainer.appendChild(tablaSinIngresos);

  // 🚨 Alerta automática
  // alert(
  //   `⚠️ ${data.sin_ingresos.length} usuarios no registraron ingresos en el intervalo seleccionado`,
  // );
}

btnRecargar?.addEventListener('click', async () => {
  const desde = desdeInput.value;
  const hasta = hastaInput.value;
  if (!desde || !hasta) return;
  const { baseUrl } = await getConfig();
  const url = `${baseUrl}/api/monitorLogAccesos/datos.php?id=${idCliente}&desde=${desde}&hasta=${hasta}`;
  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      renderChart(data.horas);

      tabla.innerHTML = '';
      if (data.resumen?.length) {
        const header = document.createElement('tr');
        Object.keys(data.resumen[0]).forEach((key) => {
          const th = document.createElement('th');
          th.textContent = key;
          header.appendChild(th);
        });
        tabla.appendChild(header);

        data.resumen.forEach((row) => {
          const tr = document.createElement('tr');
          Object.values(row).forEach((value) => {
            const td = document.createElement('td');
            td.textContent = value;
            tr.appendChild(td);
          });
          tabla.appendChild(tr);
        });
      }
      if (Array.isArray(data.linea) && data.linea.length)
        renderLineChart(data.linea);
      if (Array.isArray(data.heatmap) && data.heatmap.length)
        renderHeatmap(data.heatmap);
      if (Array.isArray(data.linea) && data.linea.length)
        renderCumulativeDensityChart(data.linea);
      if (Array.isArray(data.heatmap) && data.heatmap.length)
        renderHourlyDistribution(data.heatmap);
      if (data.sin_ingresos?.length) {
        dataSinIngresos(data);
      }
    })
    .catch((err) => console.error('Error cargando datos:', err));
});

// Crear input de búsqueda
const searchBox = document.createElement('input');
searchBox.type = 'text';
searchBox.placeholder = '🔍 Buscar usuario, área o puesto...';
searchBox.classList.add('search-box');
tabla.parentElement.insertBefore(searchBox, tabla);

// Función para filtrar
searchBox.addEventListener('input', () => {
  const term = searchBox.value.toLowerCase();
  const rows = tabla.querySelectorAll('tr:not(:first-child)'); // omitir header

  rows.forEach((row) => {
    const cols = Array.from(row.querySelectorAll('td')).slice(0, 4); // solo las 4 primeras
    const match = cols.some((td) =>
      td.textContent.toLowerCase().includes(term),
    );
    const row2 = row;
    row2.style.display = match ? '' : 'none';
  });
});
