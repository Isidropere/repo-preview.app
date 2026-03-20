/**
 * productService.ts — Servicio de productos
 *
 * Funciones para consumir los endpoints de items del backend.
 * Todas las llamadas usan la instancia de Axios configurada
 * en api.ts (con token automático y manejo de errores).
 *
 * Endpoints:
 *   GET  /api/items       → getProducts()
 *   GET  /api/items/{id}  → getProductDetail()
 *   GET  /api/categorias  → getCategories()
 *   POST /api/items       → createProduct()  [requiere token]
 */
import api from '../../../core/config/api';

/**
 * Obtener listado de productos con paginación y búsqueda.
 * @param page    Número de página (default 1)
 * @param search  Texto a buscar en el nombre del producto
 * @returns       Objeto de paginación Laravel { data[], meta, links }
 */
export const getProducts = async (page = 1, search = '') => {
  const params: any = {page};
  if (search) params.q = search; // Solo agregar si hay texto de búsqueda
  const response = await api.get('/items', {params});
  return response.data;
};

/**
 * Obtener detalle completo de un producto.
 * @param id  ID del item
 * @returns   Objeto con todos los campos del item + imágenes + vendedor
 */
export const getProductDetail = async (id: number) => {
  const response = await api.get(`/items/${id}`);
  return response.data.data;
};

/**
 * Obtener lista de categorías activas.
 * Usada para poblar el filtro de categorías en HomeScreen.
 * @returns  Array de { id, nombre }
 */
export const getCategories = async () => {
  const response = await api.get('/categorias');
  return response.data.data;
};

/**
 * Publicar un nuevo producto.
 * Requiere autenticación (token en header automático).
 * Usa multipart/form-data para enviar imágenes.
 * @param data  FormData con campos del item e imágenes
 * @returns     Item creado
 */
export const createProduct = async (data: FormData) => {
  const response = await api.post('/items', data, {
    headers: {'Content-Type': 'multipart/form-data'}, // Necesario para subir archivos
  });
  return response.data.data;
};
