<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'video' ? 'Video' : 'Audio' }} Call - SYncly</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #0a0a0f; min-height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.07); z-index: 10; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .logo-img { height: 44px; object-fit: contain; }
        .call-info h2 { color: #fff; font-size: 18px; font-weight: 600; }
        .call-info p { color: #10b981; font-size: 12px; margin-top: 2px; }
        .call-timer { color: #9ca3af; font-size: 13px; font-variant-numeric: tabular-nums; }
        .video-stage { flex: 1; position: relative; background: #0d0d14; overflow: hidden; }
        #remoteVideo { width: 100%; height: 100%; background: #111118; display: flex; align-items: center; justify-content: center; }
        #remoteVideo > div { width: 100%; height: 100%; }
        .waiting-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #0d0d14; z-index: 5; transition: opacity 0.4s ease; }
        .waiting-overlay.hidden { opacity: 0; pointer-events: none; }
        .waiting-pulse { width: 100px; height: 100px; border-radius: 50%; background: rgba(16,185,129,0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; animation: pulseRing 2s ease-out infinite; }
        .waiting-pulse i { font-size: 44px; color: #10b981; }
        @keyframes pulseRing { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); } 70% { box-shadow: 0 0 0 30px rgba(16,185,129,0); } 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); } }
        .waiting-overlay h3 { color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 8px; }
        .waiting-overlay p { color: #6b7280; font-size: 14px; }
        .audio-avatar { display: none; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; gap: 16px; }
        .audio-avatar.show { display: flex; }
        .audio-avatar-circle { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; font-size: 52px; color: #fff; animation: pulseRing 2s ease-out infinite; }
        .audio-avatar p { color: #9ca3af; font-size: 16px; }
        .local-pip { position: absolute; bottom: 100px; right: 24px; width: 180px; height: 135px; border-radius: 12px; overflow: hidden; border: 2px solid rgba(255,255,255,0.15); background: #1a1a1a; z-index: 20; cursor: move; box-shadow: 0 8px 32px rgba(0,0,0,0.5); }
        .local-pip > div { width: 100% !important; height: 100% !important; transform: scaleX(-1); }
        .pip-no-video { width: 100%; height: 100%; display: none; align-items: center; justify-content: center; flex-direction: column; color: #6b7280; font-size: 12px; gap: 8px; }
        .pip-no-video i { font-size: 28px; }
        .pip-no-video.show { display: flex; }
        .controls-bar { display: flex; align-items: center; justify-content: center; gap: 16px; padding: 20px 24px; background: rgba(0,0,0,0.6); backdrop-filter: blur(12px); border-top: 1px solid rgba(255,255,255,0.05); }
        .ctrl-btn { width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; background: rgba(255,255,255,0.1); color: #fff; }
        .ctrl-btn i { font-size: 20px; }
        .ctrl-btn:hover { transform: scale(1.1); background: rgba(255,255,255,0.18); }
        .ctrl-btn.off { background: #ef4444; }
        .ctrl-btn.off:hover { background: #dc2626; }
        .ctrl-btn.end { background: #ef4444; width: 64px; height: 64px; }
        .ctrl-btn.end:hover { background: #dc2626; transform: scale(1.08); }
        .ctrl-btn.end i { font-size: 24px; }
        .ctrl-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }
        .ctrl-label { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .ctrl-label span { font-size: 10px; color: #6b7280; }
        .ended-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.88); z-index: 100; align-items: center; justify-content: center; flex-direction: column; gap: 16px; }
        .ended-overlay.show { display: flex; }
        .ended-overlay i { font-size: 56px; color: #ef4444; }
        .ended-overlay h2 { color: #fff; font-size: 24px; }
        .ended-overlay p { color: #9ca3af; font-size: 14px; }
        .redirect-bar { width: 200px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden; margin-top: 8px; }
        .redirect-fill { height: 100%; background: #ef4444; animation: fillBar 3s linear forwards; }
        @keyframes fillBar { from { width: 0%; } to { width: 100%; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="{{ route('privatecall') }}">
                <img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img">
            </a>
            <div class="call-info">
                <h2>
                    <i class="fas fa-{{ $type === 'video' ? 'video' : 'phone' }}"></i>
                    {{ $type === 'video' ? 'Video' : 'Audio' }} Call
                </h2>
                <p id="callStatus">Connecting...</p>
            </div>
        </div>
        <span class="call-timer" id="callTimer">00:00</span>
    </div>

    <div class="video-stage">
        <div class="waiting-overlay" id="waitingOverlay">
            <div class="waiting-pulse"><i class="fas fa-user-clock"></i></div>
            <h3>Waiting for the other person to join...</h3>
            <p>They'll connect shortly</p>
        </div>
        <div id="remoteVideo">
            <div class="audio-avatar" id="audioAvatar">
                <div class="audio-avatar-circle"><i class="fas fa-user"></i></div>
                <p>Audio call in progress</p>
            </div>
        </div>
        <div class="local-pip" id="localPip">
            <div id="localVideo"></div>
            <div class="pip-no-video" id="pipNoVideo">
                <i class="fas fa-video-slash"></i><span>Camera off</span>
            </div>
        </div>
    </div>

    <div class="controls-bar">
        <div class="ctrl-label">
            <button class="ctrl-btn" id="muteBtn" onclick="toggleMute()">
                <i class="fas fa-microphone" id="muteIcon"></i>
            </button>
            <span>Mute</span>
        </div>
        @if($type === 'video')
        <div class="ctrl-label">
            <button class="ctrl-btn" id="videoBtn" onclick="toggleVideo()">
                <i class="fas fa-video" id="videoIcon"></i>
            </button>
            <span>Camera</span>
        </div>
        @endif
        <div class="ctrl-label">
            <button class="ctrl-btn end" id="endBtn" onclick="endCall()">
                <i class="fas fa-phone-slash"></i>
            </button>
            <span style="color:#ef4444;">End</span>
        </div>
    </div>

    <div class="ended-overlay" id="endedOverlay">
        <i class="fas fa-phone-slash"></i>
        <h2>Call Ended</h2>
        <p id="endedMsg">The other person disconnected</p>
        <div class="redirect-bar"><div class="redirect-fill"></div></div>
    </div>

    <script>
        const AGORA_APP_ID   = "{{ env('AGORA_APP_ID') }}";
        const CHANNEL        = "{{ $channel }}";
        const CALL_TYPE      = "{{ $type }}";
        const UID            = {{ auth()->id() }};
        const CSRF           = "{{ csrf_token() }}";

        // Read recipient_id from URL — only present on initiator's side
        // Recipient's URL has no user_id, so this will be null for them
        const RECIPIENT_ID = new URLSearchParams(window.location.search).get('user_id');

        let agoraClient  = null;
        let localTracks  = { audio: null, video: null };
        let isAudioMuted = false;
        let isVideoOff   = false;
        let isCallActive = false;
        let remoteJoined = false;
        let timerSeconds = 0;
        let timerInterval= null;
        let endingCall   = false;

        function startTimer() {
            timerInterval = setInterval(() => {
                timerSeconds++;
                const m = String(Math.floor(timerSeconds / 60)).padStart(2,'0');
                const s = String(timerSeconds % 60).padStart(2,'0');
                document.getElementById('callTimer').textContent = m + ':' + s;
            }, 1000);
        }
        function stopTimer() { clearInterval(timerInterval); }

        function hideWaiting() {
            document.getElementById('waitingOverlay').classList.add('hidden');
            document.getElementById('callStatus').textContent = 'Connected';
            if (!remoteJoined) {
                remoteJoined = true;
                startTimer();
                // Update call logs to picked
                fetch('/call/pick', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ channel_name: CHANNEL }),
                }).catch(e => console.error('Pick error:', e));
            }
        }

        function cleanupTracks() {
            if (localTracks.video) { try { localTracks.video.stop(); localTracks.video.close(); } catch(e){} localTracks.video = null; }
            if (localTracks.audio) { try { localTracks.audio.stop(); localTracks.audio.close(); } catch(e){} localTracks.audio = null; }
        }

        async function endCall() {
    if (endingCall) return;
    endingCall = true;
    document.getElementById('endBtn').disabled = true;
    stopTimer();
    cleanupTracks();

    // Always fire call-cancelled if we have a recipient_id.
    // - If remote never joined: dismisses their incoming modal ✅
    // - If remote already joined: they'll also get user-left from Agora,
    //   but call-cancelled firing twice is harmless ✅
    // Update duration if call was picked
    if (remoteJoined) {
        try {
            await fetch('/call/duration', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    channel_name: CHANNEL,
                    duration: timerSeconds,
                }),
            });
        } catch(e) { console.error('Duration update error:', e); }
    }

    if (RECIPIENT_ID) {
        try {
            await fetch('/call/cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    recipient_id: RECIPIENT_ID,
                    channel_name: CHANNEL,
                }),
            });
        } catch(e) { console.error('Cancel notify error:', e); }
    }

    if (agoraClient && isCallActive) {
        try { await agoraClient.leave(); } catch(e) {}
        isCallActive = false;
    }

    window.location.href = "{{ route('privatecall') }}";
}


        function onRemoteLeft() {
            if (endingCall) return;
            endingCall = true;
            stopTimer();
            cleanupTracks();
            if (agoraClient && isCallActive) { agoraClient.leave().catch(() => {}); isCallActive = false; }
            document.getElementById('endedMsg').textContent = 'The other person ended the call';
            document.getElementById('endedOverlay').classList.add('show');
            setTimeout(() => { window.location.href = "{{ route('privatecall') }}"; }, 3000);
        }

        async function initCall() {
            try {
                agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

                agoraClient.on('user-published', async (user, mediaType) => {
                    await agoraClient.subscribe(user, mediaType);
                    hideWaiting();
                    if (mediaType === 'video') { user.videoTrack.play('remoteVideo'); document.getElementById('audioAvatar').classList.remove('show'); }
                    if (mediaType === 'audio') { user.audioTrack.play(); if (CALL_TYPE !== 'video') document.getElementById('audioAvatar').classList.add('show'); }
                });

                agoraClient.on('user-unpublished', (user, mediaType) => {
                    if (mediaType === 'video') document.getElementById('audioAvatar').classList.add('show');
                });

                agoraClient.on('user-left', () => { onRemoteLeft(); });

                const tokenRes  = await fetch("{{ route('call.token') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ channel_name: CHANNEL, uid: UID })
                });
                const tokenData = await tokenRes.json();
                if (tokenData.error) throw new Error(tokenData.error);

                await agoraClient.join(AGORA_APP_ID, CHANNEL, tokenData.token, UID);
                isCallActive = true;
                document.getElementById('callStatus').textContent = 'Waiting for other person...';

                if (CALL_TYPE === 'video') {
                    localTracks.audio = await AgoraRTC.createMicrophoneAudioTrack();
                    localTracks.video = await AgoraRTC.createCameraVideoTrack();
                    await agoraClient.publish([localTracks.audio, localTracks.video]);
                    localTracks.video.play('localVideo');
                } else {
                    localTracks.audio = await AgoraRTC.createMicrophoneAudioTrack();
                    await agoraClient.publish(localTracks.audio);
                    document.getElementById('localPip').style.display = 'none';
                }
            } catch(err) {
                console.error('Call init error:', err);
                document.getElementById('callStatus').textContent = 'Failed to connect';
            }
        }

        function toggleMute() {
            if (!localTracks.audio) return;
            isAudioMuted = !isAudioMuted;
            localTracks.audio.setEnabled(!isAudioMuted);
            document.getElementById('muteBtn').classList.toggle('off', isAudioMuted);
            document.getElementById('muteIcon').className = isAudioMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
        }

        function toggleVideo() {
            if (!localTracks.video) return;
            isVideoOff = !isVideoOff;
            localTracks.video.setEnabled(!isVideoOff);
            document.getElementById('videoBtn').classList.toggle('off', isVideoOff);
            document.getElementById('videoIcon').className      = isVideoOff ? 'fas fa-video-slash' : 'fas fa-video';
            document.getElementById('localVideo').style.display = isVideoOff ? 'none' : 'block';
            document.getElementById('pipNoVideo').classList.toggle('show', isVideoOff);
        }

        (function() {
            const pip = document.getElementById('localPip');
            let dragging = false, ox = 0, oy = 0;
            pip.addEventListener('mousedown', e => { dragging = true; ox = e.clientX - pip.offsetLeft; oy = e.clientY - pip.offsetTop; });
            document.addEventListener('mousemove', e => { if (!dragging) return; pip.style.left = (e.clientX - ox) + 'px'; pip.style.top = (e.clientY - oy) + 'px'; pip.style.right = 'auto'; pip.style.bottom = 'auto'; });
            document.addEventListener('mouseup', () => { dragging = false; });
        })();

        initCall();
    </script>
</body>
</html>