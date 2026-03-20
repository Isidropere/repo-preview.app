import api from '../../../core/config/api';

export const getDeliveryZones = async () => {
  const response = await api.get('/delivery/zonas');
  return response.data;
};

export const calcularDelivery = async (pueblo: string, tipoDestinatario: string) => {
  const response = await api.get('/delivery/calcular', {
    params: {pueblo, tipo_destinatario: tipoDestinatario},
  });
  return response.data;
};
