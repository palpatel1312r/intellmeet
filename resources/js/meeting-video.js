import "./bootstrap";
// Meeting Video Room JavaScript
// Configuration
let meetingCode = null;
let userId = null;
let userName = null;

// WebRTC Variables
let localStream = null;
let peerConnections = {};
let socket = null;
let isAudioEnabled = true;
let isVideoEnabled = true;

// Get initials from name
function getInitials(name) {
    return name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
}

// Get random color based on name
function getAvatarColor(name) {
    const colors = [
        "avatar-color-0",
        "avatar-color-1",
        "avatar-color-2",
        "avatar-color-3",
        "avatar-color-4",
        "avatar-color-5",
        "avatar-color-6",
        "avatar-color-7",
    ];

    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
}

// Create avatar element
function createAvatarElement(targetUserId, userName, isVideoOn = false) {
    const container = document.getElementById(`container-${targetUserId}`);
    if (!container) return;

    const existingAvatar = container.querySelector(".participant-avatar");
    if (existingAvatar) existingAvatar.remove();

    if (!isVideoOn) {
        const avatar = document.createElement("div");
        avatar.className = `participant-avatar ${getAvatarColor(userName)}`;
        avatar.innerHTML = getInitials(userName);
        container.appendChild(avatar);
        container.classList.add("video-off");
    } else {
        container.classList.remove("video-off");
    }
}

// Update video status badge
function updateVideoStatusBadge(targetUserId, isVideoOn, isAudioOn) {
    const container = document.getElementById(`container-${targetUserId}`);
    if (!container) return;

    let statusBadge = container.querySelector(".video-status-badge");
    if (!statusBadge) {
        statusBadge = document.createElement("div");
        statusBadge.className = "video-status-badge";
        container.appendChild(statusBadge);
    }

    const videoIcon = isVideoOn
        ? '<i class="fas fa-video"></i>'
        : '<i class="fas fa-video-slash"></i>';
    const audioIcon = isAudioOn
        ? '<i class="fas fa-microphone"></i>'
        : '<i class="fas fa-microphone-slash"></i>';

    statusBadge.innerHTML = `${videoIcon} ${audioIcon}`;

    if (!isVideoOn) statusBadge.classList.add("video-off");
    else statusBadge.classList.remove("video-off");

    if (!isAudioOn) statusBadge.classList.add("audio-off");
    else statusBadge.classList.remove("audio-off");
}

// Initialize Socket.IO
function initSocket() {
    console.log("Initializing socket connection...");

    socket = io("http://127.0.0.1:3001", {
        transports: ["websocket", "polling"],
        reconnection: true,
        reconnectionAttempts: 5,
        reconnectionDelay: 1000,
    });

    socket.on("connect", () => {
        console.log("✅ Socket connected! Socket ID:", socket.id);
        updateStatus("Connected", true);

        socket.emit("join-meeting", {
            meetingCode: meetingCode,
            userId: userId,
            userName: userName,
        });
        console.log("📢 Emitted join-meeting event for room:", meetingCode);
    });

    socket.on("connect_error", (error) => {
        console.error("❌ Socket connection error:", error);
        updateStatus("Connection failed", false);
    });

    socket.on("user-joined", (data) => {
        console.log("👤 User joined event received:", data);
        addChatMessage("System", `${data.userName} joined the meeting`, false);

        if (data.userId != userId) {
            console.log("📞 Calling new user:", data.userId);
            callUser(data.userId);
        }
    });

    socket.on("user-left", (data) => {
        console.log("👋 User left event received:", data);
        addChatMessage("System", `${data.userName} left the meeting`, false);
        removePeer(data.userId);
    });

    socket.on("offer", async (data) => {
        console.log("📨 Received offer from:", data.from, "to:", data.to);
        if (data.to == userId) {
            await handleOffer(data);
        }
    });

    socket.on("answer", async (data) => {
        console.log("📨 Received answer from:", data.from, "to:", data.to);
        if (data.to == userId) {
            await handleAnswer(data);
        }
    });

    socket.on("ice-candidate", async (data) => {
        console.log("🧊 Received ICE candidate from:", data.from);
        if (data.to == userId) {
            await handleIceCandidate(data);
        }
    });

    socket.on("chat-message", (data) => {
        console.log("💬 Chat message:", data);
        addChatMessage(data.userName, data.message, false);
    });

    socket.on("existing-users", (users) => {
        console.log("📋 Existing users in room:", users);
        users.forEach((user) => {
            if (user.userId != userId) {
                console.log("📞 Calling existing user:", user.userId);
                setTimeout(() => callUser(user.userId), 1000);
            }
        });
    });

    socket.on("disconnect", () => {
        console.log("🔌 Socket disconnected");
        updateStatus("Disconnected", false);
    });
}

