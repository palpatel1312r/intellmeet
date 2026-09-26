import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchDashboard } from '../api/endpoints';

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchDashboard()
      .then(({ data }) => setData(data))
      .catch(() => setError('Failed to load dashboard.'));
  }, []);

  if (error) return <p className="error-text">{error}</p>;
  if (!data) return <p className="muted">Loading dashboard…</p>;

  const { stats, recent_meetings: meetings, recent_tasks: tasks } = data;

  return (
    <>
      <h2 className="page-title">Dashboard</h2>

      <div className="grid cols-4">
        <div className="card stat-card"><h3>{stats.total_meetings}</h3><p>Total Meetings</p></div>
        <div className="card stat-card"><h3>{stats.upcoming_meetings}</h3><p>Upcoming</p></div>
        <div className="card stat-card"><h3>{stats.active_meetings}</h3><p>Active Now</p></div>
        <div className="card stat-card"><h3>{stats.pending_tasks}</h3><p>Pending Tasks</p></div>
        <div className="card stat-card"><h3>{stats.completed_tasks}</h3><p>Completed Tasks</p></div>
        <div className="card stat-card"><h3>{stats.teams_count}</h3><p>Teams</p></div>
      </div>

      <div className="grid cols-2" style={{ marginTop: 16 }}>
        <div className="card">
          <div className="row" style={{ marginBottom: 12 }}>
            <strong>Recent Meetings</strong>
            <Link to="/meetings" className="btn small secondary">View all</Link>
          </div>
          {meetings.length === 0 && <p className="muted">No meetings yet.</p>}
          {meetings.map((m) => (
            <div key={m.id} className="row" style={{ padding: '8px 0', borderBottom: '1px solid var(--border)' }}>
              <Link to={`/meetings/${m.id}`}>{m.title}</Link>
              <span className={`badge ${m.status}`}>{m.status}</span>
            </div>
          ))}
        </div>

        <div className="card">
          <div className="row" style={{ marginBottom: 12 }}>
            <strong>My Recent Tasks</strong>
            <Link to="/tasks" className="btn small secondary">View all</Link>
          </div>
          {tasks.length === 0 && <p className="muted">No tasks assigned.</p>}
          {tasks.map((t) => (
            <div key={t.id} className="row" style={{ padding: '8px 0', borderBottom: '1px solid var(--border)' }}>
              <span>{t.title}</span>
              <span>
                <span className={`badge ${t.priority}`}>{t.priority}</span>{' '}
                <span className={`badge ${t.status}`}>{t.status.replace('_', ' ')}</span>
              </span>
            </div>
          ))}
        </div>
      </div>
    </>
  );
}
