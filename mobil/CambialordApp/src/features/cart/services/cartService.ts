import api from '../../../core/config/api';

export const getCart = async () => {
  const response = await api.get('/carrito');
  return response.data.data;
};

export const addToCart = async (itemId: number, cantidad: number = 1) => {
  const response = await api.post('/carrito', {item_id: itemId, cantidad});
  return response.data;
};

export const removeFromCart = async (itemId: number) => {
  await api.delete(`/carrito/${itemId}`);
};

export const updateCantidad = async (id: number, accion: string) => {
  const response = await api.put(`/carrito/${id}/cantidad`, {accion});
  return response.data;
};

export const toggleSeleccion = async (id: number, seleccionado: boolean) => {
  const response = await api.put(`/carrito/${id}/seleccion`, {es_seleccionado: seleccionado});
  return response.data;
};

export const vaciarCarrito = async () => {
  await api.delete('/carrito/vaciar');
};
