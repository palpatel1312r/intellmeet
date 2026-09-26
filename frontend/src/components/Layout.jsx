import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';

const links = [
  { to: '/dashboard', label: '📊 Dashboard' },
  { to: '/meetings', label: '📅 Meetings' },
  { to: '/tasks', label: '✅ Tasks' },
  { to: '/teams', label: '👥 Teams' },
  { to: '/notifications', label: '🔔 Notifications' },
  { to: '/profile', label: '👤 Profile' },
];

export default function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="layout">
      <aside className="sidebar">
        <div className="brand">IntelliMeets</div>
        <nav>
          {links.map((l) => (
            <NavLink key={l.to} to={l.to} className={({ isActive }) => (isActive ? 'active' : '')}>
              {l.label}
            </NavLink>
          ))}
        </nav>
      </aside>

      <div className="main">
        <header className="topbar">
          <span className="muted">Signed in as <strong>{user?.name}</strong> ({user?.email})</span>
          <button className="btn secondary small" onClick={handleLogout}>Logout</button>
        </header>
        <main className="container">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
