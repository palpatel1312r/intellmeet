import axios from 'axios';

/**
 * Axios instance for the IntelliMeets Laravel API (Sanctum token auth).
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    Accept: 'application/json',
  },
});

// Attach bearer token on every request if present.
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('intellmeet_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// On 401, clear session and bounce to login.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('intellmeet_token');
      localStorage.removeItem('intellmeet_user');
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
