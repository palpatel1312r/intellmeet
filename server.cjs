const express = require("express");
const http = require("http");
const socketIO = require("socket.io");

const app = express();
const server = http.createServer(app);
const io = socketIO(server, {
    cors: {
        origin: "http://127.0.0.1:8000",
        methods: ["GET", "POST"],
        credentials: true,
    },
});

// Store meeting rooms
const meetings = new Map();

io.on("connection", (socket) => {
    console.log("✅ Client connected:", socket.id);

    socket.on("join-meeting", (data) => {
        const { meetingCode, userId, userName } = data;

        console.log(
            `📢 User ${userName} (${userId}) joining meeting: ${meetingCode}`,
        );
        socket.join(meetingCode);

        if (!meetings.has(meetingCode)) {
            meetings.set(meetingCode, new Map());
        }

        const meeting = meetings.get(meetingCode);
        meeting.set(userId, {
            socketId: socket.id,
            userId: userId,
            userName: userName,
        });

        socket.to(meetingCode).emit("user-joined", { userId, userName });

        const existingUsers = Array.from(meeting.values())
            .filter((user) => user.userId != userId)
            .map((user) => ({ userId: user.userId, userName: user.userName }));

        if (existingUsers.length > 0) {
            console.log(
                `📋 Sending existing users to ${userName}:`,
                existingUsers,
            );
            socket.emit("existing-users", existingUsers);
        }

        console.log(
            `👥 Meeting ${meetingCode} now has ${meeting.size} participants`,
        );
    });

    socket.on("offer", (data) => {
        const { to, from, offer, meetingCode } = data;
        console.log(`📤 Offer from ${from} to ${to} in meeting ${meetingCode}`);
        socket.to(meetingCode).emit("offer", { from, to, offer });
    });

    socket.on("answer", (data) => {
        const { to, from, answer, meetingCode } = data;
        console.log(
            `📤 Answer from ${from} to ${to} in meeting ${meetingCode}`,
        );
        socket.to(meetingCode).emit("answer", { from, to, answer });
    });

    socket.on("ice-candidate", (data) => {
        const { to, from, candidate, meetingCode } = data;
        console.log(`🧊 ICE candidate from ${from} to ${to}`);
        socket.to(meetingCode).emit("ice-candidate", { from, to, candidate });
    });

    // Fixed chat-message handler - broadcasts to others only
    socket.on("chat-message", (data) => {
        const { meetingCode, message, userName } = data;
        console.log(`💬 Chat in ${meetingCode}: ${userName}: ${message}`);
        // This sends to all clients EXCEPT the sender
        socket.to(meetingCode).emit("chat-message", { userName, message });
    });

    socket.on("disconnect", () => {
        console.log("❌ Client disconnected:", socket.id);

        for (const [meetingCode, meeting] of meetings.entries()) {
            let disconnectedUser = null;
            for (const [userId, user] of meeting.entries()) {
                if (user.socketId === socket.id) {
                    disconnectedUser = user;
                    meeting.delete(userId);
                    break;
                }
            }

            if (disconnectedUser) {
                console.log(
                    `👋 User ${disconnectedUser.userName} left meeting ${meetingCode}`,
                );
                io.to(meetingCode).emit("user-left", {
                    userId: disconnectedUser.userId,
                    userName: disconnectedUser.userName,
                });

                if (meeting.size === 0) {
                    meetings.delete(meetingCode);
                    console.log(`🗑️ Meeting ${meetingCode} removed (empty)`);
                }
                break;
            }
        }
    });
});

// Use port 3001 (or try 3002 if 3001 is busy)
const PORT = 3000;
server
    .listen(PORT, () => {
        console.log(`🚀 Socket.io server running on port ${PORT}`);
        console.log(`📡 WebSocket URL: ws://127.0.0.1:${PORT}`);
    })
    .on("error", (err) => {
        if (err.code === "EADDRINUSE") {
            console.log(
                `❌ Port ${PORT} is already in use. Trying port 3002...`,
            );
            const PORT2 = 3002;
            server.listen(PORT2);
        }
    });
