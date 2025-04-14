/**
 * Función para obtener la URL base dependiendo del entorno
 * @returns {string} URL base
 */
const getBaseUrl = () => {
  const { hostname } = window.location;

  if (hostname === 'localhost' || hostname === '127.0.0.1') {
    return 'http://localhost:8000';
  }

  if (hostname === 'sadmin.tenkiweb.com') {
    return 'https://sadmin.tenkiweb.com/';
  }

  return 'https://tenkiweb.com/tcontrol';
};

// Exportar la función en lugar de una variable mutable
export default getBaseUrl();
