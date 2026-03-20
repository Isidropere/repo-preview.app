import {useEffect} from 'react';
import {useAuthStore} from '../../core/store/authStore';
import {getProfile} from '../../features/auth/services/authService';

export const useAuth = () => {
  const {token, loadToken, setAuth, logout, isAuthenticated, isLoading, user} = useAuthStore();

  useEffect(() => {
    const init = async () => {
      const savedToken = await loadToken();
      if (savedToken) {
        try {
          const profile = await getProfile();
          await setAuth(profile, savedToken);
        } catch (_e) {
          await logout();
        }
      }
    };
    init();
  }, []);

  return {user, token, isAuthenticated, isLoading, logout};
};
