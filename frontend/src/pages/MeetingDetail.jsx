import { useCallback, useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import Swal from 'sweetalert2';
import {
  fetchMeeting,
  updateMeeting,
  endMeeting,
  summarizeMeeting,
  extractActionItems,
} from '../api/endpoints';

export default function MeetingDetail() {
  const { id } = useParams();
  const [meeting, setMeeting] = useState(null);
  const [busy, setBusy] = useState('');
  const [error, setError] = useState('');

  const load = useCallback(() => {
    fetchMeeting(id)
      .then(({ data }) => setMeeting(data.meeting))
      .catch(() => setError('Meeting not found or access denied.'));
  }, [id]);

  useEffect(load, [load]);

  const runAI = async (kind) => {
    setBusy(kind);
    try {
      if (kind === 'summarize') await summarizeMeeting(id);
      if (kind === 'actions') await extractActionItems(id);
      load();
      Swal.fire('Done ✨', `AI ${kind === 'summarize' ? 'summary generated' : 'action items extracted'}.`, 'success');
    } catch (err) {
      Swal.fire('AI error', err.response?.data?.message || 'Request failed.', 'error');
    } finally {
      setBusy('');
    }
  };

  const changeStatus = async (status) => {
    await updateMeeting(id, { status });
    load();
  };

  if (error) return <p className="error-text">{error}</p>;
  if (!meeting) return <p className="muted">Loading…</p>;

  return (
    <>
      <div className="row" style={{ marginBottom: 16 }}>
        <h2 className="page-title" style={{ margin: 0 }}>{meeting.title}</h2>
        <div className="row" style={{ gap: 8 }}>
          <span className={`badge ${meeting.status}`}>{meeting.status}</span>
          <Link to={`/rooms/${meeting.meeting_code}`} className="btn">Enter room</Link>
        </div>
      </div>

      <div className="grid cols-2">
        <div className="card">
          <strong>Details</strong>
          <p className="muted" style={{ marginTop: 8 }}>{meeting.description || 'No description.'}</p>
          <table style={{ marginTop: 12 }}>
            <tbody>
              <tr><th>Code</th><td><code>{meeting.meeting_code}</code></td></tr>
              <tr><th>Host</th><td>{meeting.creator?.name || '—'}</td></tr>
              <tr><th>Start</th><td>{meeting.start_time ? new Date(meeting.start_time).toLocaleString() : '—'}</td></tr>
              <tr><th>End</th><td>{meeting.end_time ? new Date(meeting.end_time).toLocaleString() : '—'}</td></tr>
            </tbody>
          </table>

          <div style={{ marginTop: 12 }}>
            <label>Status</label>
            <select value={meeting.status} onChange={(e) => changeStatus(e.target.value)}>
              <option value="scheduled">Scheduled</option>
              <option value="ongoing">Ongoing</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <div className="card">
          <strong>Participants ({meeting.participants?.length ?? 0})</strong>
          <ul style={{ marginTop: 10, paddingLeft: 18 }}>
            {(meeting.participants || []).map((p) => (
              <li key={p.id} style={{ marginBottom: 4 }}>
                {p.name} <span className="muted">({p.email})</span>
              </li>
            ))}
          </ul>
          <div className="controls" style={{ marginTop: 16 }}>
            <button className="btn secondary" onClick={() => navigator.clipboard.writeText(meeting.join_link || meeting.meeting_code)}>
              📋 Copy invite link
            </button>
            {meeting.status === 'ongoing' && (
              <button className="btn danger" onClick={async () => { await endMeeting(id); load(); }}>
                End meeting
              </button>
            )}
          </div>
        </div>
      </div>

      <div className="card">
        <div className="row">
          <strong>🤖 AI Assistant</strong>
          <div className="row" style={{ gap: 8 }}>
            <button className="btn secondary" disabled={!!busy} onClick={() => runAI('summarize')}>
              {busy === 'summarize' ? 'Summarizing…' : 'Generate summary'}
            </button>
            <button className="btn secondary" disabled={!!busy} onClick={() => runAI('actions')}>
              {busy === 'actions' ? 'Extracting…' : 'Extract action items'}
            </button>
          </div>
        </div>
        {meeting.summary && (
          <p style={{ marginTop: 12, whiteSpace: 'pre-wrap' }}>{meeting.summary}</p>
        )}
      </div>

      <div className="card">
        <strong>Action Items</strong>
        {(meeting.action_items || []).length === 0 && <p className="muted" style={{ marginTop: 8 }}>None yet — run the AI extractor after a meeting.</p>}
        <ul style={{ marginTop: 10, paddingLeft: 18 }}>
          {(meeting.action_items || []).map((a) => (
            <li key={a.id} style={{ marginBottom: 6 }}>
              {a.title} {a.due_date ? <span className="muted">(due {a.due_date})</span> : null}
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}
