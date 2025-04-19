import getConfig from '../../js/modules/utils/get-config.js';

const tabla = document.querySelector('#accesosTable tbody');
const searchInput = document.getElementById('searchInput');
let chartInstance = null;
function renderGrafico(stats) {
  const ctx = document.getElementById('chartAccesos').getContext('2d');
  if (chartInstance) {
    chartInstance.destroy(); // ✅ destruye el anterior
  }
  // eslint-disable-next-line no-unused-vars, no-undef
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: Object.keys(stats),
      datasets: [
        {
          label: '✔️ Éxitos',
          data: Object.values(stats).map((d) => d.ok),
          backgroundColor: 'lime',
        },
        {
          label: '❌ Fallidos',
          data: Object.values(stats).map((d) => d.fail),
          backgroundColor: 'red',
        },
      ],
    },
    options: {
      plugins: {
        legend: { labels: { color: '#0f0' } },
      },
      scales: {
        x: { ticks: { color: '#0f0' } },
        y: { ticks: { color: '#0f0' }, beginAtZero: true },
      },
    },
  });
}

function mostrarIntentosSospechosos(data) {
  const tablaBruta = document.querySelector('#fuerzaBrutaTable tbody');
  tablaBruta.innerHTML = '';

  const sospechosos = {};

  data.forEach((item) => {
    if (item.estado === 'fail') {
      const clave = `${item.ip}::${item.email}`;
      if (!sospechosos[clave]) {
        sospechosos[clave] = { ...item, fallos: 1 };
      } else {
        sospechosos[clave].fallos++;
        sospechosos[clave].fecha = item.fecha; // actualiza último intento
      }
    }
  });

  // Filtro: solo los que tienen más de 3 intentos fallidos
  Object.values(sospechosos)
    .filter((v) => v.fallos >= 3)
    .forEach((s) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${s.ip}</td>
        <td>${s.email}</td>
        <td class="fail">${s.fallos}</td>
        <td>${s.fecha}</td>
      `;
      tablaBruta.appendChild(tr);
    });
}

async function cargarAccesos() {
  try {
    tabla.innerHTML = "<tr><td colspan='7'>Cargando...</td></tr>";
    const { baseUrl } = await getConfig();
    const cadena = document.getElementById('cliente-id').textContent;
    const match = cadena.match(/mc\d{4}/);
    const dbName = match ? match[0] : null;
    const url = `${baseUrl}/api/monitorAccesos/datos_accesos.php?_=${Date.now()}&dbName=${encodeURIComponent(dbName)}`;
    const res = await fetch(url, { cache: 'no-store' });

    // const dbName = match ? match[0] : null;
    // const url = `${baseUrl}/api/monitorAccesos/datos_accesos.php?_=${Date.now()}`;
    // const res = await fetch(url, { cache: 'no-store' });
    const json = await res.json();
    const { data } = json;

    // Mostrar ataques
    const ataquesDiv = document.getElementById('bloqueAtaques');
    ataquesDiv.innerHTML = '';

    if (json.ips_sospechosas.length || json.emails_sospechosos.length) {
      ataquesDiv.innerHTML += `<h2>🛡️ Posibles ataques de fuerza bruta</h2>`;
    }

    if (json.ips_sospechosas.length) {
      ataquesDiv.innerHTML += `<h3>🔁 IPs sospechosas</h3><ul>`;
      json.ips_sospechosas.forEach((ip) => {
        ataquesDiv.innerHTML += `<li title="${ip.geo}">${ip.ip} — ${ip.intentos} intentos</li>`;
      });
      ataquesDiv.innerHTML += `</ul>`;
    }

    if (json.emails_sospechosos.length) {
      ataquesDiv.innerHTML += `<h3>👤 Emails sospechosos</h3><ul>`;
      json.emails_sospechosos.forEach((e) => {
        ataquesDiv.innerHTML += `<li>${e.email} — ${e.intentos} fallos</li>`;
      });
      ataquesDiv.innerHTML += `</ul>`;
    }

    tabla.innerHTML = '';

    const accesosPorUsuario = {};

    data.forEach((item) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.fecha_local}</td>
        <td>${item.email}</td>
        <td>${item.planta}</td>
        <td>${item.ip}</td>
        <td title="${item.geo}">${item.geo.split('-')[0]}</td>
        <td>${item.navegador}</td>
        <td class="${item.estado === 'ok' ? 'success' : 'fail'}">${item.estado.toUpperCase()}</td>
      `;
      tabla.appendChild(tr);

      const key = item.email;
      if (!accesosPorUsuario[key]) accesosPorUsuario[key] = { ok: 0, fail: 0 };
      item.estado === 'ok'
        ? accesosPorUsuario[key].ok++
        : accesosPorUsuario[key].fail++;
    });

    renderGrafico(accesosPorUsuario);
    mostrarIntentosSospechosos(data);
  } catch (error) {
    console.error('❌ Error al cargar accesos:', error);
    tabla.innerHTML =
      "<tr><td colspan='7'>❌ Error al cargar datos. Ver consola.</td></tr>";
  }
}

const btnRecargar = document.getElementById('recargarBtn');
btnRecargar.addEventListener('click', () => {
  btnRecargar.disabled = true;
  btnRecargar.textContent = '🔄 Recargando...';
  cargarAccesos().finally(() => {
    btnRecargar.disabled = false;
    btnRecargar.textContent = '🔄 Recargar';
  });
});

document
  .getElementById('cerrarBtn')
  .addEventListener('click', () => window.close());

searchInput.addEventListener('input', () => {
  const filtro = searchInput.value.toLowerCase();
  document.querySelectorAll('#accesosTable tbody tr').forEach((f) => {
    const row = f;
    row.style.display = row.textContent.toLowerCase().includes(filtro)
      ? ''
      : 'none';
  });
});

cargarAccesos();
