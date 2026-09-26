import { useCallback, useEffect, useState } from 'react';
import Swal from 'sweetalert2';
import { fetchTeams, createTeam, deleteTeam, fetchTeam, addTeamMember, removeTeamMember } from '../api/endpoints';
import { useAuth } from '../context/AuthContext.jsx';

export default function Teams() {
  const { user } = useAuth();
  const [teams, setTeams] = useState([]);
  const [selected, setSelected] = useState(null); // full team with members
  const [loading, setLoading] = useState(true);
  const [name, setName] = useState('');
  const [memberEmail, setMemberEmail] = useState('');

  const load = useCallback(() => {
    setLoading(true);
    fetchTeams()
      .then(({ data }) => setTeams(data.data || []))
      .catch(() => Swal.fire('Error', 'Could not load teams.', 'error'))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const openTeam = (id) => {
    fetchTeam(id).then(({ data }) => setSelected(data.team)).catch(() => setSelected(null));
  };

  const submitCreate = async (e) => {
    e.preventDefault();
    if (!name.trim()) return;
    try {
      await createTeam({ name: name.trim() });
      setName('');
      Swal.fire('Team created', '', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Could not create team.', 'error');
    }
  };

  const invite = async (e) => {
    e.preventDefault();
    if (!memberEmail.trim() || !selected) return;
    try {
      await addTeamMember(selected.id, memberEmail.trim());
      setMemberEmail('');
      openTeam(selected.id);
      Swal.fire('Member added', '', 'success');
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'User not found.', 'error');
    }
  };

  const removeMember = async (userId) => {
    await removeTeamMember(selected.id, userId);
    openTeam(selected.id);
  };

  const remove = async (team) => {
    const ok = await Swal.fire({ title: 'Delete team?', text: team.name, icon: 'warning', showCancelButton: true });
    if (!ok.isConfirmed) return;
    await deleteTeam(team.id);
    if (selected?.id === team.id) setSelected(null);
    load();
  };

  return (
    <>
      <h2 className="page-title">Teams</h2>

      <form className="card row" onSubmit={submitCreate} style={{ gap: 10 }}>
        <input placeholder="New team name…" value={name} onChange={(e) => setName(e.target.value)} />
        <button className="btn">+ Create team</button>
      </form>

      {loading && <p className="muted">Loading…</p>}
      {!loading && teams.length === 0 && <p className="muted">You are not in any teams yet.</p>}

      <div className="grid cols-2">
        {teams.map((t) => (
          <div className="card" key={t.id}>
            <div className="row">
              <strong>{t.name}</strong>
              <span className="muted">{t.members_count ?? t.members?.length ?? 0} members</span>
            </div>
            {t.description && <p className="muted" style={{ marginTop: 6 }}>{t.description}</p>}
            <div className="row" style={{ marginTop: 12, gap: 8 }}>
              <button className="btn small secondary" onClick={() => openTeam(t.id)}>View members</button>
              {t.owner_id === user.id && (
                <button className="btn small danger" onClick={() => remove(t)}>Delete</button>
              )}
            </div>
          </div>
        ))}
      </div>

      {selected && (
        <div className="card" style={{ marginTop: 16 }}>
          <div className="row">
            <strong>{selected.name} — Members</strong>
            <button className="btn small secondary" onClick={() => setSelected(null)}>Close</button>
          </div>
          <table style={{ marginTop: 10 }}>
            <thead>
              <tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr>
            </thead>
            <tbody>
              {(selected.members || []).map((m) => (
                <tr key={m.id}>
                  <td>{m.name}</td>
                  <td>{m.email}</td>
                  <td>{m.id === selected.owner_id ? 'Owner' : m.role}</td>
                  <td style={{ textAlign: 'right' }}>
                    {selected.owner_id === user.id && m.id !== selected.owner_id && (
                      <button className="btn small danger" onClick={() => removeMember(m.id)}>Remove</button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {selected.owner_id === user.id && (
            <form className="row" onSubmit={invite} style={{ marginTop: 12, gap: 8 }}>
              <input type="email" placeholder="Add member by email…" value={memberEmail} onChange={(e) => setMemberEmail(e.target.value)} />
              <button className="btn">Add member</button>
            </form>
          )}
        </div>
      )}
    </>
  );
}
