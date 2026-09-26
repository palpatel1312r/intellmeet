import { useEffect, useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { io } from 'socket.io-client';
import Swal from 'sweetalert2';
import { useAuth } from '../context/AuthContext.jsx';
import { joinMeetingByCode, endMeeting } from '../api/endpoints';

const SOCKET_URL = import.meta.env.VITE_SOCKET_URL || 'http://localhost:3000';
const ICE_SERVERS = [{ urls: 'stun:stun.l.google.com:19302' }];

/**
 * Video room wired to the IntelliMeets Socket.io signaling server (server.cjs)
 * using the exact events it emits: join-meeting, offer, answer, ice-candidate,
 * chat-message, user-joined, existing-users, user-left.
 */
export default function VideoRoom() {
  const { code } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth();

  const socketRef = useRef(null);
  const peersRef = useRef({}); // userId -> { peerConnection, streams }
  const localStreamRef = useRef(null);
  const localVideoRef = useRef(null);

  const [participants, setParticipants] = useState([]); // remote users with streams
  const [messages, setMessages] = useState([]);
  const [chatInput, setChatInput] = useState('');
  const [micOn, setMicOn] = useState(true);
  const [camOn, setCamOn] = useState(true);
  const [connected, setConnected] = useState(false);

  // ---- Setup: media + socket + WebRTC helpers ------------------------------
  useEffect(() => {
    let cancelled = false;

    const createPeerConnection = (remoteUserId, remoteUserName) => {
      const pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

      pc.onicecandidate = (e) => {
        if (e.candidate) {
          socketRef.current.emit('ice-candidate', {
            to: remoteUserId, from: user.id, candidate: e.candidate, meetingCode: code,
          });
        }
      };

      pc.ontrack = (e) => {
        const stream = e.streams[0];
        setParticipants((prev) => {
          const others = prev.filter((p) => p.userId !== remoteUserId);
          return [...others, { userId: remoteUserId, userName: remoteUserName, stream }];
        });
      };

      if (localStreamRef.current) {
        localStreamRef.current.getTracks().forEach((track) => pc.addTrack(track, localStreamRef.current));
      }

      peersRef.current[remoteUserId] = { pc, userName: remoteUserName };
      return pc;
    };

    const closePeer = (remoteUserId) => {
      const peer = peersRef.current[remoteUserId];
      if (peer) {
        peer.pc.close();
        delete peersRef.current[remoteUserId];
      }
      setParticipants((prev) => prev.filter((p) => p.userId !== remoteUserId));
    };

    (async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        if (cancelled) { stream.getTracks().forEach((t) => t.stop()); return; }
        localStreamRef.current = stream;
        if (localVideoRef.current) localVideoRef.current.srcObject = stream;
      } catch {
        if (!cancelled) Swal.fire('No camera/mic', 'Running in view-only mode.', 'warning');
      }

      const socket = io(SOCKET_URL, { transports: ['websocket'] });
      socketRef.current = socket;

      socket.on('connect', () => {
        setConnected(true);
        socket.emit('join-meeting', { meetingCode: code, userId: user.id, userName: user.name });
      });

      socket.on('existing-users', async (users) => {
        // We are newer: initiate offers to everyone already in the room.
        for (const u of users) {
          const pc = createPeerConnection(u.userId, u.userName);
          const offer = await pc.createOffer();
          await pc.setLocalDescription(offer);
          socket.emit('offer', { to: u.userId, from: user.id, offer, meetingCode: code });
        }
      });

      socket.on('user-joined', async ({ userId, userName }) => {
        setParticipants((prev) =>
          prev.some((p) => p.userId === userId) ? prev : [...prev, { userId, userName, stream: null }]
        );
      });

      socket.on('offer', async ({ from, offer }) => {
        const name = participants.find((p) => p.userId === from)?.userName || 'Guest';
        const pc = createPeerConnection(from, name);
        await pc.setRemoteDescription(new RTCSessionDescription(offer));
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        socket.emit('answer', { to: from, from: user.id, answer, meetingCode: code });
      });

      socket.on('answer', async ({ from, answer }) => {
        const peer = peersRef.current[from];
        if (peer) await peer.pc.setRemoteDescription(new RTCSessionDescription(answer));
      });

      socket.on('ice-candidate', async ({ from, candidate }) => {
        const peer = peersRef.current[from];
        if (peer) {
          try { await peer.pc.addIceCandidate(new RTCIceCandidate(candidate)); } catch { /* ignore */ }
        }
      });

      socket.on('chat-message', ({ userName, message }) => {
        setMessages((prev) => [...prev, { userName, message, mine: false }]);
      });

      socket.on('user-left', ({ userId }) => closePeer(userId));

      socket.on('disconnect', () => setConnected(false));
    })();

    return () => {
      cancelled = true;
      Object.values(peersRef.current).forEach(({ pc }) => pc.close());
      peersRef.current = {};
      localStreamRef.current?.getTracks().forEach((t) => t.stop());
      socketRef.current?.disconnect();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [code, user.id]);

  // ---- Controls -------------------------------------------------------------
  const toggleMic = () => {
    const next = !micOn;
    localStreamRef.current?.getAudioTracks().forEach((t) => (t.enabled = next));
    setMicOn(next);
  };

  const toggleCam = () => {
    const next = !camOn;
    localStreamRef.current?.getVideoTracks().forEach((t) => (t.enabled = next));
    setCamOn(next);
  };

  const sendChat = (e) => {
    e.preventDefault();
    const text = chatInput.trim();
    if (!text) return;
    socketRef.current?.emit('chat-message', { meetingCode: code, message: text, userName: user.name });
    setMessages((prev) => [...prev, { userName: user.name, message: text, mine: true }]);
    setChatInput('');
  };

  const leave = async () => {
    try { await endMeetingByCode(); } catch { /* optional */ }
    navigate('/meetings');
  };

  const endMeetingByCode = async () => {
    // Resolve meeting id from code via join endpoint, then mark ended.
    const { data } = await joinMeetingByCode(code);
    if (data.meeting?.id) await endMeeting(data.meeting.id);
  };

  return (
    <>
      <div className="row" style={{ marginBottom: 16 }}>
        <h2 className="page-title" style={{ margin: 0 }}>
          Room <code>{code}</code>{' '}
          <span className={`badge ${connected ? 'ongoing' : 'cancelled'}`}>
            {connected ? '● Connected' : '○ Disconnected'}
          </span>
        </h2>
        <button className="btn danger" onClick={leave}>Leave / End meeting</button>
      </div>

      <div className="controls">
        <button className={`btn ${micOn ? 'secondary' : 'danger'}`} onClick={toggleMic}>
          {micOn ? '🎤 Mute' : '🔇 Unmute'}
        </button>
        <button className={`btn ${camOn ? 'secondary' : 'danger'}`} onClick={toggleCam}>
          {camOn ? '📷 Camera on' : '🚫 Camera off'}
        </button>
      </div>

      <div className="video-grid">
        <div className="video-tile">
          <video ref={localVideoRef} autoPlay playsInline muted />
          <span className="name-tag">{user.name} (You)</span>
        </div>
        {participants.map((p) => (
          <div className="video-tile" key={p.userId}>
            {p.stream ? (
              <video autoPlay playsInline ref={(el) => el && (el.srcObject = p.stream)} />
            ) : (
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%', color: '#94a3b8' }}>
                Waiting for {p.userName}…
              </div>
            )}
            <span className="name-tag">{p.userName}</span>
          </div>
        ))}
      </div>

      <div className="card chat-panel">
        <strong>💬 Chat</strong>
        <div className="chat-messages">
          {messages.length === 0 && <p className="muted">No messages yet.</p>}
          {messages.map((m, i) => (
            <div className="chat-bubble" key={i}>
              <span className="who">{m.userName}:</span> {m.message}
            </div>
          ))}
        </div>
        <form className="chat-input-row" onSubmit={sendChat}>
          <input
            placeholder="Type a message…"
            value={chatInput}
            onChange={(e) => setChatInput(e.target.value)}
          />
          <button className="btn">Send</button>
        </form>
      </div>
    </>
  );
}
