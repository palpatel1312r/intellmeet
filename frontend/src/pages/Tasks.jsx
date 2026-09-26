import { useCallback, useEffect, useState } from 'react';
import Swal from 'sweetalert2';
import { fetchTasks, createTask, updateTask, deleteTask, fetchTeams } from '../api/endpoints';
import { useAuth } from '../context/AuthContext.jsx';

const STATUSES = ['todo', 'in_progress', 'review', 'done'];
const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

const emptyForm = { title: '', description: '', team_id: '', assigned_to: '', status: 'todo', priority: 'medium', due_date: '' };

export default function Tasks() {
  const { user } = useAuth();
  const [tasks, setTasks] = useState([]);
  const [teams, setTeams] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showCreate, setShowCreate] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [error, setError] = useState('');
  const [mineOnly, setMineOnly] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    fetchTasks(mineOnly ? { mine: 1 } : {})
      .then(({ data }) => setTasks(data.data || []))
      .catch(() => Swal.fire('Error', 'Could not load tasks.', 'error'))
      .finally(() => setLoading(false));
  }, [mineOnly]);

  useEffect(load, [load]);
  useEffect(() => {
    fetchTeams().then(({ data }) => setTeams(data.data || [])).catch(() => {});
  }, []);

  const submitCreate = async (e) => {
    e.preventDefault();
    setError('');
    if (!form.team_id) { setError('Pick a team for this task.'); return; }
    try {
      const payload = { ...form };
      Object.keys(payload).forEach((k) => payload[k] === '' && delete payload[k]);
      payload.assigned_to = payload.assigned_to || user.id;
      await createTask(payload);
      setShowCreate(false);
      setForm(emptyForm);
      Swal.fire('Task created', '', 'success');
      load();
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(errors ? Object.values(errors).flat().join(' ') : 'Could not create task.');
    }
  };

  const moveStatus = async (task, status) => {
    await updateTask(task.id, { status });
    load();
  };

  const remove = async (task) => {
    const ok = await Swal.fire({ title: 'Delete task?', text: task.title, icon: 'warning', showCancelButton: true });
    if (!ok.isConfirmed) return;
    await deleteTask(task.id);
    load();
  };

  return (
    <>
      <div className="row" style={{ marginBottom: 16 }}>
        <h2 className="page-title" style={{ margin: 0 }}>Tasks</h2>
        <div className="row" style={{ gap: 8 }}>
          <label className="muted" style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
            <input type="checkbox" style={{ width: 'auto' }} checked={mineOnly} onChange={(e) => setMineOnly(e.target.checked)} />
            Assigned to me
          </label>
          <button className="btn" onClick={() => setShowCreate((v) => !v)}>+ New Task</button>
        </div>
      </div>

      {showCreate && (
        <form className="card" onSubmit={submitCreate}>
          <strong>Create a task</strong>
          <div className="grid cols-2" style={{ marginTop: 12 }}>
            <div className="form-group">
              <label>Title *</label>
              <input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
            </div>
            <div className="form-group">
              <label>Team *</label>
              <select required value={form.team_id} onChange={(e) => setForm({ ...form, team_id: e.target.value })}>
                <option value="">Select team…</option>
                {teams.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
              </select>
            </div>
            <div className="form-group">
              <label>Description</label>
              <textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
            </div>
            <div className="form-group">
              <label>Priority</label>
              <select value={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.value })}>
                {PRIORITIES.map((p) => <option key={p} value={p}>{p}</option>)}
              </select>
            </div>
            <div className="form-group">
              <label>Due date</label>
              <input type="date" value={form.due_date} onChange={(e) => setForm({ ...form, due_date: e.target.value })} />
            </div>
          </div>
          {error && <p className="error-text">{error}</p>}
          <div style={{ marginTop: 8 }}>
            <button className="btn" type="submit">Create task</button>{' '}
            <button className="btn secondary" type="button" onClick={() => setShowCreate(false)}>Cancel</button>
          </div>
        </form>
      )}

      {loading && <p className="muted">Loading…</p>}
      {!loading && tasks.length === 0 && <p className="muted">No tasks found.</p>}

      {!loading && tasks.length > 0 && (
        <div className="grid cols-4">
          {STATUSES.map((col) => {
            const colTasks = tasks.filter((t) => t.status === col);
            return (
              <div className="card" key={col}>
                <div className="row" style={{ marginBottom: 10 }}>
                  <span className={`badge ${col}`}>{col.replace('_', ' ')}</span>
                  <span className="muted">{colTasks.length}</span>
                </div>
                {colTasks.map((t) => {
                  const idx = STATUSES.indexOf(t.status);
                  return (
                    <div key={t.id} className="card" style={{ padding: 12, marginBottom: 10, background: 'var(--surface-2)' }}>
                      <div className="row" style={{ alignItems: 'flex-start' }}>
                        <strong style={{ fontSize: 14 }}>{t.title}</strong>
                        <button className="btn small danger" onClick={() => remove(t)}>✕</button>
                      </div>
                      <p className="muted" style={{ margin: '6px 0' }}>
                        <span className={`badge ${t.priority}`}>{t.priority}</span>
                        {t.due_date ? ` · due ${t.due_date}` : ''}
                        {t.assignee ? ` · ${t.assignee.name}` : ''}
                      </p>
                      <div className="row" style={{ gap: 6 }}>
                        {idx > 0 && (
                          <button className="btn small secondary" onClick={() => moveStatus(t, STATUSES[idx - 1])}>← Back</button>
                        )}
                        {idx < STATUSES.length - 1 && (
                          <button className="btn small" onClick={() => moveStatus(t, STATUSES[idx + 1])}>Advance →</button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            );
          })}
        </div>
      )}
    </>
  );
}
