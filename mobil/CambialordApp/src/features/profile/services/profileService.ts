import api from '../../../core/config/api';

export const getProfile = async () => {
  const response = await api.get('/profile');
  return response.data.data;
};

export const updateProfile = async (data: {nombres?: string; apellidos?: string; telefono?: string; nombre_usuario?: string}) => {
  const response = await api.put('/profile', data);
  return response.data;
};

export const changePassword = async (currentPassword: string, password: string, passwordConfirmation: string) => {
  const response = await api.put('/profile/password', {
    current_password: currentPassword,
    password,
    password_confirmation: passwordConfirmation,
  });
  return response.data;
};

export const getMisProductos = async (page = 1) => {
  const response = await api.get('/mis-productos', {params: {page}});
  return response.data;
};
