<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call - SYncly</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #000; min-height: 100vh; }
        .bg-pattern {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            z-index: -1;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; display: flex; gap: 24px; }
        .main-content { flex: 1; }
        .side-panel { width: 300px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 20px; }
        .call-logs { max-height: 600px; overflow-y: auto; }
        .log-date { color: #9ca3af; font-size: 12px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px; }
        .log-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .log-item:last-child { border-bottom: none; }
        .log-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .log-icon.outgoing { background: #10b981; color: #fff; }
        .log-icon.incoming { background: #3b82f6; color: #fff; }
        .log-icon.missed { background: #ef4444; color: #fff; }
        .log-details { flex: 1; }
        .log-name { color: #fff; font-size: 14px; font-weight: 500; }
        .log-status { color: #9ca3af; font-size: 12px; }
        .log-time { color: #6b7280; font-size: 10px; }
        .header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 40px; padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .logo-img { height: 60px; object-fit: contain; }
        .back-btn { color: #10b981; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .back-btn:hover { color: #34d399; }
        .users-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; }
        .main-cont{
            display:grid;
            grid-template-columns: 80% 20%;
            gap: 24px;
        }
        .user-card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 24px; text-align: center; transition: all 0.3s ease;
        }
        .user-card:hover { background: rgba(255,255,255,0.08); transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .user-card.inactive { opacity: 0.6; }
        .profile-wrapper { position: relative; width: 80px; height: 80px; margin: 0 auto 16px; }
        .profile-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.1); }
        .online-dot { position: absolute; bottom: 4px; right: 4px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #1a1a2e; }
        .online-dot.active   { background: #10b981; }
        .online-dot.inactive { background: #6b7280; }
        .user-name   { color: #fff;    font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .user-status { color: #9ca3af; font-size: 12px; margin-bottom: 16px; }
        .call-buttons { display: flex; justify-content: center; gap: 12px; }
        .call-btn { width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .call-btn.audio { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .call-btn.video { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .call-btn:hover { transform: scale(1.1); box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
        .call-btn:disabled { background: #4b5563; cursor: not-allowed; opacity: 0.5; }
        .call-btn:disabled:hover { transform: none; box-shadow: none; }
        .call-btn i { font-size: 18px; }

        /* Modal base */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.82); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        @keyframes modalSlide { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* Incoming */
        .incoming-content { background: rgba(20,20,30,0.97); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; padding: 40px; text-align: center; min-width: 320px; animation: modalSlide 0.3s ease; }
        .caller-profile { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; border: 4px solid #10b981; }
        .caller-name { color: #fff;    font-size: 24px; font-weight: 600; margin-bottom: 8px; }
        .caller-info { color: #9ca3af; font-size: 14px; margin-bottom: 30px; }
        .modal-buttons { display: flex; justify-content: center; gap: 20px; }
        .modal-btn { width: 64px; height: 64px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .modal-btn.accept { background: #10b981; color: #fff; }
        .modal-btn.reject { background: #ef4444; color: #fff; }
        .modal-btn:hover  { transform: scale(1.1); }
        .modal-btn i      { font-size: 24px; }
        .ring-animation   { animation: ring 1s infinite; }
        @keyframes ring { 0%,100% { transform: rotate(0); } 25% { transform: rotate(15deg); } 75% { transform: rotate(-15deg); } }

        /* Outgoing */
        .outgoing-content { background: rgba(12,12,22,0.98); border: 1px solid rgba(255,255,255,0.08); border-radius: 28px; padding: 48px 40px 36px; text-align: center; min-width: 320px; animation: modalSlide 0.3s ease; }
        .outgoing-avatar { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 22px; border: 4px solid #3b82f6; animation: outgoingPulse 1.6s ease-in-out infinite; }
        @keyframes outgoingPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); } 50% { box-shadow: 0 0 0 20px rgba(59,130,246,0); } }
        .outgoing-name   { color: #fff;    font-size: 22px; font-weight: 600; margin-bottom: 6px; }
        .outgoing-status { color: #9ca3af; font-size: 14px; margin-bottom: 14px; }
        .calling-dots { display: flex; justify-content: center; gap: 6px; margin-bottom: 20px; }
        .calling-dots span { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; animation: dotBounce 1.2s infinite ease-in-out; }
        .calling-dots span:nth-child(2) { animation-delay: 0.2s; }
        .calling-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotBounce { 0%,80%,100% { transform: scale(0.6); opacity: 0.4; } 40% { transform: scale(1.1); opacity: 1; } }
        .outgoing-type-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.25); border-radius: 20px; padding: 5px 16px; color: #93c5fd; font-size: 12px; margin-bottom: 30px; }
        .cancel-wrap { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .cancel-btn { width: 64px; height: 64px; border-radius: 50%; border: none; background: #ef4444; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
        .cancel-btn:hover    { background: #dc2626; transform: scale(1.08); }
        .cancel-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .cancel-btn i  { font-size: 24px; }
        .cancel-wrap p { color: #6b7280; font-size: 12px; }

        /* Toast */
        .toast { display: none; position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%); background: rgba(30,30,40,0.95); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 14px 24px; color: #fff; font-size: 14px; z-index: 2000; }
        .toast.show { display: block; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="container">
        <div class="main-content">
            <div class="header">
                <div class="header-left">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img">
                    </a>
                </div>
                <a href="{{ route('dashboard') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="main-cont">
            <div class="users-grid">
                @foreach ($users as $user)
                    <div class="user-card {{ $user->is_active ? '' : 'inactive' }}">
                        <div class="profile-wrapper">
                            <img src="{{ asset('user-avatar.jpg') }}" alt="{{ $user->name }}" class="profile-img">
                            <div class="online-dot {{ $user->is_active ? 'active' : 'inactive' }}"></div>
                        </div>
                        <h3 class="user-name">{{ $user->name }}</h3>
                        <p class="user-status">{{ $user->is_active ? 'Online' : 'Offline' }}</p>
                        <div class="call-buttons">
                            <button class="call-btn audio"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-call-type="audio"
                                    {{ !$user->is_active ? 'disabled' : '' }}>
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="call-btn video"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-call-type="video"
                                    {{ !$user->is_active ? 'disabled' : '' }}>
                                <i class="fas fa-video"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
             <div class="side-panel">
            <h3 style="color: #fff; font-size: 18px; margin-bottom: 16px;">Call Logs</h3>
            <div class="call-logs" id="callLogs">
                <!-- Logs will be loaded here -->
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- INCOMING CALL MODAL (recipient) -->
    <div class="modal-overlay" id="incomingCallModal">
        <div class="incoming-content">
            <img src="{{ asset('user-avatar.jpg') }}" alt="Caller" class="caller-profile ring-animation">
            <h2 class="caller-name" id="callerName">Unknown</h2>
            <p  class="caller-info" id="callTypeLabel">Incoming call...</p>
            <div class="modal-buttons">
                <button class="modal-btn reject" id="rejectCall"><i class="fas fa-phone-slash"></i></button>
                <button class="modal-btn accept" id="acceptCall"><i class="fas fa-phone"></i></button>
            </div>
        </div>
    </div>

    <!-- OUTGOING CALL MODAL (caller) -->
    <div class="modal-overlay" id="outgoingCallModal">
        <div class="outgoing-content">
            <img src="{{ asset('user-avatar.jpg') }}" alt="Recipient" class="outgoing-avatar">
            <h2 class="outgoing-name"   id="outgoingName">...</h2>
            <p  class="outgoing-status">Calling</p>
            <div class="calling-dots"><span></span><span></span><span></span></div>
            <div class="outgoing-type-badge">
                <i class="fas fa-phone" id="outgoingBadgeIcon"></i>
                <span id="outgoingBadgeText">Audio Call</span>
            </div>
            <div class="cancel-wrap">
                <button class="cancel-btn" id="cancelCallBtn"><i class="fas fa-phone-slash"></i></button>
                <p>Cancel</p>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        const CSRF           = "{{ csrf_token() }}";
        const PUSHER_KEY     = "{{ config('broadcasting.connections.pusher.key') }}";
        const PUSHER_CLUSTER = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
        const AUTH_USER_ID   = {{ auth()->id() }};

        // ── State ────────────────────────────────────────────────
        let currentChannel      = null;
        let currentCallType     = null;
        let currentRecipientId  = null;
        let outgoingActive      = false;   // tracks if caller hasn't cancelled yet

        // ── Toast ────────────────────────────────────────────────
        function showToast(msg, ms = 3500) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), ms);
        }

        // ────────────────────────────────────────────────────────
        // RINGBACK  — Web Audio API synthesised tone (no file, no
        // autoplay block).  Classic 440 Hz + 480 Hz  2s on / 4s off.
        // ────────────────────────────────────────────────────────
        let _rbStopped = true;
        let _rbTimeout = null;
        let _rbCtx     = null;
        let _rbOscs    = [];

        function _rbStartBurst() {
            // Must create a new AudioContext inside a user-gesture callback
            // (the click on the call button satisfies this requirement)
            try {
                _rbCtx  = new (window.AudioContext || window.webkitAudioContext)();
                const gain = _rbCtx.createGain();
                gain.gain.value = 0.2;
                gain.connect(_rbCtx.destination);

                _rbOscs = [440, 480].map(f => {
                    const o = _rbCtx.createOscillator();
                    o.type = 'sine';
                    o.frequency.value = f;
                    o.connect(gain);
                    o.start();
                    return o;
                });

                // Ring for 2 s
                _rbTimeout = setTimeout(() => {
                    _rbStopBurst();
                    if (!_rbStopped) {
                        // Silence 4 s then ring again
                        _rbTimeout = setTimeout(() => {
                            if (!_rbStopped) _rbStartBurst();
                        }, 4000);
                    }
                }, 2000);
            } catch(e) {
                console.warn('Web Audio ringback error:', e);
            }
        }

        function _rbStopBurst() {
            _rbOscs.forEach(o => { try { o.stop(); } catch(e){} });
            _rbOscs = [];
            if (_rbCtx) { try { _rbCtx.close(); } catch(e){} _rbCtx = null; }
        }

        function playRingback() {
            _rbStopped = false;
            clearTimeout(_rbTimeout);
            _rbStopBurst();
            _rbStartBurst();   // called inside click handler = user gesture ✅
        }

        function stopRingback() {
            _rbStopped = true;
            clearTimeout(_rbTimeout);
            _rbStopBurst();
        }

        // ────────────────────────────────────────────────────────
        // RINGTONE  — MP3 for recipient
        // ────────────────────────────────────────────────────────
        function playRingtone() {
            stopRingtone();
            const a = new Audio('{{ asset("sounds/ringtone.mp3") }}');
            a.loop = true;
            a.play().catch(() => {});
            window._ringtone = a;
        }
        function stopRingtone() {
            if (window._ringtone) {
                window._ringtone.pause();
                window._ringtone.currentTime = 0;
                window._ringtone = null;
            }
        }

        // ── Outgoing modal ───────────────────────────────────────
        function showOutgoingModal(userName, callType) {
            document.getElementById('outgoingName').textContent      = userName;
            document.getElementById('outgoingBadgeText').textContent = callType === 'video' ? 'Video Call' : 'Audio Call';
            document.getElementById('outgoingBadgeIcon').className   = callType === 'video' ? 'fas fa-video' : 'fas fa-phone';
            document.getElementById('cancelCallBtn').disabled        = false;
            document.getElementById('outgoingCallModal').classList.add('show');
            outgoingActive = true;
            playRingback();  // ✅ inside click handler, plays for BOTH audio & video
        }

        function hideOutgoingModal() {
            document.getElementById('outgoingCallModal').classList.remove('show');
            stopRingback();
            outgoingActive = false;
        }

        // ── Cancel call (caller presses cancel before pickup) ────
        document.getElementById('cancelCallBtn').addEventListener('click', async function () {
            console.log('🚫 Cancel button clicked');
            
            this.disabled = true;
            stopRingback();

            // Immediately hide modal — don't wait for server
            document.getElementById('outgoingCallModal').classList.remove('show');
            outgoingActive = false;

            // Notify server → server broadcasts 'call-cancelled' to recipient
            if (currentRecipientId && currentChannel) {
                try {
                    await fetch('/call/cancel', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            recipient_id: currentRecipientId,
                            channel_name: currentChannel,
                        }),
                    });
                } catch(e) { console.error('Cancel error:', e); }
            }

            currentChannel     = null;
            currentRecipientId = null;
            this.disabled      = false;
        });

        // ── Pusher ───────────────────────────────────────────────
        function initPusher() {
            if (typeof Pusher === 'undefined') { setTimeout(initPusher, 300); return; }

            const pusher = new Pusher(PUSHER_KEY, {
                cluster:       PUSHER_CLUSTER,
                forceTLS:      true,
                authEndpoint:  '/broadcasting/auth',
                authTransport: 'ajax',
                auth: { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } },
            });

            const ch = pusher.subscribe('private-user.' + AUTH_USER_ID);
            ch.bind('pusher:subscription_succeeded', () => console.log('✅ Pusher ready'));
            ch.bind('pusher:subscription_error',     e  => console.error('❌ Pusher error:', e));

            // Incoming call
            ch.bind('incoming-call', data => showIncomingCallModal(data));

            // Caller cancelled before pickup → dismiss incoming modal on recipient side
            ch.bind('call-cancelled', () => {
    console.log('🚫 call-cancelled received — hiding modal');
    stopRingtone();

    const modal = document.getElementById('incomingCallModal');
    // Remove class AND force display:none — belt and braces
    modal.classList.remove('show');
    modal.style.display = 'none';
    // Restore after short delay so it can be shown again if needed
    setTimeout(() => { modal.style.display = ''; }, 100);

    showToast('📵 The caller cancelled the call');
});

            window._pusher = pusher;
        }

        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', initPusher)
            : initPusher();

        // ── Incoming call modal ──────────────────────────────────
        function showIncomingCallModal(data) {
            document.getElementById('callerName').textContent    = data.caller?.name ?? 'Unknown';
            document.getElementById('callTypeLabel').textContent =
                data.callType === 'video' ? '📹 Incoming Video Call...' : '📞 Incoming Audio Call...';

            currentChannel     = data.channel_name;
            currentRecipientId = data.caller?.id ?? null;
            currentCallType    = data.callType || 'audio';

            document.getElementById('incomingCallModal').classList.add('show');
            playRingtone();   // ✅ plays for BOTH audio & video
        }

        document.getElementById('acceptCall').addEventListener('click', () => {
            stopRingtone();
            window.location.href = '/call/video?channel=' + currentChannel + '&type=' + currentCallType;
        });

        document.getElementById('rejectCall').addEventListener('click', async () => {
            stopRingtone();
            document.getElementById('incomingCallModal').classList.remove('show');

            // Notify server
            try {
                await fetch('/call/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ channel_name: currentChannel }),
                });
            } catch(e) { console.error('Reject error:', e); }
        });

        // ── Outgoing call buttons ────────────────────────────────
        document.querySelectorAll('.call-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const userId   = this.dataset.userId;
                const userName = this.dataset.userName;
                const callType = this.dataset.callType;
                if (!userId) return;

                const channelName = 'call_' + Math.random().toString(36).substr(2, 9);

                // Store state BEFORE showing modal
                currentChannel     = channelName;
                currentRecipientId = userId;
                currentCallType    = callType;

                // Show modal + ringback (inside click handler = no autoplay block)
                showOutgoingModal(userName, callType);

                try {
                    const res  = await fetch('/call', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ recipient_id: userId, channel_name: channelName, call_type: callType }),
                    });
                    const data = await res.json();

                    if (data.success) {
                        // Only navigate if caller hasn't already cancelled
                        if (outgoingActive) {
                            hideOutgoingModal();
                            window.location.href = '/call/video?channel=' + channelName
                                + '&type=' + callType + '&user_id=' + userId;
                        }
                    } else {
                        hideOutgoingModal();
                        showToast('❌ Could not reach ' + userName);
                    }
                } catch(e) {
                    console.error(e);
                    hideOutgoingModal();
                }
            });
        });

        // Load call logs
        async function loadCallLogs() {
            try {
                const res = await fetch('/call/logs');
                const logs = await res.json();
                const container = document.getElementById('callLogs');
                container.innerHTML = '';
                for (const [date, items] of Object.entries(logs)) {
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'log-date';
                    dateDiv.textContent = new Date(date).toLocaleDateString();
                    container.appendChild(dateDiv);
                    items.forEach(log => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'log-item';
                        let iconClass = 'log-icon ';
                        let statusText = '';
                        if (log.status === 'outgoing_picked') {
                            iconClass += 'outgoing';
                            statusText = 'Outgoing call';
                        } else if (log.status === 'incoming_picked') {
                            iconClass += 'incoming';
                            statusText = 'Incoming call';
                        } else if (log.status === 'outgoing_missed' || log.status === 'incoming_missed') {
                            iconClass += 'missed';
                            statusText = 'Missed call';
                        } else if (log.status === 'outgoing_cancelled' || log.status === 'incoming_cancelled') {
                            iconClass += 'missed';
                            statusText = 'Cancelled call';
                        }
                        let icon = log.call_type === 'video' ? '<i class="fas fa-video"></i>' : '<i class="fas fa-phone"></i>';
                        let duration = log.duration ? ` (${Math.floor(log.duration / 60)}:${(log.duration % 60).toString().padStart(2,'0')})` : '';
                        itemDiv.innerHTML = `
                            <div class="${iconClass}">${icon}</div>
                            <div class="log-details">
                                <div class="log-name">${log.other_user.name}</div>
                                <div class="log-status">${statusText}${duration}</div>
                                <div class="log-time">${new Date(log.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                            </div>
                        `;
                        container.appendChild(itemDiv);
                    });
                }
                if (Object.keys(logs).length === 0) {
                    container.innerHTML = '<p style="color: #9ca3af; text-align: center;">No call logs yet</p>';
                }
            } catch(e) {
                console.error('Load logs error:', e);
            }
        }

        loadCallLogs();
    </script>
</body>
</html>