// Initialize Camera
async function initCamera() {
    try {
        console.log("🎥 Requesting camera/microphone access...");
        localStream = await navigator.mediaDevices.getUserMedia({
            video: true,
            audio: true,
        });
        const localVideo = document.getElementById("localVideo");
        if (localVideo) {
            localVideo.srcObject = localStream;
            console.log("✅ Camera initialized successfully");
        }
    } catch (error) {
        console.error("❌ Camera error:", error);
        alert("Could not access camera/microphone. Please check permissions.");
    }
}

// Call a user
async function callUser(targetUserId) {
    if (peerConnections[targetUserId]) {
        console.log("⚠️ Already connected to user:", targetUserId);
        return;
    }

    if (targetUserId == userId) {
        console.log("⚠️ Cannot call self");
        return;
    }

    console.log("📞 Creating connection to user:", targetUserId);

    const pc = new RTCPeerConnection({
        iceServers: [
            { urls: "stun:stun.l.google.com:19302" },
            { urls: "stun:stun1.l.google.com:19302" },
            { urls: "stun:stun2.l.google.com:19302" },
        ],
    });

    peerConnections[targetUserId] = pc;

    if (localStream) {
        localStream.getTracks().forEach((track) => {
            pc.addTrack(track, localStream);
            console.log("➕ Added track to peer connection:", track.kind);
        });
    }

    pc.onicecandidate = (event) => {
        if (event.candidate) {
            console.log("🧊 Sending ICE candidate to:", targetUserId);
            socket.emit("ice-candidate", {
                to: targetUserId,
                from: userId,
                candidate: event.candidate,
                meetingCode: meetingCode,
            });
        }
    };

    pc.ontrack = (event) => {
        console.log("📺 Received remote track from:", targetUserId);
        displayRemoteVideo(targetUserId, event.streams[0]);
    };

    try {
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        socket.emit("offer", {
            to: targetUserId,
            from: userId,
            offer: offer,
            meetingCode: meetingCode,
        });
        console.log("📤 Offer sent to:", targetUserId);
    } catch (error) {
        console.error("❌ Error creating offer:", error);
    }
}

// Display remote video
function displayRemoteVideo(targetUserId, stream) {
    let videoElement = document.getElementById(`video-${targetUserId}`);

    if (videoElement) {
        console.log("🔄 Updating existing video for:", targetUserId);
        videoElement.srcObject = stream;
        return;
    }

    console.log("🖥️ Creating new video element for:", targetUserId);

    const remoteGrid = document.getElementById("remoteVideos");
    if (!remoteGrid) return;

    const emptyState = remoteGrid.querySelector(".col-span-full");
    if (emptyState) emptyState.remove();

    const container = document.createElement("div");
    container.className = "video-container";
    container.id = `container-${targetUserId}`;

    videoElement = document.createElement("video");
    videoElement.id = `video-${targetUserId}`;
    videoElement.autoplay = true;
    videoElement.playsInline = true;
    videoElement.className = "w-full h-full object-cover";
    videoElement.srcObject = stream;

    const nameLabel = document.createElement("div");
    nameLabel.className = "participant-name";
    nameLabel.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i>Loading...`;

    container.appendChild(videoElement);
    container.appendChild(nameLabel);
    remoteGrid.appendChild(container);

    fetch(`/meetings/participant-name/${targetUserId}`)
        .then((res) => res.json())
        .then((data) => {
            const label = document.querySelector(
                `#container-${targetUserId} .participant-name`,
            );
            if (label)
                label.innerHTML = `<i class="fas fa-user mr-1"></i>${data.name}`;
            createAvatarElement(targetUserId, data.name, true);
            updateVideoStatusBadge(targetUserId, true, true);
        })
        .catch((error) => console.error("Error fetching name:", error));
}

