import { useState } from 'react';
import Swal from 'sweetalert2';
import { updateProfile } from '../api/endpoints';
import { useAuth } from '../context/AuthContext.jsx';

export default function Profile() {
  const { user, setUser } = useAuth();
  const [form, setForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    bio: user?.bio || '',
    company: user?.company || '',
    position: user?.position || '',
    phone: user?.phone || '',
  });
  const [avatar, setAvatar] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  const update = (key) => (e) => setForm({ ...form, [key]: e.target.value });

  const submit = async (e) => {
    e.preventDefault();
    setBusy(true);
    setError('');
    try {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => fd.append(k, v));
      if (avatar) fd.append('avatar', avatar);
      const { data } = await updateProfile(fd);
      setUser(data.user);
      localStorage.setItem('intellmeet_user', JSON.stringify(data.user));
      Swal.fire('Saved', 'Profile updated.', 'success');
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(errors ? Object.values(errors).flat().join(' ') : 'Update failed.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <>
      <h2 className="page-title">My Profile</h2>
      <form className="card" style={{ maxWidth: 560 }} onSubmit={submit}>
        <div className="form-group">
          <label>Name</label>
          <input required value={form.name} onChange={update('name')} />
        </div>
        <div className="form-group">
          <label>Email</label>
          <input type="email" required value={form.email} onChange={update('email')} />
        </div>
        <div className="grid cols-2">
          <div className="form-group">
            <label>Company</label>
            <input value={form.company} onChange={update('company')} />
          </div>
          <div className="form-group">
            <label>Position</label>
            <input value={form.position} onChange={update('position')} />
          </div>
          <div className="form-group">
            <label>Phone</label>
            <input value={form.phone} onChange={update('phone')} />
          </div>
          <div className="form-group">
            <label>Avatar</label>
            <input type="file" accept="image/*" onChange={(e) => setAvatar(e.target.files[0] || null)} />
          </div>
        </div>
        <div className="form-group">
          <label>Bio</label>
          <textarea value={form.bio} onChange={update('bio')} />
        </div>

        {error && <p className="error-text">{error}</p>}

        <button className="btn" disabled={busy}>{busy ? 'Saving…' : 'Save changes'}</button>
      </form>
    </>
  );
}
