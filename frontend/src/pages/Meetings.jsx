import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Swal from 'sweetalert2';
import { fetchMeetings, createMeeting, joinMeetingByCode, deleteMeeting } from '../api/endpoints';

const emptyForm = { title: '', description: '', start_time: '', end_time: '' };

export default function Meetings() {
  const navigate = useNavigate();
  const [meetings, setMeetings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showCreate, setShowCreate] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [joinCode, setJoinCode] = useState('');
  const [error, setError] = useState('');

  const load = useCallback(() => {
    setLoading(true);
    fetchMeetings()
      .then(({ data }) => setMeetings(data.data || []))
      .catch(() => Swal.fire('Error', 'Could not load meetings.', 'error'))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const submitCreate = async (e) => {
    e.preventDefault();
    setError('');
    try {
      const payload = { ...form };
      if (!payload.start_time) delete payload.start_time;
      if (!payload.end_time) delete payload.end_time;
      const { data } = await createMeeting(payload);
      setShowCreate(false);
      setForm(emptyForm);
      Swal.fire('Created!', `Meeting code: ${data.meeting.meeting_code}`, 'success');
      navigate(`/rooms/${data.meeting.meeting_code}`);
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(errors ? Object.values(errors).flat().join(' ') : 'Could not create meeting.');
    }
  };

  const handleJoin = async (e) => {
    e.preventDefault();
    if (!joinCode.trim()) return;
    try {
      const { data } = await joinMeetingByCode(joinCode.trim());
      navigate(`/rooms/${data.meeting.meeting_code}`);
    } catch {
      Swal.fire('Not found', 'No meeting matches that code.', 'error');
    }
  };

  const remove = async (m) => {
    const ok = await Swal.fire({
      title: 'Delete this meeting?',
      text: m.title,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
    });
    if (!ok.isConfirmed) return;
    await deleteMeeting(m.id);
    Swal.fire('Deleted', '', 'success');
    load();
  };

  return (
    <>
      <div className="row" style={{ marginBottom: 16 }}>
        <h2 className="page-title" style={{ margin: 0 }}>Meetings</h2>
        <div className="row" style={{ gap: 8 }}>
          <form onSubmit={handleJoin} className="row" style={{ gap: 8 }}>
            <input
              style={{ width: 180 }}
              placeholder="Join with code…"
              value={joinCode}
              onChange={(e) => setJoinCode(e.target.value)}
            />
            <button className="btn secondary">Join</button>
          </form>
          <button className="btn" onClick={() => setShowCreate((v) => !v)}>+ New Meeting</button>
        </div>
      </div>

      {showCreate && (
        <form className="card" onSubmit={submitCreate}>
          <strong>Schedule a new meeting</strong>
          <div className="grid cols-2" style={{ marginTop: 12 }}>
            <div className="form-group">
              <label>Title *</label>
              <input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
            </div>
            <div className="form-group">
              <label>Description</label>
              <input value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
            </div>
            <div className="form-group">
              <label>Start time</label>
              <input type="datetime-local" value={form.start_time} onChange={(e) => setForm({ ...form, start_time: e.target.value })} />
            </div>
            <div className="form-group">
              <label>End time</label>
              <input type="datetime-local" value={form.end_time} onChange={(e) => setForm({ ...form, end_time: e.target.value })} />
            </div>
          </div>
          {error && <p className="error-text">{error}</p>}
          <div style={{ marginTop: 8 }}>
            <button className="btn" type="submit">Create &amp; open room</button>{' '}
            <button className="btn secondary" type="button" onClick={() => setShowCreate(false)}>Cancel</button>
          </div>
        </form>
      )}

      <div className="card">
        {loading && <p className="muted">Loading…</p>}
        {!loading && meetings.length === 0 && <p className="muted">No meetings yet. Create one to get started!</p>}
        {!loading && meetings.length > 0 && (
          <table>
            <thead>
              <tr>
                <th>Title</th><th>Code</th><th>Start</th><th>Status</th><th>Participants</th><th></th>
              </tr>
            </thead>
            <tbody>
              {meetings.map((m) => (
                <tr key={m.id}>
                  <td><Link to={`/meetings/${m.id}`} style={{ color: 'var(--primary)' }}>{m.title}</Link></td>
                  <td><code>{m.meeting_code}</code></td>
                  <td>{m.start_time ? new Date(m.start_time).toLocaleString() : '—'}</td>
                  <td><span className={`badge ${m.status}`}>{m.status}</span></td>
                  <td>{m.participants_count ?? (m.participants?.length ?? '—')}</td>
                  <td style={{ textAlign: 'right' }}>
                    <Link to={`/rooms/${m.meeting_code}`} className="btn small">Enter room</Link>{' '}
                    <button className="btn small danger" onClick={() => remove(m)}>Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
