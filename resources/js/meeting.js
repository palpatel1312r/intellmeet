class MeetingRoom {
    constructor(meetingCode, userId, userName) {
        this.meetingCode = meetingCode;
        this.userId = userId;
        this.userName = userName;

        this.peerConnections = new Map();
        this.localStream = null;

        this.init();
    }

    async init() {
        await this.initLocalStream();
        this.initializeEcho();
    }

    // 🎥 Get camera + mic
    async initLocalStream() {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true,
            });

            document.getElementById("localVideo").srcObject = this.localStream;
        } catch (err) {
            console.error("Media error:", err);
        }
    }

    // 📡 Laravel Echo (IMPORTANT: use join, not channel)
    initializeEcho() {
        window.Echo.join(`meeting.${this.meetingCode}`)
            .here((users) => {
                console.log("Users in room:", users);

                users.forEach((user) => {
                    if (user.id !== this.userId) {
                        this.callUser(user);
                    }
                });
            })
            .joining((user) => {
                console.log("User joined:", user);
                this.callUser(user);
            })
            .leaving((user) => {
                console.log("User left:", user);
                this.removePeer(user.id);
            })
            .listenForWhisper("offer", (data) => this.handleOffer(data))
            .listenForWhisper("answer", (data) => this.handleAnswer(data))
            .listenForWhisper("ice-candidate", (data) =>
                this.handleIceCandidate(data),
            );
    }

    // 📞 Call new user
    async callUser(user) {
        const pc = this.createPeerConnection(user.id);

        this.localStream.getTracks().forEach((track) => {
            pc.addTrack(track, this.localStream);
        });

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        window.Echo.join(`meeting.${this.meetingCode}`).whisper("offer", {
            to: user.id,
            from: this.userId,
            offer: offer,
        });
    }

    // 🎯 Create Peer Connection
    createPeerConnection(userId) {
        const pc = new RTCPeerConnection({
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
        });

        // ICE
        pc.onicecandidate = (event) => {
            if (event.candidate) {
                window.Echo.join(`meeting.${this.meetingCode}`).whisper(
                    "ice-candidate",
                    {
                        to: userId,
                        from: this.userId,
                        candidate: event.candidate,
                    },
                );
            }
        };

        // Remote video
        pc.ontrack = (event) => {
            let video = document.getElementById(`video-${userId}`);

            if (!video) {
                video = document.createElement("video");
                video.id = `video-${userId}`;
                video.autoplay = true;
                video.playsInline = true;
                video.className = "w-full rounded-lg";

                document.getElementById("participantsGrid").appendChild(video);
            }

            video.srcObject = event.streams[0];
        };

        this.peerConnections.set(userId, pc);
        return pc;
    }

    // 📥 Handle Offer
    async handleOffer(data) {
        if (data.to !== this.userId) return;

        const pc = this.createPeerConnection(data.from);

        this.localStream.getTracks().forEach((track) => {
            pc.addTrack(track, this.localStream);
        });

        await pc.setRemoteDescription(new RTCSessionDescription(data.offer));

        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);

        window.Echo.join(`meeting.${this.meetingCode}`).whisper("answer", {
            to: data.from,
            from: this.userId,
            answer: answer,
        });
    }

    // 📥 Handle Answer
    async handleAnswer(data) {
        if (data.to !== this.userId) return;

        const pc = this.peerConnections.get(data.from);
        if (!pc) return;

        await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
    }

    // 📥 Handle ICE
    async handleIceCandidate(data) {
        if (data.to !== this.userId) return;

        const pc = this.peerConnections.get(data.from);
        if (!pc) return;

        try {
            await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
        } catch (e) {
            console.error("ICE error:", e);
        }
    }

    // ❌ Remove peer
    removePeer(userId) {
        const pc = this.peerConnections.get(userId);
        if (pc) pc.close();

        this.peerConnections.delete(userId);

        const video = document.getElementById(`video-${userId}`);
        if (video) video.remove();
    }
}

// 🚀 Initialize
if (document.getElementById("meeting-room")) {
    const el = document.getElementById("meeting-room");

    new MeetingRoom(
        el.dataset.meetingCode,
        parseInt(el.dataset.userId),
        el.dataset.userName,
    );
}
