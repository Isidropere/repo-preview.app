import api from '../../../core/config/api';

export const login = async (email: string, password: string) => {
  const response = await api.post('/login', {email, password});
  return response.data.data;
};

export const register = async (name: string, email: string, password: string) => {
  const response = await api.post('/register', {name, email, password, password_confirmation: password});
  return response.data.data;
};

export const logout = async () => {
  await api.post('/logout');
};

export const getProfile = async () => {
  const response = await api.get('/user');
  return response.data.data;
};
