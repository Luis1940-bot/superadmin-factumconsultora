import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import getConfig from '../../js/modules/utils/get-config.js';

function filtrarTabla() {
  const input = document.getElementById('searchInput');
  const filtro = input.value.toLowerCase();
  const filas = document.querySelectorAll('#usuariosTable tr');

  for (let i = 1; i < filas.length; i++) {
    const fila = filas[i];
    const id = fila.children[0]?.textContent.toLowerCase() || '';
    const nombre = fila.children[1]?.textContent.toLowerCase() || '';
    const cliente = fila.children[9]?.textContent.toLowerCase() || '';
    const mail = fila.children[5]?.textContent.toLowerCase() || '';

    const visible =
      id.includes(filtro) ||
      nombre.includes(filtro) ||
      cliente.includes(filtro) ||
      mail.includes(filtro);

    fila.style.display = visible ? '' : 'none';
  }
}

// eslint-disable-next-line no-unused-vars
function cargarUsuario(
  id,
  nombre,
  area,
  activo,
  puesto,
  mail,
  verificador,
  codVerificador,
  idtipousuario,
  idLTYcliente,
) {
  // console.log('Cargando usuario: ', id);

  const modal = document.getElementById('editModal');
  modal.style.display = 'block';

  document.getElementById('edit_idusuario').value = id;
  document.getElementById('edit_nombre').value = nombre;
  document.getElementById('edit_area').value = area;
  document.getElementById('edit_activo').value = activo;
  document.getElementById('edit_puesto').value = puesto;
  document.getElementById('edit_mail').value = mail;
  document.getElementById('edit_verificador').value = verificador;
  document.getElementById('edit_cod_verificador').value =
    codVerificador === 'NULL' ? '' : codVerificador;
  document.getElementById('edit_idtipousuario').value = idtipousuario;
  document.getElementById('edit_idLTYcliente').value = idLTYcliente;
}

// eslint-disable-next-line no-unused-vars
function cerrarModal() {
  document.getElementById('editModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('searchInput');
  input.value = '';
  if (input) {
    input.addEventListener('input', filtrarTabla);
  }

  const cerrarModalBtn = document.getElementById('cerrarModalBtn');
  if (cerrarModalBtn) {
    cerrarModalBtn.addEventListener('click', cerrarModal);
  }

  // También asegurate que tu función esté definida:
  window.cerrarModal = function windowCierraModal() {
    document.getElementById('editModal').style.display = 'none';
  };
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formEditarUsuario');
  const btnGuardar = document.getElementById('btnGuardarUsuario');
  const modal = document.getElementById('editModal');
  const cerrarModalBtn = document.getElementById('cerrarModalBtn');

  // Evento para cerrar el modal
  cerrarModalBtn?.addEventListener('click', () => {
    modal.classList.remove('show');
  });

  // Evento para abrir el modal con los datos del usuario
  const editarBtns = document.querySelectorAll('.btn-edit');

  editarBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      form.querySelector('#edit_idusuario').value = btn.dataset.id;
      form.querySelector('#edit_nombre').value = btn.dataset.nombre;
      form.querySelector('#edit_area').value = btn.dataset.area;
      form.querySelector('#edit_activo').value = btn.dataset.activo;
      form.querySelector('#edit_puesto').value = btn.dataset.puesto;
      form.querySelector('#edit_mail').value = btn.dataset.mail;
      form.querySelector('#edit_verificador').value = btn.dataset.verificador;
      form.querySelector('#edit_cod_verificador').value = btn.dataset.codver;
      form.querySelector('#edit_idtipousuario').value = btn.dataset.tipousuario;
      form.querySelector('#edit_idLTYcliente').value = btn.dataset.cliente;

      modal.classList.add('show');
    });
  });

  btnGuardar?.addEventListener('click', async () => {
    let dbName = document.getElementById('cliente-id').textContent;
    const match = dbName.match(/mc\d{4}/);
    dbName = match ? match[0] : null;
    const datos = {
      idusuario: form.querySelector('#edit_idusuario')?.value.trim(),
      nombre: form.querySelector('#edit_nombre')?.value.trim(),
      area: form.querySelector('#edit_area')?.value.trim(),
      activo: form.querySelector('#edit_activo')?.value,
      puesto: form.querySelector('#edit_puesto')?.value.trim(),
      mail: form.querySelector('#edit_mail')?.value.trim(),
      verificador: form.querySelector('#edit_verificador')?.value,
      cod_verificador: form
        .querySelector('#edit_cod_verificador')
        ?.value.trim(),
      idtipousuario: form.querySelector('#edit_idtipousuario')?.value,
      idLTYcliente: form.querySelector('#edit_idLTYcliente')?.value,
      dbName,
    };

    if (
      !datos.nombre ||
      !datos.mail ||
      !datos.idtipousuario ||
      !datos.idLTYcliente
    ) {
      mostrarMensaje('⚠️ Campos obligatorios incompletos.', 'warning');
      return;
    }

    try {
      const { baseUrl } = await getConfig();

      const res = await fetch(`${baseUrl}/api/router.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ruta: '/update_usuario', ...datos }),
      });

      const result = await res.json();

      if (result.success) {
        mostrarMensaje('✅ Usuario actualizado correctamente', 'ok');
        window.location.reload();
      } else {
        mostrarMensaje(`❌ Error: ${result.message}`, 'error');
      }
    } catch (err) {
      mostrarMensaje(`❌ Error inesperado: ${err}`, 'error');
    }
  });
  const cerrarVentanaBtn = document.getElementById('cerrarVentanaBtn');

  cerrarVentanaBtn?.addEventListener('click', () => {
    if (window.close) {
      window.close();
    } else {
      mostrarMensaje('Cerrá esta pestaña manualmente', 'info');
    }
  });
});
