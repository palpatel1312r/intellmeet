import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { register } from '../api/endpoints';
import { useAuth } from '../context/AuthContext.jsx';

export default function Register() {
  const navigate = useNavigate();
  const { setUser } = useAuth();
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const update = (key) => (e) => setForm({ ...form, [key]: e.target.value });

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      const { data } = await register(form);
      localStorage.setItem('intellmeet_token', data.token);
      localStorage.setItem('intellmeet_user', JSON.stringify(data.user));
      setUser(data.user);
      navigate('/dashboard');
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(errors ? Object.values(errors).flat().join(' ') : 'Registration failed.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-wrap">
      <form className="card auth-card" onSubmit={submit}>
        <h1>Create your account</h1>
        <p className="sub">Join IntelliMeets — AI-powered meetings</p>

        <div className="form-group">
          <label>Full name</label>
          <input required value={form.name} onChange={update('name')} placeholder="Jane Doe" />
        </div>
        <div className="form-group">
          <label>Email</label>
          <input type="email" required value={form.email} onChange={update('email')} placeholder="you@example.com" />
        </div>
        <div className="form-group">
          <label>Password</label>
          <input type="password" required minLength={8} value={form.password} onChange={update('password')} />
        </div>
        <div className="form-group">
          <label>Confirm password</label>
          <input type="password" required value={form.password_confirmation} onChange={update('password_confirmation')} />
        </div>

        {error && <p className="error-text">{error}</p>}

        <button className="btn" style={{ width: '100%', marginTop: 8 }} disabled={busy}>
          {busy ? 'Creating account…' : 'Create account'}
        </button>

        <p className="muted" style={{ marginTop: 14 }}>
          Already registered? <Link to="/login" style={{ color: 'var(--primary)' }}>Sign in</Link>
        </p>
      </form>
    </div>
  );
}
