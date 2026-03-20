import {useState, useCallback} from 'react';

interface UseApiState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
  execute: (...args: any[]) => Promise<T | null>;
}

export function useApi<T>(apiFunc: (...args: any[]) => Promise<T>): UseApiState<T> {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const execute = useCallback(async (...args: any[]) => {
    setLoading(true);
    setError(null);
    try {
      const result = await apiFunc(...args);
      setData(result);
      return result;
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error inesperado');
      return null;
    } finally {
      setLoading(false);
    }
  }, [apiFunc]);

  return {data, loading, error, execute};
}
