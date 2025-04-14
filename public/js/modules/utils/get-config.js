// modules/utils/get-config.js

export default async function getConfig() {
  try {
    const response = await fetch('../../../load-config-js.php');

    if (!response.ok) {
      throw new Error('No se pudo cargar config');
    }

    const config = await response.json();
    return config; // contiene { baseUrl, routes }
  } catch (error) {
    console.error('Error al obtener configuración:', error);
    return {
      baseUrl: window.location.origin,
      routes: {},
    };
  }
}