// Handle offer
async function handleOffer(data) {
    console.log("🔄 Handling offer from:", data.from);

    let pc = peerConnections[data.from];
    if (!pc) {
        pc = new RTCPeerConnection({
            iceServers: [
                { urls: "stun:stun.l.google.com:19302" },
                { urls: "stun:stun1.l.google.com:19302" },
                { urls: "stun:stun2.l.google.com:19302" },
            ],
        });
        peerConnections[data.from] = pc;

        pc.onicecandidate = (event) => {
            if (event.candidate) {
                socket.emit("ice-candidate", {
                    to: data.from,
                    from: userId,
                    candidate: event.candidate,
                    meetingCode: meetingCode,
                });
            }
        };

        pc.ontrack = (event) => {
            console.log("📺 Received remote track from:", data.from);
            displayRemoteVideo(data.from, event.streams[0]);
        };

        if (localStream) {
            localStream.getTracks().forEach((track) => {
                pc.addTrack(track, localStream);
            });
        }
    }

    try {
        await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);

        socket.emit("answer", {
            to: data.from,
            from: userId,
            answer: answer,
            meetingCode: meetingCode,
        });
        console.log("📤 Answer sent to:", data.from);
    } catch (error) {
        console.error("❌ Error handling offer:", error);
    }
}

// Handle answer
async function handleAnswer(data) {
    const pc = peerConnections[data.from];
    if (pc) {
        try {
            await pc.setRemoteDescription(
                new RTCSessionDescription(data.answer),
            );
            console.log("✅ Answer set for:", data.from);
        } catch (error) {
            console.error("❌ Error handling answer:", error);
        }
    }
}

// Handle ICE candidate
async function handleIceCandidate(data) {
    const pc = peerConnections[data.from];
    if (pc && data.candidate) {
        try {
            await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
            console.log("✅ ICE candidate added for:", data.from);
        } catch (error) {
            console.error("❌ Error adding ICE candidate:", error);
        }
    }
}

// Remove peer
function removePeer(targetUserId) {
    if (peerConnections[targetUserId]) {
        peerConnections[targetUserId].close();
        delete peerConnections[targetUserId];
    }
    const container = document.getElementById(`container-${targetUserId}`);
    if (container) container.remove();

    const remoteGrid = document.getElementById("remoteVideos");
    if (remoteGrid && remoteGrid.children.length === 0) {
        remoteGrid.innerHTML = `
            <div class="col-span-full text-center text-gray-500 py-8">
                <i class="fas fa-user-plus text-4xl mb-2 block"></i>
                <p>Waiting for participants to join...</p>
            </div>
        `;
    }
}

// Toggle Audio
function toggleAudio() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            isAudioEnabled = !isAudioEnabled;
            audioTrack.enabled = isAudioEnabled;
            const btn = document.getElementById("toggleAudio");
            btn.innerHTML = isAudioEnabled
                ? '<i class="fas fa-microphone mr-2"></i>Mute'
                : '<i class="fas fa-microphone-slash mr-2"></i>Unmute';

            if (!isAudioEnabled) {
                btn.classList.add("active");
            } else {
                btn.classList.remove("active");
            }

            updateVideoStatusBadge("local", isVideoEnabled, isAudioEnabled);
            console.log("🎤 Audio:", isAudioEnabled ? "unmuted" : "muted");
        }
    }
}

