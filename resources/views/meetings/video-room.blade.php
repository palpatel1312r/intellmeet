@extends('layouts.app')

@section('title', 'Video Meeting - ' . $meeting->title)

@section('content')
    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-white font-bold">{{ $meeting->title }}</h1>
                    <p class="text-indigo-200 text-sm">Code: {{ $meeting->meeting_code }}</p>
                </div>
                <div class="flex gap-2">
                    <span id="connectionStatus" class="text-white text-sm bg-black bg-opacity-50 px-3 py-1 rounded-lg">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Connecting...
                    </span>
                    <button onclick="copyMeetingLink()"
                        class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg text-sm transition">
                        <i class="fas fa-link mr-1"></i>Copy Link
                    </button>
                    <button onclick="showLeaveModal()"
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm transition">
                        <i class="fas fa-sign-out-alt mr-1"></i>Leave
                    </button>
                </div>
            </div>
        </div>

        <!-- Leave Confirmation Modal -->
        <div id="leaveModal" class="fixed inset-0 z-50 hidden overflow-y-auto" style="font-family: system-ui;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeLeaveModal()"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Leave Meeting?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to leave this meeting?</p>
                                    <div class="mt-4 p-3 bg-yellow-50 rounded-md border border-yellow-100">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="ml-2">
                                                <p class="text-xs text-yellow-800 font-semibold">If you leave:</p>
                                                <ul class="mt-1 text-xs text-yellow-700 list-disc list-inside">
                                                    <li>You will be disconnected from the meeting</li>
                                                    <li>Other participants will continue</li>
                                                    <li>You can rejoin later using the meeting link</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="confirmLeave()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                            Leave Meeting
                        </button>
                        <button type="button" onclick="closeLeaveModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Video Section -->
                <div class="lg:col-span-2">
                    <!-- Recording Indicator -->
                    <div id="recordingIndicator"
                        class="hidden mb-2 flex items-center justify-between bg-red-600 text-white px-3 py-2 rounded-lg">
                        <div class="flex items-center">
                            <div class="recording-pulse mr-2"></div>
                            <i class="fas fa-circle text-red-300 text-xs mr-2"></i>
                            <span class="text-sm font-medium">Recording in progress</span>
                            <span id="recordingTimer" class="ml-2 text-sm font-mono">00:00</span>
                        </div>
                        <button onclick="stopRecording()" class="text-white hover:text-gray-200 text-sm">
                            <i class="fas fa-stop mr-1"></i>Stop Recording
                        </button>
                    </div>

                    <!-- Local Video -->
                    <div class="video-container mb-4">
                        <video id="localVideo" autoplay muted playsinline></video>
                        <div class="participant-name">
                            <i class="fas fa-user mr-1"></i>You ({{ auth()->user()->name }})
                        </div>
                        <div id="recordingBadge"
                            class="hidden absolute top-2 right-2 bg-red-600 text-white px-2 py-1 rounded-lg text-xs z-20">
                            <i class="fas fa-circle text-red-300 text-xs mr-1"></i> REC
                        </div>
                    </div>

                    <!-- Remote Videos Grid -->
                    <div id="remoteVideos" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-full text-center text-gray-500 py-8">
                            <i class="fas fa-user-plus text-4xl mb-2 block"></i>
                            <p>Waiting for participants to join...</p>
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="bg-gray-800 rounded-lg p-4 mt-4 flex justify-center gap-3 flex-wrap">
                        <button id="toggleAudio"
                            class="control-btn bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-microphone mr-2"></i>Mute
                        </button>
                        <button id="toggleVideo"
                            class="control-btn bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-video mr-2"></i>Stop Video
                        </button>
                        <button id="shareScreen"
                            class="control-btn bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-desktop mr-2"></i>Share Screen
                        </button>
                        <button id="startRecording"
                            class="control-btn bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-circle mr-2"></i>Start Recording
                        </button>
                    </div>
                </div>

                <!-- Chat Section -->
                <div class="bg-gray-800 rounded-lg flex flex-col" style="height: 550px;">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-3 rounded-t-lg">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-comments mr-2"></i>Meeting Chat
                        </h3>
                    </div>
                    <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-2">
                        <div class="text-center text-gray-500 text-sm py-8">
                            <i class="fas fa-comment-dots text-3xl mb-2 block"></i>
                            <p>No messages yet</p>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-700">
                        <div class="flex gap-2">
                            <input type="text" id="chatInput" placeholder="Type a message..."
                                class="flex-1 bg-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button id="sendMessage"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recording Success Modal -->
    <div id="recordingCompleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex items-center">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900">Recording Saved!</h3>
                            <p class="text-sm text-gray-500">Your meeting recording has been saved.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                    <button onclick="closeRecordingModal()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.5.0/socket.io.min.js"></script>
    <script>
        // ==================== CONFIGURATION ====================
        const meetingCode = '{{ $meeting->meeting_code }}';
        const userId = {{ auth()->id() }};
        const userName = '{{ auth()->user()->name }}';
        const meetingId = {{ $meeting->id }};

        // WebRTC Variables
        let localStream = null;
        let peerConnections = {};
        let socket = null;
        let isAudioEnabled = true;
        let isVideoEnabled = true;

        // Recording Variables
        let mediaRecorder = null;
        let recordedChunks = [];
        let isRecording = false;
        let recordingStartTime = null;
        let recordingTimerInterval = null;

        // Session & Leave Variables
        let isLeaving = false;
        const SESSION_KEY = `meeting_session_${meetingId}`;
        let meetingSession = null;

        // ==================== LEAVE MODAL FUNCTIONS ====================

        function showLeaveModal() {
            document.getElementById('leaveModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLeaveModal() {
            document.getElementById('leaveModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function confirmLeave() {
            closeLeaveModal();
            showToast('info', 'Leaving meeting...');

            setTimeout(() => {
                leaveMeeting();
                window.location.href = '{{ route('meetings.show', $meeting) }}';
            }, 500);
        }

        function leaveMeeting() {
            isLeaving = true;
            clearMeetingSession();
            localStorage.removeItem('lastActiveMeeting');

            if (socket && socket.connected) {
                socket.emit('leave-meeting', {
                    meetingCode: meetingCode,
                    userId: userId
                });
                socket.disconnect();
            }

            if (localStream) {
                localStream.getTracks().forEach(track => {
                    track.stop();
                });
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLeaveModal();
            }
        });

        // ==================== SESSION PERSISTENCE ====================

        function saveMeetingSession() {
            if (isLeaving) return;

            meetingSession = {
                meetingId: meetingId,
                meetingCode: meetingCode,
                userId: userId,
                userName: userName,
                isActive: true,
                audioEnabled: isAudioEnabled,
                videoEnabled: isVideoEnabled,
                isRecording: isRecording,
                joinedAt: Date.now()
            };
            sessionStorage.setItem(SESSION_KEY, JSON.stringify(meetingSession));
            localStorage.setItem('lastActiveMeeting', JSON.stringify({
                meetingId: meetingId,
                meetingCode: meetingCode,
                timestamp: Date.now()
            }));
        }

        function clearMeetingSession() {
            sessionStorage.removeItem(SESSION_KEY);
            meetingSession = null;
        }

        // ==================== SOCKET.IO ====================

        function initSocket() {
            console.log('Initializing socket connection...');

            socket = io('http://127.0.0.1:3000', {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 5,
                reconnectionDelay: 1000
            });

            socket.on('connect', () => {
                console.log('✅ Socket connected! Socket ID:', socket.id);
                updateStatus('Connected', true);
                saveMeetingSession();

                socket.emit('join-meeting', {
                    meetingCode: meetingCode,
                    userId: userId,
                    userName: userName
                });
                console.log('📢 Emitted join-meeting event for room:', meetingCode);
            });

            socket.on('connect_error', (error) => {
                console.error('❌ Socket connection error:', error);
                updateStatus('Connection failed', false);
            });

            socket.on('user-joined', (data) => {
                console.log('👤 User joined event received:', data);
                addChatMessage('System', `${data.userName} joined the meeting`, false);

                if (data.userId != userId) {
                    console.log('📞 Calling new user:', data.userId);
                    callUser(data.userId);
                }
            });

            socket.on('user-left', (data) => {
                console.log('👋 User left event received:', data);
                addChatMessage('System', `${data.userName} left the meeting`, false);
                removePeer(data.userId);
            });

            socket.on('offer', async (data) => {
                console.log('📨 Received offer from:', data.from, 'to:', data.to);
                if (data.to == userId) {
                    await handleOffer(data);
                }
            });

            socket.on('answer', async (data) => {
                console.log('📨 Received answer from:', data.from, 'to:', data.to);
                if (data.to == userId) {
                    await handleAnswer(data);
                }
            });

            socket.on('ice-candidate', async (data) => {
                console.log('🧊 Received ICE candidate from:', data.from);
                if (data.to == userId) {
                    await handleIceCandidate(data);
                }
            });

            socket.on('chat-message', (data) => {
                console.log('💬 Chat message:', data);
                addChatMessage(data.userName, data.message, false);
            });

            socket.on('existing-users', (users) => {
                console.log('📋 Existing users in room:', users);
                users.forEach(user => {
                    if (user.userId != userId) {
                        console.log('📞 Calling existing user:', user.userId);
                        setTimeout(() => callUser(user.userId), 1000);
                    }
                });
            });

            socket.on('disconnect', () => {
                console.log('🔌 Socket disconnected');
                updateStatus('Disconnected', false);
            });
        }

        // ==================== CAMERA ====================

        async function initCamera() {
            try {
                console.log('🎥 Requesting camera/microphone access...');
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                const localVideo = document.getElementById('localVideo');
                if (localVideo) {
                    localVideo.srcObject = localStream;
                    console.log('✅ Camera initialized successfully');
                }
            } catch (error) {
                console.error('❌ Camera error:', error);
                alert('Could not access camera/microphone. Please check permissions.');
            }
        }

        // ==================== RECORDING FEATURES ====================

        async function startRecording() {
            if (isRecording) {
                console.log('Recording already in progress');
                return;
            }

            try {
                let streamToRecord = localStream;

                if (!streamToRecord) {
                    alert('No video stream available to record');
                    return;
                }

                mediaRecorder = new MediaRecorder(streamToRecord, {
                    mimeType: 'video/webm',
                    videoBitsPerSecond: 2500000
                });

                recordedChunks = [];

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    saveRecording();
                };

                mediaRecorder.start(1000);
                isRecording = true;
                recordingStartTime = Date.now();

                const startBtn = document.getElementById('startRecording');
                startBtn.innerHTML = '<i class="fas fa-stop mr-2"></i>Stop Recording';
                startBtn.classList.remove('bg-red-600');
                startBtn.classList.add('bg-gray-600');
                startBtn.onclick = stopRecording;

                document.getElementById('recordingIndicator').classList.remove('hidden');
                document.getElementById('recordingBadge').classList.remove('hidden');

                startRecordingTimer();
                addChatMessage('System', '🔴 Recording started by ' + userName, false);

                socket.emit('recording-started', {
                    meetingCode: meetingCode,
                    userName: userName
                });

                console.log('Recording started');
            } catch (error) {
                console.error('Error starting recording:', error);
                alert('Failed to start recording: ' + error.message);
            }
        }

        function stopRecording() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;

                if (recordingTimerInterval) {
                    clearInterval(recordingTimerInterval);
                    recordingTimerInterval = null;
                }

                const startBtn = document.getElementById('startRecording');
                startBtn.innerHTML = '<i class="fas fa-circle mr-2"></i>Start Recording';
                startBtn.classList.remove('bg-gray-600');
                startBtn.classList.add('bg-red-600');
                startBtn.onclick = startRecording;

                document.getElementById('recordingIndicator').classList.add('hidden');
                document.getElementById('recordingBadge').classList.add('hidden');

                addChatMessage('System', '⏹️ Recording stopped by ' + userName, false);

                socket.emit('recording-stopped', {
                    meetingCode: meetingCode,
                    userName: userName
                });

                console.log('Recording stopped');
            }
        }

        function startRecordingTimer() {
            const timerElement = document.getElementById('recordingTimer');
            recordingTimerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                timerElement.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }

        function saveRecording() {
            if (recordedChunks.length === 0) {
                console.log('No recording data to save');
                return;
            }

            const blob = new Blob(recordedChunks, {
                type: 'video/webm'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            a.href = url;
            a.download = `meeting-${meetingCode}-${timestamp}.webm`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            document.getElementById('recordingCompleteModal').classList.remove('hidden');
            recordedChunks = [];
        }

        function closeRecordingModal() {
            document.getElementById('recordingCompleteModal').classList.add('hidden');
        }

        // ==================== WEBRTC FUNCTIONS ====================

        async function callUser(targetUserId) {
            if (peerConnections[targetUserId] || targetUserId == userId) return;

            const pc = new RTCPeerConnection({
                iceServers: [{
                        urls: 'stun:stun.l.google.com:19302'
                    },
                    {
                        urls: 'stun:stun1.l.google.com:19302'
                    },
                    {
                        urls: 'stun:stun2.l.google.com:19302'
                    }
                ]
            });

            peerConnections[targetUserId] = pc;

            if (localStream) {
                localStream.getTracks().forEach(track => {
                    pc.addTrack(track, localStream);
                });
            }

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    socket.emit('ice-candidate', {
                        to: targetUserId,
                        from: userId,
                        candidate: event.candidate,
                        meetingCode: meetingCode
                    });
                }
            };

            pc.ontrack = (event) => {
                displayRemoteVideo(targetUserId, event.streams[0]);
            };

            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                socket.emit('offer', {
                    to: targetUserId,
                    from: userId,
                    offer: offer,
                    meetingCode: meetingCode
                });
            } catch (error) {
                console.error('Error creating offer:', error);
            }
        }

        function displayRemoteVideo(userId, stream) {
            let videoElement = document.getElementById(`video-${userId}`);
            if (videoElement) {
                videoElement.srcObject = stream;
                return;
            }

            const remoteGrid = document.getElementById('remoteVideos');
            if (!remoteGrid) return;

            const emptyState = remoteGrid.querySelector('.col-span-full');
            if (emptyState) emptyState.remove();

            const container = document.createElement('div');
            container.className = 'video-container';
            container.id = `container-${userId}`;

            videoElement = document.createElement('video');
            videoElement.id = `video-${userId}`;
            videoElement.autoplay = true;
            videoElement.playsInline = true;
            videoElement.className = 'w-full h-full object-cover';
            videoElement.srcObject = stream;

            const nameLabel = document.createElement('div');
            nameLabel.className = 'participant-name';
            nameLabel.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i>Loading...`;

            container.appendChild(videoElement);
            container.appendChild(nameLabel);
            remoteGrid.appendChild(container);

            fetch(`/meetings/participant-name/${userId}`)
                .then(res => res.json())
                .then(data => {
                    const label = document.querySelector(`#container-${userId} .participant-name`);
                    if (label) label.innerHTML = `<i class="fas fa-user mr-1"></i>${data.name}`;
                })
                .catch(error => console.error('Error fetching name:', error));
        }

        async function handleOffer(data) {
            let pc = peerConnections[data.from];
            if (!pc) {
                pc = new RTCPeerConnection({
                    iceServers: [{
                            urls: 'stun:stun.l.google.com:19302'
                        },
                        {
                            urls: 'stun:stun1.l.google.com:19302'
                        },
                        {
                            urls: 'stun:stun2.l.google.com:19302'
                        }
                    ]
                });
                peerConnections[data.from] = pc;

                pc.onicecandidate = (event) => {
                    if (event.candidate) {
                        socket.emit('ice-candidate', {
                            to: data.from,
                            from: userId,
                            candidate: event.candidate,
                            meetingCode: meetingCode
                        });
                    }
                };

                pc.ontrack = (event) => {
                    displayRemoteVideo(data.from, event.streams[0]);
                };

                if (localStream) {
                    localStream.getTracks().forEach(track => {
                        pc.addTrack(track, localStream);
                    });
                }
            }

            try {
                await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                socket.emit('answer', {
                    to: data.from,
                    from: userId,
                    answer: answer,
                    meetingCode: meetingCode
                });
            } catch (error) {
                console.error('Error handling offer:', error);
            }
        }

        async function handleAnswer(data) {
            const pc = peerConnections[data.from];
            if (pc) {
                try {
                    await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
                } catch (error) {
                    console.error('Error handling answer:', error);
                }
            }
        }

        async function handleIceCandidate(data) {
            const pc = peerConnections[data.from];
            if (pc && data.candidate) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                } catch (error) {
                    console.error('Error adding ICE candidate:', error);
                }
            }
        }

        function removePeer(userId) {
            if (peerConnections[userId]) {
                peerConnections[userId].close();
                delete peerConnections[userId];
            }
            const container = document.getElementById(`container-${userId}`);
            if (container) container.remove();

            const remoteGrid = document.getElementById('remoteVideos');
            if (remoteGrid && remoteGrid.children.length === 0) {
                remoteGrid.innerHTML = `
                    <div class="col-span-full text-center text-gray-500 py-8">
                        <i class="fas fa-user-plus text-4xl mb-2 block"></i>
                        <p>Waiting for participants to join...</p>
                    </div>
                `;
            }
        }

        // ==================== CONTROLS ====================

        function toggleAudio() {
            if (localStream) {
                const audioTrack = localStream.getAudioTracks()[0];
                if (audioTrack) {
                    isAudioEnabled = !isAudioEnabled;
                    audioTrack.enabled = isAudioEnabled;
                    const btn = document.getElementById('toggleAudio');
                    btn.innerHTML = isAudioEnabled ? '<i class="fas fa-microphone mr-2"></i>Mute' :
                        '<i class="fas fa-microphone-slash mr-2"></i>Unmute';
                }
            }
        }

        function toggleVideo() {
            if (localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    isVideoEnabled = !isVideoEnabled;
                    videoTrack.enabled = isVideoEnabled;
                    const btn = document.getElementById('toggleVideo');
                    btn.innerHTML = isVideoEnabled ? '<i class="fas fa-video mr-2"></i>Stop Video' :
                        '<i class="fas fa-video-slash mr-2"></i>Start Video';
                }
            }
        }

        async function shareScreen() {
            try {
                const screenStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true
                });
                const videoTrack = screenStream.getVideoTracks()[0];

                Object.values(peerConnections).forEach(pc => {
                    const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                    if (sender) sender.replaceTrack(videoTrack);
                });

                const localVideo = document.getElementById('localVideo');
                if (localVideo) {
                    localVideo.srcObject = screenStream;
                    localVideo.setAttribute('data-screen', 'true');
                }

                videoTrack.onended = () => {
                    if (localVideo && localStream) {
                        localVideo.srcObject = localStream;
                        localVideo.removeAttribute('data-screen');
                        Object.values(peerConnections).forEach(pc => {
                            const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                            if (sender && localStream) {
                                sender.replaceTrack(localStream.getVideoTracks()[0]);
                            }
                        });
                    }
                };
            } catch (error) {
                console.error('Screen share error:', error);
            }
        }

        // ==================== CHAT ====================

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (message && socket) {
                addChatMessage(userName, message, true);
                socket.emit('chat-message', {
                    meetingCode: meetingCode,
                    message: message,
                    userName: userName
                });
                input.value = '';
            }
        }

        function addChatMessage(sender, message, isOwn = false) {
            const container = document.getElementById('chatMessages');
            if (!container) return;

            const emptyState = container.querySelector('.text-center');
            if (emptyState) emptyState.remove();

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isOwn ? 'justify-end' : 'justify-start'} mb-2 chat-message`;
            messageDiv.innerHTML = `
                <div class="${isOwn ? 'bg-indigo-600' : 'bg-gray-700'} rounded-lg px-3 py-2 max-w-xs">
                    ${!isOwn ? `<p class="text-xs text-indigo-300 font-semibold mb-1">${escapeHtml(sender)}</p>` : ''}
                    <p class="text-white text-sm">${escapeHtml(message)}</p>
                    <p class="text-xs text-gray-400 mt-1">${new Date().toLocaleTimeString()}</p>
                </div>
            `;
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        // ==================== UTILITIES ====================

        function updateStatus(status, isConnected) {
            const statusElement = document.getElementById('connectionStatus');
            if (statusElement) {
                if (isConnected) {
                    statusElement.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${status}`;
                    statusElement.className = 'bg-green-600 text-white text-sm px-3 py-1 rounded-lg';
                } else {
                    statusElement.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${status}`;
                    statusElement.className = 'bg-red-600 text-white text-sm px-3 py-1 rounded-lg';
                }
            }
        }

        function copyMeetingLink() {
            const link = `http://127.0.0.1:8000/join/${meetingCode}`;
            navigator.clipboard.writeText(link).then(() => {
                showToast('success', 'Meeting link copied!');
            }).catch(() => {
                showToast('error', 'Failed to copy link');
            });
        }

        function showToast(type, message) {
            const existingToast = document.getElementById('customToast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.id = 'customToast';
            toast.className = `fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ==================== INITIALIZATION ====================

        async function init() {
            console.log('🚀 Initializing video meeting...');
            await initCamera();
            initSocket();
        }

        init();

        // Event Listeners
        document.getElementById('toggleAudio').addEventListener('click', toggleAudio);
        document.getElementById('toggleVideo').addEventListener('click', toggleVideo);
        document.getElementById('shareScreen').addEventListener('click', shareScreen);
        document.getElementById('sendMessage').addEventListener('click', sendMessage);
        document.getElementById('startRecording').addEventListener('click', startRecording);
        document.getElementById('chatInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Expose global functions
        window.copyMeetingLink = copyMeetingLink;
        window.stopRecording = stopRecording;
        window.closeRecordingModal = closeRecordingModal;
        window.showLeaveModal = showLeaveModal;
        window.closeLeaveModal = closeLeaveModal;
        window.confirmLeave = confirmLeave;
    </script>

    <style>
        .video-container {
            position: relative;
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 16/9;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .participant-name {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            z-index: 10;
        }

        .control-btn {
            transition: all 0.2s;
        }

        .control-btn:active {
            transform: scale(0.95);
        }

        .chat-message {
            animation: fadeIn 0.3s ease;
        }

        .recording-pulse {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ff3b30;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }
    </style>
@endsection
