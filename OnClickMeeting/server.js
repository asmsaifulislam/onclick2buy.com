const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { v4: uuidv4 } = require('uuid');
const Database = require('better-sqlite3');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const path = require('path');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });

const JWT_SECRET = 'onclickmeeting-secret-' + Date.now();
const PORT = process.env.PORT || 3200;

// Database
const db = new Database(path.join(__dirname, 'meeting.db'));
db.pragma('journal_mode = WAL');

db.exec(`CREATE TABLE IF NOT EXISTS users (
  id TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT UNIQUE,
  phone TEXT UNIQUE,
  password TEXT,
  avatar_color TEXT DEFAULT '#6366f1',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)`);

db.exec(`CREATE TABLE IF NOT EXISTS meetings (
  id TEXT PRIMARY KEY,
  room_id TEXT UNIQUE NOT NULL,
  host_id TEXT NOT NULL,
  host_token TEXT,
  title TEXT NOT NULL,
  description TEXT DEFAULT '',
  scheduled_at DATETIME,
  status TEXT DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (host_id) REFERENCES users(id)
)`);
try { db.exec("ALTER TABLE meetings ADD COLUMN host_token TEXT"); } catch(e) {}

db.exec(`CREATE TABLE IF NOT EXISTS meeting_participants (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  meeting_id TEXT NOT NULL,
  user_id TEXT NOT NULL,
  joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  left_at DATETIME
)`);

const createUser = db.prepare('INSERT INTO users (id, name, email, phone, password, avatar_color) VALUES (?, ?, ?, ?, ?, ?)');
const findUserByEmail = db.prepare('SELECT * FROM users WHERE email = ?');
const findUserByPhone = db.prepare('SELECT * FROM users WHERE phone = ?');
const findUserById = db.prepare('SELECT * FROM users WHERE id = ?');
const createMeeting = db.prepare('INSERT INTO meetings (id, room_id, host_id, host_token, title, description, scheduled_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
const getMeetingByRoom = db.prepare('SELECT * FROM meetings WHERE room_id = ?');
const getUserMeetings = db.prepare('SELECT * FROM meetings WHERE host_id = ? ORDER BY created_at DESC');
const updateMeetingStatus = db.prepare('UPDATE meetings SET status = ? WHERE room_id = ?');
const addParticipant = db.prepare('INSERT INTO meeting_participants (meeting_id, user_id) VALUES (?, ?)');
const removeParticipant = db.prepare('UPDATE meeting_participants SET left_at = CURRENT_TIMESTAMP WHERE meeting_id = ? AND user_id = ? AND left_at IS NULL');
const getActiveRooms = db.prepare("SELECT DISTINCT room_id FROM meetings WHERE status = 'active'");

const colors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#14b8a6'];

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Auth middleware
function auth(req, res, next) {
  const token = req.headers.authorization?.replace('Bearer ', '');
  if (!token) return res.status(401).json({ error: 'No token' });
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded;
    next();
  } catch { res.status(401).json({ error: 'Invalid token' }); }
}

// Auth Routes
app.post('/api/register', (req, res) => {
  const { name, email, phone, password } = req.body;
  if (!name || (!email && !phone)) return res.status(400).json({ error: 'Name and email or phone required' });

  const existing = email ? findUserByEmail.get(email) : findUserByPhone.get(phone);
  if (existing) return res.status(400).json({ error: 'User already exists' });

  const id = uuidv4();
  const hash = password ? bcrypt.hashSync(password, 10) : null;
  const color = colors[Math.floor(Math.random() * colors.length)];

  createUser.run(id, name, email || null, phone || null, hash, color);
  const token = jwt.sign({ id, name, email, phone }, JWT_SECRET, { expiresIn: '30d' });
  res.json({ token, user: { id, name, email, phone, avatar_color: color } });
});

app.post('/api/login', (req, res) => {
  const { email, phone, password } = req.body;
  const user = email ? findUserByEmail.get(email) : findUserByPhone.get(phone);
  if (!user) return res.status(400).json({ error: 'User not found' });
  if (user.password && !bcrypt.compareSync(password, user.password)) return res.status(400).json({ error: 'Wrong password' });

  const token = jwt.sign({ id: user.id, name: user.name, email: user.email, phone: user.phone }, JWT_SECRET, { expiresIn: '30d' });
  res.json({ token, user: { id: user.id, name: user.name, email: user.email, phone: user.phone, avatar_color: user.avatar_color } });
});

