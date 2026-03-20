import api from '../../../core/config/api';

export const getConversations = async () => {
  const response = await api.get('/messages');
  return response.data.data;
};

export const getMessages = async (userId: number) => {
  const response = await api.get(`/messages/${userId}`);
  return response.data.data;
};

export const sendMessage = async (receiverId: number, content: string) => {
  const response = await api.post('/messages', {receiver_id: receiverId, content});
  return response.data.data;
};

export const getUnreadCount = async () => {
  const response = await api.get('/messages/unread/count');
  return response.data.data.count;
};
