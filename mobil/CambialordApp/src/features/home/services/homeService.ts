import api from '../../../core/config/api';

export const getHomeProducts = async (page = 1) => {
  const response = await api.get('/items', {params: {page, per_page: 20}});
  return response.data;
};

export const getCategories = async () => {
  const response = await api.get('/categorias');
  return response.data.data;
};