app.get('/api/me', auth, (req, res) => {
  const user = findUserById.get(req.user.id);
  if (!user) return res.status(404).json({ error: 'Not found' });
  res.json({ id: user.id, name: user.name, email: user.email, phone: user.phone, avatar_color: user.avatar_color });
});

// Meeting Routes
app.post('/api/meetings', auth, (req, res) => {
  const { title, description, scheduled_at } = req.body;
  const id = uuidv4();
  const room_id = 'OCM-' + uuidv4().substring(0, 8).toUpperCase();
  const host_token = uuidv4().substring(0, 12);

  createMeeting.run(id, room_id, req.user.id, host_token, title || 'Instant Meeting', description || '', scheduled_at || null);
  res.json({ id, room_id, host_token, title: title || 'Instant Meeting' });
});

app.get('/api/meetings', auth, (req, res) => {
  const meetings = getUserMeetings.all(req.user.id);
  res.json(meetings);
});

app.get('/api/meetings/:roomId', (req, res) => {
  const meeting = getMeetingByRoom.get(req.params.roomId);
  if (!meeting) return res.status(404).json({ error: 'Meeting not found' });
  res.json(meeting);
});

// Dashboard
app.get('/', (req, res) => res.sendFile(path.join(__dirname, 'public', 'index.html')));
app.get('/room/:roomId', (req, res) => res.sendFile(path.join(__dirname, 'public', 'room.html')));

// Socket.io - WebRTC Signaling
const rooms = new Map();
// rooms(roomId) = { participants: Map<socketId, {userId, userName, socket, isHost}>, waiting: Map<socketId, {userId, userName, socket}> }

