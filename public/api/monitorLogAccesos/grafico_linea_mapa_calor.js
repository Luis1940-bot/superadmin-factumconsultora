// === Gráfico de línea: evolución diaria de ingresos ===
// export function renderLineChart(data) {
//   const container = document.getElementById('chartLinea');
//   const svgNS = 'http://www.w3.org/2000/svg';
//   container.innerHTML = '';

//   const chartTitle = document.createElement('h2');
//   chartTitle.textContent = '📈 Evolución Diaria de Ingresos';
//   chartTitle.style.textAlign = 'center';
//   chartTitle.style.marginBottom = '1rem';
//   container.appendChild(chartTitle);

//   const svg = document.createElementNS(svgNS, 'svg');
//   svg.setAttribute('width', '100%');
//   svg.setAttribute('height', '300');
//   svg.style.background = '#111';
//   container.appendChild(svg);

//   const maxY = Math.max(...data.map((d) => d.ingresos));
//   const chartWidth = container.clientWidth - 40;
//   const chartHeight = 200;
//   const stepX = chartWidth / (data.length - 1);

//   const points = data.map((d, i) => {
//     const x = 20 + stepX * i;
//     const y = 40 + chartHeight - (d.ingresos / maxY) * chartHeight;
//     return { x, y, label: d.fecha, valor: d.ingresos };
//   });

//   const polyline = document.createElementNS(svgNS, 'polyline');
//   polyline.setAttribute('fill', 'none');
//   polyline.setAttribute('stroke', '#0f0');
//   polyline.setAttribute('stroke-width', '2');
//   polyline.setAttribute('points', points.map((p) => `${p.x},${p.y}`).join(' '));
//   svg.appendChild(polyline);

//   points.forEach((p) => {
//     const circle = document.createElementNS(svgNS, 'circle');
//     circle.setAttribute('cx', p.x);
//     circle.setAttribute('cy', p.y);
//     circle.setAttribute('r', '3');
//     circle.setAttribute('fill', '#0f0');
//     svg.appendChild(circle);

//     const text = document.createElementNS(svgNS, 'text');
//     text.setAttribute('x', p.x);
//     text.setAttribute('y', 260);
//     text.setAttribute('font-size', '10');
//     text.setAttribute('text-anchor', 'middle');
//     text.setAttribute('fill', '#0f0');
//     text.textContent = p.label.slice(5);
//     svg.appendChild(text);
//   });
// }

// // === Mapa de calor día x hora ===
// export function renderHeatmap(data) {
//   const container = document.getElementById('chartHeatmap');
//   const svgNS = 'http://www.w3.org/2000/svg';
//   container.innerHTML = '';

//   const chartTitle = document.createElement('h2');
//   chartTitle.textContent = '🔥 Mapa de Calor Día x Hora';
//   chartTitle.style.textAlign = 'center';
//   chartTitle.style.marginBottom = '1rem';
//   container.appendChild(chartTitle);

//   const dias = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
//   const svg = document.createElementNS(svgNS, 'svg');
//   svg.setAttribute('width', '100%');
//   svg.setAttribute('height', '380');
//   svg.style.background = '#111';
//   container.appendChild(svg);

//   const width = container.clientWidth - 40;
//   const cellW = width / 24;
//   const cellH = 40;
//   const max = Math.max(...data.map((d) => d.ingresos));

//   data.forEach(({ dia, hora, ingresos }) => {
//     const rect = document.createElementNS(svgNS, 'rect');
//     rect.setAttribute('x', hora * cellW + 20);
//     rect.setAttribute('y', dia * cellH + 20);
//     rect.setAttribute('width', cellW - 1);
//     rect.setAttribute('height', cellH - 1);

//     // const percent = ingresos / max;
//     // const r = Math.round(255 * percent);
//     // const g = Math.round(255 * (1 - percent));
//     // const color = `rgb(${r},${g},0)`; // verde → amarillo → rojo
//     const ratio = ingresos / max;
//     let color;

