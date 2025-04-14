import { mostrarMensaje } from '../../js/modules/ui/alerts.js';
import { mostrarPrompt } from '../../js/modules/ui/prompt.js';
import getConfig from '../../js/modules/utils/get-config.js';

const container = document.getElementById('jsonContainer');
const isRemote = (container.dataset.isremote || '').toLowerCase() === 'true';
const remoteUrl = container.dataset.remoteurl || '';

let jsonData = {};

/**
 * Crea un botón con evento
 */
function crearBoton(texto, accion) {
  const btn = document.createElement('button');
  btn.textContent = texto;
  btn.addEventListener('click', accion);
  return btn;
}

function expandirAncestros(elemento) {
  let actual = elemento.parentElement;

  while (actual && actual !== document.body) {
    if (actual.classList.contains('hidden')) {
      actual.classList.remove('hidden');
    }

    // Actualiza icono del toggle si lo hay
    const toggleBtn = actual.querySelector('.node-pair button');
    if (toggleBtn && toggleBtn.textContent.trim() === '➕') {
      toggleBtn.textContent = '➖';
    }

    actual = actual.parentElement;
  }
}

function renderJsonUI() {
  container.innerHTML = '';
  Object.entries(jsonData).forEach(([key, value]) => {
    // Crear contenedor de grupo
    const section = document.createElement('div');
    section.className = 'json-section';

    // Contenedor para el contenido del grupo (se declara antes del listener)
    const content = document.createElement('div');
    content.className = 'json-section-content';

    // Crear encabezado/título
    const header = document.createElement('div');
    header.className = 'json-section-header';
    header.textContent = key.toUpperCase();

    // Listener para expandir/contraer
    header.addEventListener('click', () => {
      content.classList.toggle('hidden');
      header.classList.toggle('collapsed');
    });

    // Crear el nodo dentro del grupo
    // eslint-disable-next-line no-use-before-define
    const nodo = crearNodo(key, value, jsonData);
    content.appendChild(nodo);

    // Armar la sección
    section.appendChild(header);
    section.appendChild(content);
    container.appendChild(section);
  });
}

/**
 * Renderiza un nodo de clave/valor
 */
