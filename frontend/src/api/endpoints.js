import api from './client';

// Auth
export const login = (credentials) => api.post('/auth/login', credentials);
export const register = (data) => api.post('/auth/register', data);
export const logout = () => api.delete('/auth/logout');
export const fetchUser = () => api.get('/auth/me');
export const updateProfile = (data) => api.post('/auth/profile', data, {
  headers: { 'Content-Type': 'multipart/form-data' },
});

// Dashboard
export const fetchDashboard = () => api.get('/dashboard');

// Meetings
export const fetchMeetings = (params = {}) => api.get('/meetings', { params });
export const fetchMeeting = (id) => api.get(`/meetings/${id}`);
export const createMeeting = (data) => api.post('/meetings', data);
export const updateMeeting = (id, data) => api.put(`/meetings/${id}`, data);
export const deleteMeeting = (id) => api.delete(`/meetings/${id}`);
export const endMeeting = (id) => api.post(`/meetings/${id}/end`);
export const joinMeetingByCode = (code) => api.post(`/join/${code}`);

// AI features
export const summarizeMeeting = (id) => api.post(`/meetings/${id}/summarize`);
export const extractActionItems = (id) => api.post(`/meetings/${id}/action-items`);
export const transcribeMeeting = (id, formData) =>
  api.post(`/meetings/${id}/transcribe`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

// Tasks
export const fetchTasks = (params = {}) => api.get('/tasks', { params });
export const createTask = (data) => api.post('/tasks', data);
export const updateTask = (id, data) => api.put(`/tasks/${id}`, data);
export const deleteTask = (id) => api.delete(`/tasks/${id}`);

// Teams
export const fetchTeams = (params = {}) => api.get('/teams', { params });
export const fetchTeam = (id) => api.get(`/teams/${id}`);
export const createTeam = (data) => api.post('/teams', data);
export const updateTeam = (id, data) => api.put(`/teams/${id}`, data);
export const deleteTeam = (id) => api.delete(`/teams/${id}`);
export const addTeamMember = (id, email) => api.post(`/teams/${id}/members`, { email });
export const removeTeamMember = (id, userId) => api.delete(`/teams/${id}/members/${userId}`);

// Notifications
export const fetchNotifications = () => api.get('/notifications');
export const fetchUnreadCount = () => api.get('/notifications/unread-count');
export const markNotificationRead = (id) => api.post(`/notifications/${id}/mark-as-read`);
export const markAllNotificationsRead = () => api.post('/notifications/mark-all-as-read');