//     if (ratio === 0) {
//       color = '#000000'; // Sin actividad = negro
//     } else if (ratio < 0.25) {
//       color = `rgb(0, 0, ${Math.round(255 * ratio * 4)})`; // Azul oscuro → Azul fuerte
//     } else if (ratio < 0.5) {
//       color = `rgb(0, ${Math.round(255 * (ratio - 0.25) * 4)}, 255)`; // Azul → Celeste
//     } else if (ratio < 0.75) {
//       color = `rgb(${Math.round(255 * (ratio - 0.5) * 4)}, 255, ${Math.round(255 - 255 * (ratio - 0.5) * 4)})`; // Celeste → Amarillo
//     } else {
//       color = `rgb(255, ${Math.round(255 - 255 * (ratio - 0.75) * 4)}, 0)`; // Amarillo → Rojo
//     }

//     rect.setAttribute('fill', color);
//     svg.appendChild(rect);
//   });

//   dias.forEach((d, i) => {
//     const text = document.createElementNS(svgNS, 'text');
//     text.setAttribute('x', 5);
//     text.setAttribute('y', i * cellH + 40);
//     text.setAttribute('font-size', '10');
//     text.setAttribute('fill', '#0f0');
//     text.textContent = d;
//     svg.appendChild(text);
//   });

//   for (let h = 0; h < 24; h += 2) {
//     const text = document.createElementNS(svgNS, 'text');
//     text.setAttribute('x', h * cellW + 24);
//     text.setAttribute('y', 10);
//     text.setAttribute('font-size', '10');
//     text.setAttribute('fill', '#0f0');
//     text.textContent = h;
//     svg.appendChild(text);
//   }
//   // === Leyenda de colores ===
//   const legendContainer = document.createElement('div');
//   legendContainer.style.display = 'flex';
//   legendContainer.style.alignItems = 'center';
//   legendContainer.style.gap = '8px';
//   legendContainer.style.marginTop = '12px';
//   legendContainer.style.justifyContent = 'center';
//   legendContainer.style.color = '#0f0';
//   legendContainer.style.fontFamily = 'monospace';
//   legendContainer.style.fontSize = '12px';

//   const gradient = document.createElement('div');
//   gradient.style.width = '120px';
//   gradient.style.height = '12px';
//   gradient.style.background = 'linear-gradient(to right, blue, yellow, red)';
//   gradient.style.border = '1px solid #0f0';

//   const low = document.createElement('span');
//   low.textContent = 'Bajo';
//   const high = document.createElement('span');
//   high.textContent = 'Alto';

//   legendContainer.appendChild(low);
//   legendContainer.appendChild(gradient);
//   legendContainer.appendChild(high);

//   container.appendChild(legendContainer);
// }
// === Gráfico de línea: evolución diaria de ingresos ===
// === Gráfico de línea: evolución diaria de ingresos ===
// === Gráfico de línea: evolución diaria de ingresos ===
export function renderLineChart(data) {
  const container = document.getElementById('chartLinea');
  const svgNS = 'http://www.w3.org/2000/svg';
  container.innerHTML = '';

  const chartTitle = document.createElement('h2');
  chartTitle.textContent = '📈 Evolución Diaria de Ingresos';
  chartTitle.style.textAlign = 'center';
  chartTitle.style.marginBottom = '1rem';
  container.appendChild(chartTitle);

  const svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('width', '100%');
  svg.setAttribute('height', '300');
  svg.style.background = '#111';
  container.appendChild(svg);

  const maxY = Math.max(...data.map((d) => d.ingresos));
  const chartWidth = container.clientWidth - 40;
  const chartHeight = 200;
  const stepX = chartWidth / (data.length - 1);

  const points = data.map((d, i) => {
    const x = 20 + stepX * i;
    const y = 40 + chartHeight - (d.ingresos / maxY) * chartHeight;
    return { x, y, label: d.fecha, valor: d.ingresos };
  });

  const polyline = document.createElementNS(svgNS, 'polyline');
  polyline.setAttribute('fill', 'none');
  polyline.setAttribute('stroke', '#0f0');
  polyline.setAttribute('stroke-width', '2');
  polyline.setAttribute('points', points.map((p) => `${p.x},${p.y}`).join(' '));
  svg.appendChild(polyline);

  points.forEach((p) => {
    const circle = document.createElementNS(svgNS, 'circle');
    circle.setAttribute('cx', p.x);
    circle.setAttribute('cy', p.y);
    circle.setAttribute('r', '3');
    circle.setAttribute('fill', '#0f0');
    svg.appendChild(circle);

    const text = document.createElementNS(svgNS, 'text');
    text.setAttribute('x', p.x);
    text.setAttribute('y', 260);
    text.setAttribute('font-size', '10');
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('fill', '#0f0');
    text.textContent = p.label.slice(5);
    svg.appendChild(text);
  });
}