// Toggle Video
function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            isVideoEnabled = !isVideoEnabled;
            videoTrack.enabled = isVideoEnabled;
            const btn = document.getElementById("toggleVideo");
            btn.innerHTML = isVideoEnabled
                ? '<i class="fas fa-video mr-2"></i>Stop Video'
                : '<i class="fas fa-video-slash mr-2"></i>Start Video';

            if (!isVideoEnabled) {
                btn.classList.add("active");
            } else {
                btn.classList.remove("active");
            }

            const localContainer = document.querySelector(
                "#localVideoContainer",
            );
            if (localContainer) {
                const userName =
                    document
                        .querySelector(".participant-name")
                        ?.innerText.replace("You (", "")
                        .replace(")", "") || "User";
                createAvatarElement("local", userName, isVideoEnabled);
            }

            console.log("📹 Video:", isVideoEnabled ? "on" : "off");
        }
    }
}

// Share Screen
async function shareScreen() {
    try {
        const screenStream = await navigator.mediaDevices.getDisplayMedia({
            video: true,
        });
        const videoTrack = screenStream.getVideoTracks()[0];

        Object.values(peerConnections).forEach((pc) => {
            const sender = pc
                .getSenders()
                .find((s) => s.track && s.track.kind === "video");
            if (sender) sender.replaceTrack(videoTrack);
        });

        const localVideo = document.getElementById("localVideo");
        if (localVideo) localVideo.srcObject = screenStream;

        videoTrack.onended = () => {
            if (localVideo && localStream) {
                localVideo.srcObject = localStream;
                Object.values(peerConnections).forEach((pc) => {
                    const sender = pc
                        .getSenders()
                        .find((s) => s.track && s.track.kind === "video");
                    if (sender && localStream) {
                        sender.replaceTrack(localStream.getVideoTracks()[0]);
                    }
                });
            }
        };
        console.log("🖥️ Screen sharing started");
    } catch (error) {
        console.error("Screen share error:", error);
    }
}

// Send Chat Message
function sendMessage() {
    const input = document.getElementById("chatInput");
    const message = input.value.trim();
    if (message && socket) {
        addChatMessage(userName, message, true);
        socket.emit("chat-message", {
            meetingCode: meetingCode,
            message: message,
            userName: userName,
        });
        input.value = "";
        console.log("💬 Message sent:", message);
    }
}

// Add Chat Message
function addChatMessage(sender, message, isOwn = false) {
    const container = document.getElementById("chatMessages");
    if (!container) return;

    const emptyState = container.querySelector(".text-center");
    if (emptyState) emptyState.remove();

    const messageDiv = document.createElement("div");
    messageDiv.className = `flex ${isOwn ? "justify-end" : "justify-start"} mb-2 chat-message`;
    messageDiv.innerHTML = `
        <div class="${isOwn ? "bg-indigo-600" : "bg-gray-700"} rounded-lg px-3 py-2 max-w-xs">
            ${!isOwn ? `<p class="text-xs text-indigo-300 font-semibold mb-1">${escapeHtml(sender)}</p>` : ""}
            <p class="text-white text-sm">${escapeHtml(message)}</p>
            <p class="text-xs text-gray-400 mt-1">${new Date().toLocaleTimeString()}</p>
        </div>
    `;
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

function updateStatus(status, isConnected) {
    const statusElement = document.getElementById("connectionStatus");
    if (statusElement) {
        if (isConnected) {
            statusElement.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${status}`;
            statusElement.className =
                "bg-green-600 text-white text-sm px-3 py-1 rounded-lg";
        } else {
            statusElement.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${status}`;
            statusElement.className =
                "bg-red-600 text-white text-sm px-3 py-1 rounded-lg";
        }
    }
}

function copyMeetingLink() {
    const link = `http://127.0.0.1:8000/join/${meetingCode}`;
    navigator.clipboard.writeText(link);
    showNotification("Meeting link copied!", "success");
}

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
        type === "success" ? "bg-green-500" : "bg-red-500"
    }`;
    notification.innerHTML = `<i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"} mr-2"></i>${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
async function init() {
    console.log("🚀 Initializing video meeting...");
    await initCamera();
    initSocket();
}

// Export functions for global access
window.initMeeting = (code, id, name) => {
    meetingCode = code;
    userId = id;
    userName = name;
    init();
};

window.copyMeetingLink = copyMeetingLink;
window.toggleAudio = toggleAudio;
window.toggleVideo = toggleVideo;
window.shareScreen = shareScreen;
window.sendMessage = sendMessage;