io.on('connection', (socket) => {
  console.log('Connected:', socket.id);

  socket.on('join-room', ({ roomId, userId, userName, hostToken }) => {
    socket.join(roomId);
    socket.roomId = roomId;
    socket.userId = userId;
    socket.userName = userName;

    if (!rooms.has(roomId)) {
      const meeting = getMeetingByRoom.get(roomId);
      rooms.set(roomId, { participants: new Map(), waiting: new Map(), hostId: meeting ? meeting.host_id : null, hostToken: meeting ? meeting.host_token : null });
    }
    const room = rooms.get(roomId);

    // Determine host:
    // 1. DB meeting exists + userId matches host_id
    // 2. DB meeting exists + hostToken matches
    // 3. No DB meeting + first person = host
    // 4. No DB meeting + no participants = first person = host
    let isHost = false;
    if (room.hostId) {
      isHost = (room.hostId === userId);
      if (!isHost && hostToken && room.hostToken) {
        isHost = (room.hostToken === hostToken);
      }
    } else if (room.participants.size === 0) {
      isHost = true;
    }

    console.log(`[JOIN] ${userName} in ${roomId} | hostId=${room.hostId} | userId=${userId} | hostToken=${hostToken} | roomToken=${room.hostToken} | isHost=${isHost} | participants=${room.participants.size}`);

    // If NOT host and room has participants -> go to waiting room
    if (!isHost && room.participants.size > 0) {
      room.waiting.set(socket.id, { userId, userName, socket });
      let notified = false;
      room.participants.forEach((p, sid) => {
        if (p.isHost) {
          io.to(sid).emit('waiting-user', { socketId: socket.id, userId, userName });
          notified = true;
        }
      });
      socket.emit('waiting-room', { message: 'Waiting for host approval...' });
      console.log(`[WAITING] ${userName} -> waiting room in ${roomId} (notified=${notified})`);
      return;
    }

    room.participants.set(socket.id, { userId, userName, socket, isHost });

    const participants = [];
    room.participants.forEach((p, sid) => {
      if (sid !== socket.id) participants.push({ socketId: sid, userId: p.userId, userName: p.userName, isHost: p.isHost });
    });

    let waitingList = [];
    if (isHost) {
      room.waiting.forEach((w, sid) => {
        waitingList.push({ socketId: sid, userId: w.userId, userName: w.userName });
      });
    }

    socket.emit('room-joined', { participants, socketId: socket.id, isHost, waitingList });
    socket.to(roomId).emit('user-joined', { socketId: socket.id, userId, userName, isHost });

    console.log(`[JOINED] ${userName} in ${roomId} (${room.participants.size} users, host: ${isHost})`);
  });

  function isUserHost(room, participant) {
    if (participant.isHost) return true;
    if (room.hostId && room.hostId === participant.userId) return true;
    return false;
  }

  socket.on('approve-user', ({ roomId, targetSocketId }) => {
    const room = rooms.get(roomId);
    if (!room) { console.log(`[APPROVE] Room ${roomId} not found`); return; }
    const requester = room.participants.get(socket.id);
    if (!requester || !isUserHost(room, requester)) { console.log(`[APPROVE] Not host: ${requester?.userName}`); return; }

    const waitingUser = room.waiting.get(targetSocketId);
    if (!waitingUser) { console.log(`[APPROVE] Target ${targetSocketId} not in waiting`); return; }

    room.waiting.delete(targetSocketId);
    room.participants.set(targetSocketId, { userId: waitingUser.userId, userName: waitingUser.userName, socket: waitingUser.socket, isHost: false });

    // Build participant list for the approved user
    const participants = [];
    room.participants.forEach((p, sid) => {
      if (sid !== targetSocketId) participants.push({ socketId: sid, userId: p.userId, userName: p.userName, isHost: p.isHost });
    });

    // Tell the approved user they're in (with list of existing participants)
    waitingUser.socket.emit('room-joined', { participants, socketId: targetSocketId, isHost: false, waitingList: [] });

    // Tell ALL existing participants about the new user (including the host who approved)
    room.participants.forEach((p, sid) => {
      if (sid !== targetSocketId) {
        io.to(sid).emit('user-joined', { socketId: targetSocketId, userId: waitingUser.userId, userName: waitingUser.userName, isHost: false });
      }
    });

    socket.emit('user-approved', { socketId: targetSocketId, userName: waitingUser.userName });

    console.log(`[APPROVE] ${waitingUser.userName} approved by ${requester.userName} in ${roomId} (${room.participants.size} total)`);
  });

  socket.on('deny-user', ({ roomId, targetSocketId }) => {
    const room = rooms.get(roomId);
    if (!room) return;
    const requester = room.participants.get(socket.id);
    if (!requester || !isUserHost(room, requester)) return;

    const waitingUser = room.waiting.get(targetSocketId);
    if (!waitingUser) return;

    room.waiting.delete(targetSocketId);
    waitingUser.socket.emit('join-denied', { message: 'Host denied your request to join.' });

    console.log(`${waitingUser.userName} denied by ${socket.userName} in ${roomId}`);
  });

  socket.on('admit-all', ({ roomId }) => {
    const room = rooms.get(roomId);
    if (!room) return;
    const requester = room.participants.get(socket.id);
    if (!requester || !isUserHost(room, requester)) return;

    const waitingIds = [...room.waiting.keys()];
    waitingIds.forEach(targetSocketId => {
      const waitingUser = room.waiting.get(targetSocketId);
      if (!waitingUser) return;

      room.waiting.delete(targetSocketId);
      room.participants.set(targetSocketId, { userId: waitingUser.userId, userName: waitingUser.userName, socket: waitingUser.socket, isHost: false });

      const participants = [];
      room.participants.forEach((p, sid) => {
        if (sid !== targetSocketId) participants.push({ socketId: sid, userId: p.userId, userName: p.userName, isHost: p.isHost });
      });

      waitingUser.socket.emit('room-joined', { participants, socketId: targetSocketId, isHost: false, waitingList: [] });

      room.participants.forEach((p, sid) => {
        if (sid !== targetSocketId) {
          io.to(sid).emit('user-joined', { socketId: targetSocketId, userId: waitingUser.userId, userName: waitingUser.userName, isHost: false });
        }
      });
    });

    console.log(`All waiting users admitted in ${roomId}`);
  });

  socket.on('offer', ({ to, offer }) => {
    io.to(to).emit('offer', { from: socket.id, offer });
  });

  socket.on('answer', ({ to, answer }) => {
    io.to(to).emit('answer', { from: socket.id, answer });
  });

  socket.on('ice-candidate', ({ to, candidate }) => {
    io.to(to).emit('ice-candidate', { from: socket.id, candidate });
  });

  socket.on('chat-message', ({ roomId, message, userName }) => {
    io.to(roomId).emit('chat-message', {
      id: uuidv4(),
      message,
      userName,
      timestamp: new Date().toISOString()
    });
  });

  socket.on('toggle-audio', ({ roomId, muted }) => {
    socket.to(roomId).emit('user-toggle-audio', { socketId: socket.id, muted });
  });

  socket.on('toggle-video', ({ roomId, off }) => {
    socket.to(roomId).emit('user-toggle-video', { socketId: socket.id, off });
  });

  socket.on('screen-share-started', ({ roomId }) => {
    socket.to(roomId).emit('user-screen-share', { socketId: socket.id, sharing: true });
  });

  socket.on('screen-share-stopped', ({ roomId }) => {
    socket.to(roomId).emit('user-screen-share', { socketId: socket.id, sharing: false });
  });

  socket.on('hand-raise', ({ roomId }) => {
    io.to(roomId).emit('user-hand-raise', { socketId: socket.id, userName: socket.userName });
  });

  socket.on('invite-user', ({ roomId, invitee, userName }) => {
    io.to(roomId).emit('invite-notification', { from: userName, invitee });
  });

  socket.on('promote-to-host', ({ roomId, targetSocketId }) => {
    const room = rooms.get(roomId);
    if (!room) return;
    const requester = room.participants.get(socket.id);
    if (!requester || !requester.isHost) return;
    const target = room.participants.get(targetSocketId);
    if (!target) return;
    target.isHost = true;
    io.to(targetSocketId).emit('promoted-to-host', { byName: socket.userName });
    io.to(roomId).emit('user-promoted', { socketId: targetSocketId, userName: target.userName });
    console.log(`${target.userName} promoted to co-host by ${socket.userName} in ${roomId}`);
  });

  socket.on('demote-host', ({ roomId, targetSocketId }) => {
    const room = rooms.get(roomId);
    if (!room) return;
    const requester = room.participants.get(socket.id);
    if (!requester || !requester.isHost) return;
    const target = room.participants.get(targetSocketId);
    if (!target) return;
    target.isHost = false;
    io.to(targetSocketId).emit('demoted-from-host', { byName: socket.userName });
    io.to(roomId).emit('user-demoted', { socketId: targetSocketId, userName: target.userName });
    console.log(`${target.userName} demoted by ${socket.userName} in ${roomId}`);
  });

  socket.on('remote-control-request', ({ roomId, targetSocketId, fromName }) => {
    io.to(targetSocketId).emit('remote-control-request', { from: socket.id, fromName });
  });

  socket.on('remote-control-approved', ({ roomId, targetSocketId }) => {
    io.to(targetSocketId).emit('remote-control-approved', { from: socket.id });
  });

  socket.on('remote-control-denied', ({ roomId, targetSocketId }) => {
    io.to(targetSocketId).emit('remote-control-denied', { from: socket.id });
  });

  socket.on('remote-control-stopped', ({ roomId }) => {
    socket.to(roomId).emit('remote-control-stopped', { from: socket.id });
  });

  socket.on('end-meeting', ({ roomId }) => {
    updateMeetingStatus.run('ended', roomId);
    io.to(roomId).emit('meeting-ended');
    if (rooms.has(roomId)) {
      rooms.get(roomId).forEach((p, sid) => {
        const s = io.sockets.sockets.get(sid);
        if (s) s.leave(roomId);
      });
      rooms.delete(roomId);
    }
  });

  socket.on('disconnect', () => {
    if (socket.roomId && rooms.has(socket.roomId)) {
      const room = rooms.get(socket.roomId);
      const wasWaiting = room.waiting.has(socket.id);
      room.participants.delete(socket.id);
      room.waiting.delete(socket.id);
      socket.to(socket.roomId).emit('user-left', { socketId: socket.id });
      if (wasWaiting) {
        socket.to(socket.roomId).emit('waiting-user-left', { socketId: socket.id });
        console.log(`[WAITING] ${socket.userName} left waiting room`);
      }
      if (room.participants.size === 0 && room.waiting.size === 0) rooms.delete(socket.roomId);
    }
    console.log('Disconnected:', socket.id);
  });
});

server.listen(PORT, () => {
  console.log(`OnClickMeeting running on port ${PORT}`);
});
