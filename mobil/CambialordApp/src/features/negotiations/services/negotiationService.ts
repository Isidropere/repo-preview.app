/**
 * negotiationService.ts — Servicio de negociaciones
 *
 * Funciones para gestionar propuestas de intercambio entre
 * compradores y vendedores.
 *
 * Endpoints:
 *   GET  /api/negociaciones                    → getNegotiations()
 *   POST /api/negociaciones                    → createNegotiation()
 *   POST /api/negociaciones/{id}/aceptar       → acceptNegotiation()
 *   POST /api/negociaciones/{id}/rechazar      → rejectNegotiation()
 *   POST /api/negociaciones/{id}/contraoferta  → counterOffer()
 *
 * Todos los endpoints requieren token (auth:sanctum).
 */
import api from '../../../core/config/api';

/**
 * Obtener todas las negociaciones del usuario autenticado.
 * Incluye tanto las que inició como comprador y como vendedor.
 * @returns  Array de negociaciones con datos del item y usuarios
 */
export const getNegotiations = async () => {
  const response = await api.get('/negociaciones');
  return response.data.data;
};

/**
 * Crear una nueva propuesta de negociación/intercambio.
 * @param itemId       ID del item que se quiere negociar
 * @param mensaje      Mensaje descriptivo para el vendedor
 * @param montoOferta  Precio ofertado (opcional si es intercambio puro)
 * @returns            Negociación creada
 */
export const createNegotiation = async (itemId: number, mensaje: string, montoOferta?: number) => {
  const response = await api.post('/negociaciones', {
    item_id: itemId,
    mensaje,
    monto_oferta: montoOferta, // undefined si no se envía precio
  });
  return response.data;
};

/**
 * Aceptar una negociación (solo el vendedor puede hacerlo).
 * @param id  ID de la negociación
 * @returns   { message }
 */
export const acceptNegotiation = async (id: number) => {
  const response = await api.post(`/negociaciones/${id}/aceptar`);
  return response.data;
};

/**
 * Rechazar una negociación (solo el vendedor puede hacerlo).
 * @param id  ID de la negociación
 * @returns   { message }
 */
export const rejectNegotiation = async (id: number) => {
  const response = await api.post(`/negociaciones/${id}/rechazar`);
  return response.data;
};

/**
 * Enviar una contraoferta con un precio diferente.
 * @param id      ID de la negociación
 * @param monto   Nuevo precio propuesto
 * @param mensaje Mensaje explicativo (opcional)
 * @returns       { message }
 */
export const counterOffer = async (id: number, monto?: number, mensaje?: string) => {
  const response = await api.post(`/negociaciones/${id}/contraoferta`, {
    monto_contra_oferta: monto,
    mensaje,
  });
  return response.data;
};