// === Mapa de calor día x hora ===
export function renderHeatmap(data) {
  const container = document.getElementById('chartHeatmap');
  const svgNS = 'http://www.w3.org/2000/svg';
  container.innerHTML = '';

  const chartTitle = document.createElement('h2');
  chartTitle.textContent = '🔥 Mapa de Calor Día x Hora';
  chartTitle.style.textAlign = 'center';
  chartTitle.style.marginBottom = '1rem';
  container.appendChild(chartTitle);

  const dias = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
  const svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('width', '100%');
  svg.setAttribute('height', '380');
  svg.style.background = '#111';
  container.appendChild(svg);

  const width = container.clientWidth - 40;
  const cellW = width / 24;
  const cellH = 40;
  const max = Math.max(...data.map((d) => d.ingresos));

  data.forEach(({ dia, hora, ingresos }) => {
    const rect = document.createElementNS(svgNS, 'rect');
    rect.setAttribute('x', hora * cellW + 20);
    rect.setAttribute('y', dia * cellH + 20);
    rect.setAttribute('width', cellW - 1);
    rect.setAttribute('height', cellH - 1);

    const percent = ingresos / max;
    const b = Math.round(255 * percent);
    const color = `rgb(0,${b},${b})`; // azul frío a cian intenso

    rect.setAttribute('fill', color);
    svg.appendChild(rect);
  });

  dias.forEach((d, i) => {
    const text = document.createElementNS(svgNS, 'text');
    text.setAttribute('x', 5);
    text.setAttribute('y', i * cellH + 40);
    text.setAttribute('font-size', '10');
    text.setAttribute('fill', '#0f0');
    text.textContent = d;
    svg.appendChild(text);
  });

  for (let h = 0; h < 24; h += 2) {
    const text = document.createElementNS(svgNS, 'text');
    text.setAttribute('x', h * cellW + 24);
    text.setAttribute('y', 10);
    text.setAttribute('font-size', '10');
    text.setAttribute('fill', '#0f0');
    text.textContent = h;
    svg.appendChild(text);
  }

  const legendContainer = document.createElement('div');
  legendContainer.style.display = 'flex';
  legendContainer.style.alignItems = 'center';
  legendContainer.style.gap = '8px';
  legendContainer.style.marginTop = '12px';
  legendContainer.style.justifyContent = 'center';
  legendContainer.style.color = '#0f0';
  legendContainer.style.fontFamily = 'monospace';
  legendContainer.style.fontSize = '12px';

  const gradient = document.createElement('div');
  gradient.style.width = '120px';
  gradient.style.height = '12px';
  gradient.style.background = 'linear-gradient(to right, #001020, #00ffff)';
  gradient.style.border = '1px solid #0f0';

  const low = document.createElement('span');
  low.textContent = 'Bajo';
  const high = document.createElement('span');
  high.textContent = 'Alto';

  legendContainer.appendChild(low);
  legendContainer.appendChild(gradient);
  legendContainer.appendChild(high);

  container.appendChild(legendContainer);
}

