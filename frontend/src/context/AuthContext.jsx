import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { login as loginApi, logout as logoutApi, fetchUser } from '../api/endpoints';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const raw = localStorage.getItem('intellmeet_user');
    return raw ? JSON.parse(raw) : null;
  });
  const [loading, setLoading] = useState(true);

  // Validate stored token on first load.
  useEffect(() => {
    const token = localStorage.getItem('intellmeet_token');
    if (!token) {
      setLoading(false);
      return;
    }
    fetchUser()
      .then(({ data }) => {
        setUser(data.user);
        localStorage.setItem('intellmeet_user', JSON.stringify(data.user));
      })
      .catch(() => {
        localStorage.removeItem('intellmeet_token');
        localStorage.removeItem('intellmeet_user');
        setUser(null);
      })
      .finally(() => setLoading(false));
  }, []);

  const login = useCallback(async (email, password) => {
    const { data } = await loginApi({ email, password });
    localStorage.setItem('intellmeet_token', data.token);
    localStorage.setItem('intellmeet_user', JSON.stringify(data.user));
    setUser(data.user);
    return data.user;
  }, []);

  const logout = useCallback(async () => {
    try {
      await logoutApi();
    } catch {
      /* token may already be expired */
    }
    localStorage.removeItem('intellmeet_token');
    localStorage.removeItem('intellmeet_user');
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, setUser, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within an AuthProvider');
  return ctx;
}