// Modificación del método crearNodo para permitir agregar claves o elementos a cualquier nivel
function crearNodo(key, val, parent) {
  const value = val;
  const wrapper = document.createElement('div');
  wrapper.className = 'node';

  const pair = document.createElement('div');
  pair.className = 'node-pair';

  const keyInput = document.createElement('textarea');
  keyInput.value = key;
  keyInput.className = 'node-key';

  keyInput.addEventListener('change', () => {
    let currentKey = key;
    const parentCopy = { ...parent };

    if (
      parentCopy &&
      Object.prototype.hasOwnProperty.call(parentCopy, currentKey)
    ) {
      const newKey = keyInput.value;
      if (newKey && newKey !== currentKey) {
        parentCopy[newKey] = parentCopy[currentKey];
        delete parentCopy[currentKey];
        currentKey = newKey;
      }
    }
  });

  pair.appendChild(keyInput);

  // Botón de colapsar/expandir
  let toggleBtn = null;
  const childrenWrapper = document.createElement('div');
  childrenWrapper.className = 'node-children';

  if (Array.isArray(value)) {
    toggleBtn = crearBoton('➕', () => {
      childrenWrapper.classList.toggle('hidden');
      toggleBtn.textContent = childrenWrapper.classList.contains('hidden')
        ? '➕'
        : '➖';
    });
    pair.insertBefore(toggleBtn, keyInput);

    value.forEach((item, index) => {
      const sub = crearNodo(index, item, value);
      childrenWrapper.appendChild(sub);
    });

    const cont = document.createElement('div');
    cont.className = 'node-controls';

    const btnAddItem = crearBoton('➕ Agregar Elemento', async () => {
      const nuevoElemento = await mostrarPrompt(
        'Valor del nuevo elemento a agregar:',
        'Agregar',
      );
      if (nuevoElemento !== null) {
        try {
          const parsed = JSON.parse(nuevoElemento);
          value.push(parsed);
        } catch (e) {
          value.push(nuevoElemento);
        }
        renderJsonUI();
      }
    });

    cont.appendChild(btnAddItem);
    childrenWrapper.appendChild(cont);

    wrapper.appendChild(pair);
    wrapper.appendChild(childrenWrapper);
  } else if (typeof value === 'object' && value !== null) {
    toggleBtn = crearBoton('➕', () => {
      childrenWrapper.classList.toggle('hidden');
      toggleBtn.textContent = childrenWrapper.classList.contains('hidden')
        ? '➕'
        : '➖';
    });
    pair.insertBefore(toggleBtn, keyInput);

    Object.entries(value).forEach(([subKey, subVal]) => {
      const sub = crearNodo(subKey, subVal, value);
      childrenWrapper.appendChild(sub);
    });

    const cont = document.createElement('div');
    cont.className = 'node-controls';

    const btnAddKey = crearBoton('➕ Agregar Clave', async () => {
      const nuevaClave = await mostrarPrompt(
        'Nombre de la nueva clave a agregar:',
        'Agregar',
      );
      if (nuevaClave && !value[nuevaClave]) {
        value[nuevaClave] = [];
        renderJsonUI();
      }
    });

    const btnExportar = crearBoton('📤 Exportar JSON', () => {
      const dataStr = `data:text/json;charset=utf-8,${encodeURIComponent(
        JSON.stringify(jsonData, null, 2),
      )}`;
      const downloadAnchor = document.createElement('a');
      downloadAnchor.setAttribute('href', dataStr);
      downloadAnchor.setAttribute('download', 'data.json');
      document.body.appendChild(downloadAnchor);
      downloadAnchor.click();
      downloadAnchor.remove();
    });

    const btnImportar = crearBoton('📥 Importar JSON', () => {
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'application/json';
      fileInput.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (event) => {
            try {
              const imported = JSON.parse(event.target.result);
              Object.assign(value, imported);
              renderJsonUI();
            } catch (err) {
              mostrarMensaje('❌ JSON inválido al importar', 'error');
            }
          };
          reader.readAsText(file);
        }
      };
      fileInput.click();
    });

    const btnDuplicar = crearBoton('📄 Duplicar Nodo', () => {
      if (typeof key !== 'undefined' && parent && typeof parent === 'object') {
        try {
          const clone = JSON.parse(JSON.stringify(val));

          if (Array.isArray(parent)) {
            parent.push(clone);
          } else {
            let newKey = `${key}_copy`;
            let i = 0;

            if (
              parent &&
              typeof parent === 'object' &&
              !Array.isArray(parent)
            ) {
              while (Object.hasOwn(parent, newKey)) {
                newKey = `${key}_copy${++i}`;
              }
            } else if (Array.isArray(parent)) {
              parent.push(JSON.parse(JSON.stringify(val)));
              renderJsonUI();
              return;
            } else {
              mostrarMensaje(
                '⚠️ Este nodo no se puede duplicar directamente',
                'warning',
              );
              return;
            }

            const newParent = parent;
            newParent[newKey] = clone;
          }

          renderJsonUI();

          setTimeout(() => {
            const allKeys = document.querySelectorAll('.node-key');
            allKeys.forEach((el) => {
              if (
                el.value === `${key}_copy` ||
                el.value.startsWith(`${key}_copy`)
              ) {
                expandirAncestros(el);
                el.classList.add('found-match');
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.focus();
              }
            });
          }, 100);
        } catch (err) {
          mostrarMensaje('❌ Error al duplicar nodo', 'error');
          console.error(err);
        }
      }
    });

    cont.appendChild(btnAddKey);
    cont.appendChild(btnDuplicar);
    cont.appendChild(btnImportar);
    cont.appendChild(btnExportar);
    childrenWrapper.appendChild(cont);

    wrapper.appendChild(pair);
    wrapper.appendChild(childrenWrapper);
  } else {
    const valInput = document.createElement('textarea');
    valInput.value = value;
    valInput.className = 'node-value';

    // valInput.addEventListener('input', () => {
    //   const destino = parent;

    //   destino[key] = valInput.value;
    // });
    valInput.addEventListener('input', () => {
      const destino = parent;
      let parsedValue = valInput.value;

      try {
        parsedValue = JSON.parse(valInput.value);
      } catch (_) {
        // Si falla el parseo, se queda como string (texto plano)
        parsedValue = valInput.value;
      }

      destino[key] = parsedValue;
    });

    pair.appendChild(valInput);
    wrapper.appendChild(pair);
  }

  if (childrenWrapper.children.length > 0) {
    childrenWrapper.classList.add('hidden');
    if (toggleBtn) toggleBtn.textContent = '➕';
  }

  return wrapper;
}

