const LOG_ENDPOINT = './tools/getLogData.php'; // En entorno local

function mostrarPlantasDesdeLog(plantas) {
  const select = document.getElementById('clienteSelect');
  select.innerHTML = '<option value="">Seleccioná una planta</option>';
  plantas.forEach((p) => {
    const option = document.createElement('option');
    option.value = p.num;
    option.textContent = `${p.name} (ID: ${p.num})`;
    select.appendChild(option);
  });
}

fetch(LOG_ENDPOINT, {
  headers: {
    headers: {
      Authorization: `Bearer ${window.token}`, // ✅ clave entre comillas normales
    },
  },
})
  .then((res) => res.json())
  .then((data) => {
    if (data.plantas) {
      mostrarPlantasDesdeLog(data.plantas);
    }

    // Mostrar info del developer
    const footer = document.querySelector('footer');
    if (footer && data.by) {
      const devLink = document.createElement('a');
      devLink.href = data.rutaDeveloper;
      devLink.target = '_blank';
      devLink.textContent = `👨‍💻 ${data.by}`;
      footer.appendChild(document.createElement('br'));
      footer.appendChild(devLink);
    }
  })
  .catch((err) => console.error('❌ Error al cargar log.json:', err));
