import { useEffect, useRef, useState } from "react";
import { usePage, Head } from "@inertiajs/react";
import Peer from "simple-peer";
import io from "socket.io-client";
import axios from "axios";

export default function MeetingRoom({ meeting }) {
    const [peers, setPeers] = useState([]);
    const [localStream, setLocalStream] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState("");
    const [isScreenSharing, setIsScreenSharing] = useState(false);

    const socketRef = useRef();
    const userVideoRef = useRef();
    const peersRef = useRef([]);

    useEffect(() => {
        // Connect to WebSocket
        socketRef.current = io(process.env.MIX_REVERB_HOST, {
            auth: {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            },
        });

        // Get user media
        navigator.mediaDevices
            .getUserMedia({ video: true, audio: true })
            .then((stream) => {
                setLocalStream(stream);
                if (userVideoRef.current) {
                    userVideoRef.current.srcObject = stream;
                }

                // Join meeting room
                socketRef.current.emit("join-meeting", {
                    meetingId: meeting.id,
                    userId: usePage().props.auth.user.id,
                });

                // Handle new peer
                socketRef.current.on("new-peer", (data) => {
                    const peer = createPeer(data.signal, data.callerId, stream);
                    peersRef.current.push({
                        peerId: data.callerId,
                        peer,
                    });
                    setPeers((prev) => [
                        ...prev,
                        { peerId: data.callerId, peer },
                    ]);
                });

                // Handle receive signal
                socketRef.current.on("receive-signal", (data) => {
                    const item = peersRef.current.find(
                        (p) => p.peerId === data.id,
                    );
                    if (item) {
                        item.peer.signal(data.signal);
                    }
                });

                // Handle peer disconnect
                socketRef.current.on("peer-disconnected", (data) => {
                    const peerObj = peersRef.current.find(
                        (p) => p.peerId === data.peerId,
                    );
                    if (peerObj) {
                        peerObj.peer.destroy();
                    }
                    const newPeers = peersRef.current.filter(
                        (p) => p.peerId !== data.peerId,
                    );
                    peersRef.current = newPeers;
                    setPeers(newPeers);
                });

                // Load chat history
                loadChatHistory();
            })
            .catch((err) => console.error("Failed to get media", err));

        return () => {
            if (localStream) {
                localStream.getTracks().forEach((track) => track.stop());
            }
            socketRef.current.disconnect();
        };
    }, []);

    const createPeer = (signal, callerId, stream) => {
        const peer = new Peer({
            initiator: false,
            trickle: false,
            stream,
        });

        peer.on("signal", (signal) => {
            socketRef.current.emit("return-signal", {
                signal,
                callerId,
                meetingId: meeting.id,
            });
        });

        peer.on("stream", (remoteStream) => {
            // Add remote stream to video element
            const videoElement = document.createElement("video");
            videoElement.srcObject = remoteStream;
            videoElement.autoplay = true;
            videoElement.playsInline = true;
            document.getElementById("remote-videos").appendChild(videoElement);
        });

        peer.signal(signal);

        return peer;
    };

    const startScreenShare = async () => {
        try {
            const screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: true,
            });
            setIsScreenSharing(true);

            // Replace video track for all peers
            peersRef.current.forEach(({ peer }) => {
                const sender = peer._pc
                    .getSenders()
                    .find((s) => s.track.kind === "video");
                if (sender) {
                    sender.replaceTrack(screenStream.getVideoTracks()[0]);
                }
            });

            screenStream.getVideoTracks()[0].onended = () => {
                stopScreenShare();
            };
        } catch (err) {
            console.error("Failed to share screen", err);
        }
    };

    const stopScreenShare = () => {
        if (localStream) {
            const videoTrack = localStream.getVideoTracks()[0];
            peersRef.current.forEach(({ peer }) => {
                const sender = peer._pc
                    .getSenders()
                    .find((s) => s.track.kind === "video");
                if (sender) {
                    sender.replaceTrack(videoTrack);
                }
            });
            setIsScreenSharing(false);
        }
    };

    const sendMessage = async () => {
        if (!newMessage.trim()) return;

        try {
            await axios.post(`/api/meetings/${meeting.id}/chat`, {
                message: newMessage,
            });
            setNewMessage("");
        } catch (error) {
            console.error("Failed to send message", error);
        }
    };

    const loadChatHistory = async () => {
        try {
            const response = await axios.get(
                `/api/meetings/${meeting.id}/messages`,
            );
            setMessages(response.data);
        } catch (error) {
            console.error("Failed to load messages", error);
        }
    };

    const endMeeting = async () => {
        if (confirm("Are you sure you want to end this meeting?")) {
            await axios.post(`/api/meetings/${meeting.id}/end`);
            window.location.href = "/meetings";
        }
    };

    return (
        <>
            <Head title={meeting.title} />
            <div className="h-screen flex flex-col">
                {/* Video Grid */}
                <div className="flex-1 bg-gray-900 p-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 h-full">
                        {/* Local Video */}
                        <div className="relative bg-black rounded-lg overflow-hidden">
                            <video
                                ref={userVideoRef}
                                autoPlay
                                muted
                                playsInline
                                className="w-full h-full object-cover"
                            />
                            <div className="absolute bottom-2 left-2 bg-black bg-opacity-50 px-2 py-1 rounded text-white text-sm">
                                You
                            </div>
                        </div>

                        {/* Remote Videos */}
                        <div
                            id="remote-videos"
                            className="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            {/* Remote videos will be added here dynamically */}
                        </div>
                    </div>
                </div>

                {/* Controls Bar */}
                <div className="bg-gray-800 p-4 flex justify-center space-x-4">
                    <button className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Mute
                    </button>
                    <button className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Stop Video
                    </button>
                    {!isScreenSharing ? (
                        <button
                            onClick={startScreenShare}
                            className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded"
                        >
                            Share Screen
                        </button>
                    ) : (
                        <button
                            onClick={stopScreenShare}
                            className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                        >
                            Stop Sharing
                        </button>
                    )}
                    <button
                        onClick={endMeeting}
                        className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                    >
                        End Meeting
                    </button>
                </div>

                {/* Chat Sidebar */}
                <div className="fixed right-0 top-0 h-full w-80 bg-white shadow-lg transform transition-transform">
                    <div className="flex flex-col h-full">
                        <div className="p-4 border-b">
                            <h3 className="text-lg font-semibold">
                                Meeting Chat
                            </h3>
                        </div>
                        <div className="flex-1 overflow-y-auto p-4 space-y-4">
                            {messages.map((msg) => (
                                <div
                                    key={msg.id}
                                    className="flex items-start space-x-2"
                                >
                                    <img
                                        src={
                                            msg.user.avatar_url ||
                                            `https://ui-avatars.com/api/?name=${msg.user.name}`
                                        }
                                        className="w-8 h-8 rounded-full"
                                        alt=""
                                    />
                                    <div>
                                        <div className="flex items-baseline space-x-2">
                                            <span className="font-medium text-sm">
                                                {msg.user.name}
                                            </span>
                                            <span className="text-xs text-gray-500">
                                                {new Date(
                                                    msg.created_at,
                                                ).toLocaleTimeString()}
                                            </span>
                                        </div>
                                        <p className="text-sm text-gray-700">
                                            {msg.message}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="p-4 border-t">
                            <div className="flex space-x-2">
                                <input
                                    type="text"
                                    value={newMessage}
                                    onChange={(e) =>
                                        setNewMessage(e.target.value)
                                    }
                                    onKeyPress={(e) =>
                                        e.key === "Enter" && sendMessage()
                                    }
                                    placeholder="Type a message..."
                                    className="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={sendMessage}
                                    className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                                >
                                    Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
