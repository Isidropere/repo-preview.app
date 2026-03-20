import {create} from 'zustand';
import * as Keychain from 'react-native-keychain';

interface User {
  id: number;
  name: string;
  email: string;
  tipo_usuario_id: number;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  setAuth: (user: User, token: string) => Promise<void>;
  logout: () => Promise<void>;
  loadToken: () => Promise<string | null>;
}

export const useAuthStore = create<AuthState>(set => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  setAuth: async (user, token) => {
    await Keychain.setGenericPassword('token', token);
    set({user, token, isAuthenticated: true, isLoading: false});
  },

  logout: async () => {
    await Keychain.resetGenericPassword();
    set({user: null, token: null, isAuthenticated: false, isLoading: false});
  },

  loadToken: async () => {
    try {
      const credentials = await Keychain.getGenericPassword();
      if (credentials) {
        set({token: credentials.password, isLoading: false});
        return credentials.password;
      }
    } catch (_e) {}
    set({isLoading: false});
    return null;
  },
}));
