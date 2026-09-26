import { useCallback, useEffect, useState } from 'react';
import { fetchNotifications, markNotificationRead, markAllNotificationsRead } from '../api/endpoints';

export default function Notifications() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    setLoading(true);
    fetchNotifications()
      .then(({ data }) => setItems(data.data || []))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const readOne = async (n) => {
    if (n.read) return;
    await markNotificationRead(n.id);
    setItems((prev) => prev.map((x) => (x.id === n.id ? { ...x, read: true } : x)));
  };

  const readAll = async () => {
    await markAllNotificationsRead();
    setItems((prev) => prev.map((x) => ({ ...x, read: true })));
  };

  return (
    <>
      <div className="row" style={{ marginBottom: 16 }}>
        <h2 className="page-title" style={{ margin: 0 }}>Notifications</h2>
        <button className="btn secondary" onClick={readAll}>Mark all as read</button>
      </div>

      {loading && <p className="muted">Loading…</p>}
      {!loading && items.length === 0 && <p className="muted">You're all caught up 🎉</p>}

      {items.map((n) => (
        <div
          key={n.id}
          className="card row"
          style={{ opacity: n.read ? 0.6 : 1, cursor: 'pointer' }}
          onClick={() => readOne(n)}
        >
          <div>
            <strong>{n.title || n.type}</strong>
            <p className="muted">{n.message}</p>
          </div>
          <span className="muted">{n.created_at ? new Date(n.created_at).toLocaleString() : ''}</span>
        </div>
      ))}
    </>
  );
}
