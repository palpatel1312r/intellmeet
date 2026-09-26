# IntelliMeets — Laravel API + React Frontend

IntelliMeets is an AI-powered meeting platform: scheduled meetings, WebRTC video
rooms with Socket.io signaling, tasks, teams, notifications and AI summaries /
action-item extraction.

```
/workspace
├── app/                      # Laravel backend (Blade views kept for legacy)
│   └── Http/Controllers/Api/ # NEW: JSON API controllers for the React SPA
│   └── Http/Resources/       # NEW: API Resources (JSON transformers)
├── routes/api.php            # Sanctum-token API routes consumed by React
├── server.cjs                # Socket.io signaling server (video rooms + chat)
└── frontend/                 # NEW: React JS SPA (Vite)
    └── src/
        ├── api/              # axios client + endpoint helpers
        ├── context/          # AuthContext (token persistence, login/logout)
        ├── components/       # Layout (sidebar/topbar)
        └── pages/            # Login, Register, Dashboard, Meetings,
                              # MeetingDetail, VideoRoom, Tasks, Teams,
                              # Notifications, Profile
```

## Prerequisites

- PHP ≥ 8.2 with Composer
- Node.js ≥ 18

## 1. Backend (Laravel)

```bash
cd /workspace
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env (SQLite works out of the box)
php artisan migrate
php artisan storage:link
php artisan serve          # http://127.0.0.1:8000
```

Auth for the SPA uses **Laravel Sanctum tokens** (`Bearer` header). The existing
session-based Blade routes are untouched. CORS is enabled via `config/cors.php`
(allowing `http://localhost:5173`).

### API overview (`/api`)

| Method | URI | Description |
|---|---|---|
| POST | `/auth/register` | Create account, returns token |
| POST | `/auth/login` | Returns Sanctum token |
| GET  | `/auth/me` | Current user |
| DELETE | `/auth/logout` | Revoke current token |
| POST | `/auth/profile` | Update profile (multipart, avatar) |
| GET  | `/dashboard` | Stats + recent meetings/tasks |
| GET/POST | `/meetings` | List / create (auto join-code) |
| GET/PUT/DELETE | `/meetings/{id}` | Show / update / delete |
| POST | `/meetings/{id}/end` | End meeting |
| POST | `/join/{code}` | Join meeting by code |
| POST | `/meetings/{id}/summarize` | AI summary |
| POST | `/meetings/{id}/action-items` | AI action items |
| GET/POST | `/tasks`, `/teams` | CRUD (REST) |
| POST/DELETE | `/teams/{id}/members[/{userId}]` | Manage members |
| GET/POST | `/notifications…` | List / mark read |

## 2. WebSocket signaling server (video rooms)

```bash
cd /workspace
npm install
node server.cjs            # http://localhost:3000  (PORT env respected)
```

Events used by the React `VideoRoom`: `join-meeting`, `existing-users`,
`user-joined`, `offer`, `answer`, `ice-candidate`, `chat-message`, `user-left`.

## 3. Frontend (React + Vite)

```bash
cd frontend
npm install
npm run dev                # http://localhost:5173
```

Environment (optional) — `frontend/.env`:

```
VITE_API_URL=http://127.0.0.1:8000/api
VITE_SOCKET_URL=http://localhost:3000
```

Without `.env`, the dev server proxies `/api` → `http://127.0.0.1:8000`
(see `frontend/vite.config.js`) and sockets default to `http://localhost:3000`.

### Running everything together

Three terminals:

1. `php artisan serve` (backend, port 8000)
2. `node server.cjs` (signaling, port 3000)
3. `cd frontend && npm run dev` (SPA, port 5173)

Then open http://localhost:5173, register, create a meeting, and share the
room code — two browsers can join the same room for live WebRTC video + chat.