// === Gráfico de densidad acumulada ===
export function renderCumulativeDensityChart(data) {
  const container = document.getElementById('chartDensidad');
  const svgNS = 'http://www.w3.org/2000/svg';
  container.innerHTML = '';

  const title = document.createElement('h2');
  title.textContent = '📊 Densidad Acumulada de Ingresos';
  title.style.textAlign = 'center';
  title.style.marginBottom = '1rem';
  container.appendChild(title);

  const svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('width', '100%');
  svg.setAttribute('height', '300');
  svg.style.background = '#111';
  container.appendChild(svg);

  let acumulado = 0;
  const acumulados = data.map((d) => {
    acumulado += d.ingresos;
    return { fecha: d.fecha, valor: acumulado };
  });

  const maxY = Math.max(...acumulados.map((d) => d.valor));
  const chartWidth = container.clientWidth - 40;
  const chartHeight = 200;
  const stepX = chartWidth / (acumulados.length - 1);

  const points = acumulados.map((d, i) => {
    const x = 20 + i * stepX;
    const y = 40 + chartHeight - (d.valor / maxY) * chartHeight;
    return { x, y, label: d.fecha, valor: d.valor };
  });

  const polyline = document.createElementNS(svgNS, 'polyline');
  polyline.setAttribute('fill', 'none');
  polyline.setAttribute('stroke', '#0ff');
  polyline.setAttribute('stroke-width', '2');
  polyline.setAttribute('points', points.map((p) => `${p.x},${p.y}`).join(' '));
  svg.appendChild(polyline);

  points.forEach((p) => {
    const circle = document.createElementNS(svgNS, 'circle');
    circle.setAttribute('cx', p.x);
    circle.setAttribute('cy', p.y);
    circle.setAttribute('r', '3');
    circle.setAttribute('fill', '#0ff');
    svg.appendChild(circle);

    const text = document.createElementNS(svgNS, 'text');
    text.setAttribute('x', p.x);
    text.setAttribute('y', 260);
    text.setAttribute('font-size', '10');
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('fill', '#0ff');
    text.textContent = p.label.slice(5);
    svg.appendChild(text);
  });
}

// === Distribución horaria por día ===
export function renderHourlyDistribution(data) {
  const container = document.getElementById('chartDistribucion');
  const svgNS = 'http://www.w3.org/2000/svg';
  container.innerHTML = '';

  const title = document.createElement('h2');
  title.textContent = '🕓 Distribución Horaria por Día';
  title.style.textAlign = 'center';
  title.style.marginBottom = '1rem';
  container.appendChild(title);

  const svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('width', '100%');
  svg.setAttribute('height', '400');
  svg.style.background = '#111';
  container.appendChild(svg);

  const dias = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
  const chartWidth = container.clientWidth - 40;
  const barGroupHeight = 50;
  const barWidth = chartWidth / 24;

  dias.forEach((diaLabel, diaIndex) => {
    const baseY = 20 + diaIndex * barGroupHeight;
    const row = data.filter((d) => d.dia === diaIndex);
    const max = Math.max(...row.map((d) => d.ingresos), 1);

    row.forEach((d) => {
      const height = (d.ingresos / max) * 30;
      const rect = document.createElementNS(svgNS, 'rect');
      rect.setAttribute('x', d.hora * barWidth + 20);
      rect.setAttribute('y', baseY + (30 - height));
      rect.setAttribute('width', barWidth - 1);
      rect.setAttribute('height', height);
      rect.setAttribute('fill', '#0f0');
      svg.appendChild(rect);
    });

    const label = document.createElementNS(svgNS, 'text');
    label.setAttribute('x', 0);
    label.setAttribute('y', baseY + 20);
    label.setAttribute('fill', '#0f0');
    label.setAttribute('font-size', '10');
    label.textContent = diaLabel;
    svg.appendChild(label);
  });
}