/**
 * Cargar y renderizar JSON
 */
async function cargarJson() {
  try {
    if (isRemote && remoteUrl) {
      const timestamp = new Date().getTime();
      const res = await fetch(`${remoteUrl}?_=${timestamp}`, {
        cache: 'no-store',
      });
      jsonData = await res.json();
    } else {
      const { baseUrl } = await getConfig();
      const res = await fetch(`${baseUrl}/load-app-json.php?file=app`, {
        cache: 'no-store',
      });
      jsonData = await res.json();
    }

    renderJsonUI();
  } catch (err) {
    mostrarMensaje('❌ Error al cargar JSON', 'error');
    console.error(err);
  }
}

function limpiarJson(obj) {
  if (typeof obj === 'string') {
    return obj.replace(/[\r\n]+/g, ' ').trim();
  }

  if (Array.isArray(obj)) {
    return obj.map(limpiarJson);
  }

  if (typeof obj === 'object' && obj !== null) {
    return Object.entries(obj).reduce((acc, [clave, valor]) => {
      const nuevaClave =
        typeof clave === 'string'
          ? clave.replace(/[\r\n]+/g, ' ').trim()
          : clave;
      acc[nuevaClave] = limpiarJson(valor);
      return acc;
    }, {});
  }

  return obj;
}

/**
 * Guardar JSON en archivo local o remoto
 */
async function guardarJson() {
  let response;

  try {
    if (isRemote && remoteUrl) {
      // console.log(JSON.stringify({ ruta: remoteUrl, contenido: jsonData }));
      response = await fetch('/api/editorJson/proxy_guardar_json.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ruta: remoteUrl,
          contenido: limpiarJson(jsonData),
        }),

        // body: JSON.stringify({ ruta: remoteUrl, contenido: jsonData }),
        cache: 'no-store',
      });
    } else {
      response = await fetch('guardar_json.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(jsonData, null, 2),
        cache: 'no-store',
      });
    }

    if (response.ok) {
      mostrarMensaje('✅ JSON guardado correctamente', 'ok');
    } else {
      mostrarMensaje('❌ Error al guardar JSON', 'error');
    }
  } catch (err) {
    mostrarMensaje('❌ Fallo de red al guardar JSON', 'error');
    console.error(err);
  }
}

/**
 * Eventos UI
 */
document.getElementById('btnRecargar').addEventListener('click', () => {
  cargarJson();
  mostrarMensaje('🔁 JSON recargado desde el archivo', 'info');
});

document.getElementById('btnCerrar').addEventListener('click', () => {
  window.close();
});

document.getElementById('btnGuardar').addEventListener('click', guardarJson);

document
  .getElementById('btnAgregarBloque')
  .addEventListener('click', async () => {
    const claveRaiz = Object.keys(jsonData)[0];
    const raiz = jsonData[claveRaiz];

    if (Array.isArray(raiz)) {
      raiz.push({
        name: '',
        ruta: '',
        type: 'popup',
      });
      renderJsonUI();
      mostrarMensaje(
        `📦 Se agregó una nueva función vacía en ${claveRaiz}`,
        'ok',
      );
    } else {
      const nombre = await mostrarPrompt('Nombre del nuevo bloque:', 'Agregar');
      if (nombre && !jsonData[nombre]) {
        jsonData[nombre] = {};
        renderJsonUI();
        mostrarMensaje('📦 Bloque agregado', 'ok');
      } else if (jsonData[nombre]) {
        mostrarMensaje('⚠️ Esa clave ya existe.', 'warning');
      }
    }
  });

document
  .getElementById('btnAgregarClave')
  .addEventListener('click', async () => {
    const clave = await mostrarPrompt(
      'Nombre de la nueva clave a agregar:',
      'Agregar',
    );
    if (!clave) return;

    Object.entries(jsonData).forEach(([val]) => {
      const value = val;
      if (typeof value === 'object' && !Array.isArray(value)) {
        value[clave] = value[clave] ?? '';
      }
    });

    renderJsonUI();
  });

// Botón "Subir arriba"
const scrollToTopBtn = document.getElementById('scrollToTopBtn');

// Mostrar/ocultar según scroll
window.addEventListener('scroll', () => {
  if (window.scrollY > 300) {
    scrollToTopBtn.classList.remove('hidden');
  } else {
    scrollToTopBtn.classList.add('hidden');
  }
});

// Evento click para volver arriba
scrollToTopBtn.addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  });
});

const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');
const nextBtn = document.getElementById('nextMatch');
const prevBtn = document.getElementById('prevMatch');

let searchTerm = '';
let matches = [];
let currentMatchIndex = -1;
const searchCounter = document.getElementById('searchCounter');

function updateCounter() {
  const total = matches.length;
  const actual = total > 0 ? currentMatchIndex + 1 : 0;
  searchCounter.textContent = `${actual} de ${total}`;
}

// Resaltar y hacer scroll a la coincidencia actual
function resaltarMatchActual() {
  matches.forEach((textarea) => textarea.classList.remove('found-match')); // input

  if (matches[currentMatchIndex]) {
    const el = matches[currentMatchIndex];
    expandirAncestros(el);
    el.classList.add('found-match');
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.focus({ preventScroll: true }); // mantiene el foco para editar si se quiere
  }
}

// Ejecutar búsqueda
function ejecutarBusqueda() {
  searchTerm = searchInput.value.trim().toLowerCase();
  matches = [];
  currentMatchIndex = -1;

  const inputs = container.querySelectorAll('textarea'); // input
  inputs.forEach((textarea) => {
    textarea.classList.remove('found-match');
    if (searchTerm && textarea.value.toLowerCase().includes(searchTerm)) {
      matches.push(textarea);
    }
  });

  if (matches.length > 0) {
    currentMatchIndex = 0;
    resaltarMatchActual();
  } else {
    updateCounter();
  }
  updateCounter();
}

// Navegación
function siguienteMatch() {
  if (matches.length === 0) return;
  currentMatchIndex = (currentMatchIndex + 1) % matches.length;
  updateCounter();
  resaltarMatchActual();
}

function anteriorMatch() {
  if (matches.length === 0) return;
  currentMatchIndex = (currentMatchIndex - 1 + matches.length) % matches.length;
  updateCounter();
  resaltarMatchActual();
}

function limpiarBusqueda() {
  searchInput.value = '';
  matches.forEach((textarea) => textarea.classList.remove('found-match'));
  matches = [];
  currentMatchIndex = -1;
  updateCounter();
  searchInput.focus();
}

// Eventos
// searchInput.addEventListener('input', ejecutarBusqueda);

searchInput.addEventListener('textarea', () => {
  searchTerm = searchInput.value.trim().toLowerCase();
  if (!searchTerm) limpiarBusqueda();
});

nextBtn.addEventListener('click', () => {
  searchInput.focus();
  siguienteMatch();
});
prevBtn.addEventListener('click', () => {
  searchInput.focus();
  anteriorMatch();
});
clearBtn.addEventListener('click', limpiarBusqueda);

// Atajos de teclado
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    limpiarBusqueda();
  } else if (e.key === 'Enter' && document.activeElement === searchInput) {
    if (matches.length === 0) {
      ejecutarBusqueda(); // ejecuta la búsqueda si aún no se hizo
    } else {
      siguienteMatch(); // si ya hay resultados, navega
    }
  } else if (e.key === 'ArrowDown' && document.activeElement === searchInput) {
    siguienteMatch();
  } else if (e.key === 'ArrowUp' && document.activeElement === searchInput) {
    anteriorMatch();
  }
});

// Inicial
cargarJson();
