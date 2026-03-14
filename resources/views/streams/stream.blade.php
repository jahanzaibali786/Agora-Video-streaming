<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Streaming - SYncly</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            background: #0a0a0f;
        }

        /* ── Page shell: header → content → controls ── */
        .page-wrap {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .header {
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 20px;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .header-left { display:flex; align-items:center; gap:12px; }
        .logo-img    { height:38px; object-fit:contain; }
        .stream-info h2 { color:#fff; font-size:16px; font-weight:600; }
        .stream-info p  { color:#ef4444; font-size:11px; margin-top:2px; display:flex; align-items:center; gap:5px; }
        .live-dot { width:7px; height:7px; border-radius:50%; background:#ef4444; animation:blink 1.2s infinite; }
        @keyframes blink{0%,100%{opacity:1;}50%{opacity:0.3;}}
        .header-right { display:flex; align-items:center; gap:14px; }
        .viewer-pill { display:flex; align-items:center; gap:6px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); border-radius:20px; padding:4px 12px; }
        .viewer-pill i { color:#ef4444; font-size:11px; }
        .viewer-pill span { color:#fca5a5; font-size:12px; font-weight:600; }
        .stream-timer { color:#9ca3af; font-size:13px; font-variant-numeric:tabular-nums; }

        /* ── Content row: video | sidebar ── */
        /* This sits BETWEEN header and controls bar, takes remaining height */
        .content-row {
            flex: 1;
            min-height: 0;          /* critical — lets flex child shrink */
            display: flex;
            overflow: hidden;
        }

        /* ── Video column ── */
        .video-col {
            flex: 1;
            min-width: 0;           /* critical — stops video expanding past sidebar */
            position: relative;
            background: #000;
            overflow: hidden;       /* clips Agora injection */
        }

        /* Agora injects a div here — lock it inside video-col */
        #local-player {
            position: absolute;
            inset: 0;
            background: #111;
        }
        /* Force every child Agora creates to stay inside */
        #local-player * {
            max-width: 100% !important;
            max-height: 100% !important;
        }
        #local-player video {
            width: 100%  !important;
            height: 100% !important;
            object-fit: contain !important;
            transform: scaleX(-1);
        }

        /* Join toasts — inside video-col, floated bottom-left */
        .join-toasts {
            position: absolute;
            bottom: 16px; left: 14px;
            display: flex; flex-direction: column-reverse; gap: 8px;
            z-index: 20;
            pointer-events: none;
            max-width: 240px;
        }
        .join-toast {
            display: flex; align-items: center; gap: 8px;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 30px;
            padding: 6px 14px 6px 6px;
            animation: toastUp 0.3s ease forwards; opacity: 0;
        }
        @keyframes toastUp { from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);} }
        .join-toast.fade-out { animation: toastFade 0.4s ease forwards; }
        @keyframes toastFade { to{opacity:0;transform:translateY(-8px);} }
        .toast-av { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; color:#fff; flex-shrink:0; }
        .join-toast span { color:#fff; font-size:11px; font-weight:500; }

        /* ── Sidebar column — completely independent of video-col ── */
        /* No position:absolute anywhere — pure flex column */
        .sidebar-col {
            width: 260px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            background: #0d0d18;
            border-left: 1px solid rgba(255,255,255,0.07);
            overflow: hidden;       /* sidebar clips its own content */
        }

        /* Tabs */
        .s-tabs {
            flex-shrink: 0;
            display: flex;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .s-tab {
            flex: 1; padding: 11px 0;
            text-align: center; font-size:12px; font-weight:600;
            color: #6b7280; cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .s-tab.active { color:#10b981; border-bottom-color:#10b981; }
        .s-tab i { margin-right:4px; }

        /* Panels */
        .s-panel { display:none; flex:1; flex-direction:column; overflow:hidden; min-height:0; }
        .s-panel.active { display:flex; }

        /* Viewers panel */
        .viewers-scroll {
            flex: 1; overflow-y: auto; padding: 8px 10px;
        }
        .viewers-scroll::-webkit-scrollbar{width:3px;}
        .viewers-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.08);border-radius:3px;}
        .viewer-row { display:flex; align-items:center; gap:9px; padding:7px 8px; border-radius:9px; transition:background 0.2s; animation:slideIn 0.2s ease; }
        @keyframes slideIn{from{opacity:0;transform:translateX(6px);}to{opacity:1;transform:translateX(0);}}
        .viewer-row:hover { background:rgba(255,255,255,0.04); }
        .v-av { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0; }
        .v-name { color:#e5e7eb; font-size:13px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .v-time { color:#6b7280; font-size:10px; }
        .v-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap:10px; color:#4b5563; padding:20px; text-align:center; }
        .v-empty i { font-size:28px; }
        .v-empty p { font-size:12px; line-height:1.5; }

        /* Chat panel */
        .chat-msgs {
            flex: 1; min-height: 0;
            overflow-y: auto;
            padding: 10px 12px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .chat-msgs::-webkit-scrollbar{width:3px;}
        .chat-msgs::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.08);border-radius:3px;}
        .c-msg { display:flex; gap:8px; animation:msgIn 0.2s ease; }
        @keyframes msgIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
        .c-av { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; color:#fff; flex-shrink:0; }
        .c-name { font-size:11px; font-weight:600; margin-bottom:2px; }
        .c-text { color:#d1d5db; font-size:13px; line-height:1.4; word-break:break-word; }
        .c-time { color:#4b5563; font-size:10px; margin-top:2px; }

        .chat-input-row {
            flex-shrink: 0;
            padding: 10px 12px;
            border-top: 1px solid rgba(255,255,255,0.07);
            display: flex; gap: 8px;
        }
        .chat-inp {
            flex: 1;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 8px 14px;
            color: #fff; font-size:13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        .chat-inp::placeholder { color:#6b7280; }
        .chat-inp:focus { border-color: rgba(16,185,129,0.4); }
        .chat-send-btn {
            width:36px; height:36px; border-radius:50%;
            border:none; background:#10b981; color:#fff;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            flex-shrink:0; transition:all 0.2s;
        }
        .chat-send-btn:hover { background:#059669; transform:scale(1.08); }
        .chat-send-btn i { font-size:14px; }

        /* ── Controls bar ── */
        .controls-bar {
            flex-shrink: 0;
            display: flex; align-items:center; justify-content:center; gap:14px;
            padding: 16px 24px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .ctrl-btn { width:52px; height:52px; border-radius:50%; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; background:rgba(255,255,255,0.1); color:#fff; }
        .ctrl-btn i { font-size:18px; }
        .ctrl-btn:hover { transform:scale(1.1); background:rgba(255,255,255,0.18); }
        .ctrl-btn.off { background:#ef4444; }
        .ctrl-btn.end { background:#ef4444; width:58px; height:58px; }
        .ctrl-btn.end:hover { background:#dc2626; transform:scale(1.08); }
        .ctrl-btn.end i { font-size:20px; }
        .ctrl-btn:disabled { opacity:0.5; cursor:not-allowed; transform:none!important; }
        .ctrl-label { display:flex; flex-direction:column; align-items:center; gap:5px; }
        .ctrl-label span { font-size:10px; color:#6b7280; }

        /* ── Ended overlay ── */
        .ended-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:999; align-items:center; justify-content:center; flex-direction:column; gap:16px; }
        .ended-overlay.show { display:flex; }
        .ended-overlay i  { font-size:56px; color:#ef4444; }
        .ended-overlay h2 { color:#fff; font-size:24px; }
        .ended-overlay p  { color:#9ca3af; font-size:14px; }
    </style>
</head>
<body>
<div class="page-wrap">

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="{{ route('stream.list') }}"><img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img"></a>
            <div class="stream-info">
                <h2><i class="fas fa-video"></i> Live Streaming</h2>
                <p><span class="live-dot"></span><span id="streamStatus">Starting...</span></p>
            </div>
        </div>
        <div class="header-right">
            <div class="viewer-pill"><i class="fas fa-eye"></i><span id="viewerPillCount">0</span></div>
            <span class="stream-timer" id="streamTimer">00:00</span>
        </div>
    </div>

    <!-- Content: video + sidebar -->
    <div class="content-row">

        <!-- Video -->
        <div class="video-col">
            <div id="local-player"></div>
            <div class="join-toasts" id="joinToasts"></div>
        </div>

        <!-- Sidebar — lives OUTSIDE video-col, never affected by Agora -->
        <div class="sidebar-col">
            <div class="s-tabs">
                <div class="s-tab active" id="tab-viewers" onclick="switchTab('viewers')">
                    <i class="fas fa-users"></i> Viewers
                </div>
                <div class="s-tab" id="tab-chat" onclick="switchTab('chat')">
                    <i class="fas fa-comments"></i> Chat
                </div>
            </div>

            <!-- Viewers panel -->
            <div class="s-panel active" id="panel-viewers">
                <div class="viewers-scroll" id="viewersList">
                    <div class="v-empty" id="viewersEmpty">
                        <i class="fas fa-eye-slash"></i>
                        <p>No viewers yet.<br>Share your link!</p>
                    </div>
                </div>
            </div>

            <!-- Chat panel -->
            <div class="s-panel" id="panel-chat">
                <div class="chat-msgs" id="chatMsgs"></div>
                <div class="chat-input-row">
                    <input class="chat-inp" id="chatInput" type="text" placeholder="Send a message..." maxlength="200">
                    <button class="chat-send-btn" onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>

    </div>

    <!-- Controls -->
    <div class="controls-bar">
        <div class="ctrl-label">
            <button class="ctrl-btn" id="muteBtn" onclick="toggleMute()"><i class="fas fa-microphone" id="muteIcon"></i></button>
            <span>Mute</span>
        </div>
        <div class="ctrl-label">
            <button class="ctrl-btn" id="videoBtn" onclick="toggleVideo()"><i class="fas fa-video" id="videoIcon"></i></button>
            <span>Camera</span>
        </div>
        <div class="ctrl-label">
            <button class="ctrl-btn end" id="endBtn" onclick="endStream()"><i class="fas fa-stop"></i></button>
            <span style="color:#ef4444;">Stop</span>
        </div>
    </div>

</div>

<div class="ended-overlay" id="endedOverlay">
    <i class="fas fa-stop"></i>
    <h2>Stream Ended</h2>
    <p>Thank you for streaming!</p>
</div>

<script>
    const APP_ID         = "{{ config('services.agora.app_id') }}";
    const CSRF           = "{{ csrf_token() }}";
    const PUSHER_KEY     = "{{ config('broadcasting.connections.pusher.key') }}";
    const PUSHER_CLUSTER = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
    const MY_NAME        = "{{ auth()->user()->name }}";
    const MY_ID          = {{ auth()->id() }};

    let agoraClient = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
    agoraClient.setClientRole('host');

    let localTracks  = { audio: null, video: null };
    let isAudioMuted = false, isVideoOff = false, isStreaming = false;
    let timerSecs    = 0, timerInterval = null;
    let viewers      = {};
    let STREAM_KEY   = null;

    // ── Helpers ──────────────────────────────────────────────────
    function initials(name){ return (name||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2); }
    function randColor(name){
        const c=['#10b981','#6366f1','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
        let h=0; for(let ch of (name||'')) h=ch.charCodeAt(0)+((h<<5)-h);
        return c[Math.abs(h)%c.length];
    }

    // ── Tabs ─────────────────────────────────────────────────────
    function switchTab(tab){
        document.querySelectorAll('.s-tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.s-panel').forEach(p=>p.classList.remove('active'));
        document.getElementById('tab-'+tab).classList.add('active');
        document.getElementById('panel-'+tab).classList.add('active');
    }

    // ── Join toast ───────────────────────────────────────────────
    function showJoinToast(name){
        const wrap = document.getElementById('joinToasts');
        const el   = document.createElement('div');
        el.className = 'join-toast';
        const color  = randColor(name);
        el.innerHTML = `<div class="toast-av" style="background:linear-gradient(135deg,${color},${color}88)">${initials(name)}</div>
            <span><b>${name}</b> joined</span>`;
        wrap.prepend(el);
        setTimeout(()=>{ el.classList.add('fade-out'); setTimeout(()=>el.remove(),400); }, 4000);
        while(wrap.children.length > 5) wrap.lastChild.remove();
    }

    // ── Viewers ──────────────────────────────────────────────────
    function renderViewers(){
        const list  = document.getElementById('viewersList');
        const empty = document.getElementById('viewersEmpty');
        const count = Object.keys(viewers).length;
        document.getElementById('viewerPillCount').textContent = count;
        list.querySelectorAll('.viewer-row').forEach(el=>el.remove());
        if(count===0){ empty.style.display='flex'; return; }
        empty.style.display='none';
        Object.values(viewers).forEach(v=>{
            const color = randColor(v.name);
            const el    = document.createElement('div');
            el.className = 'viewer-row';
            el.innerHTML = `<div class="v-av" style="background:linear-gradient(135deg,${color},${color}88)">${initials(v.name)}</div>
                <div><div class="v-name">${v.name}</div>
                <div class="v-time">${v.joinedAt.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}</div></div>`;
            list.appendChild(el);
        });
    }
    function addViewer(uid,name){ if(viewers[uid]) return; viewers[uid]={uid,name:name||'Viewer #'+uid,joinedAt:new Date()}; renderViewers(); }
    function removeViewer(uid){ delete viewers[uid]; renderViewers(); }

    // ── Chat ─────────────────────────────────────────────────────
    function appendChat(name, text){
        const color = randColor(name);
        const time  = new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
        const msgs  = document.getElementById('chatMsgs');
        const el    = document.createElement('div');
        el.className = 'c-msg';
        el.innerHTML = `<div class="c-av" style="background:linear-gradient(135deg,${color},${color}88)">${initials(name)}</div>
            <div><div class="c-name" style="color:${color}">${name}</div>
            <div class="c-text">${text}</div>
            <div class="c-time">${time}</div></div>`;
        msgs.appendChild(el);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function sendChat(){
        const inp  = document.getElementById('chatInput');
        const text = inp.value.trim();
        if(!text) return;
        inp.value = '';
        fetch('/stream/chat',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ stream_key: STREAM_KEY, message: text })
        }).catch(console.error);
    }
    document.getElementById('chatInput').addEventListener('keydown', e=>{ if(e.key==='Enter') sendChat(); });

    // ── Timer ────────────────────────────────────────────────────
    function startTimer(){
        timerInterval=setInterval(()=>{
            timerSecs++;
            document.getElementById('streamTimer').textContent=
                String(Math.floor(timerSecs/60)).padStart(2,'0')+':'+String(timerSecs%60).padStart(2,'0');
        },1000);
    }

    // ── Agora ────────────────────────────────────────────────────
    async function startStreaming(appId, channelName, token, uid){
        try{
            STREAM_KEY = channelName;
            await agoraClient.join(appId, channelName, token, uid);
            localTracks.audio = await AgoraRTC.createMicrophoneAudioTrack();
            localTracks.video = await AgoraRTC.createCameraVideoTrack();
            localTracks.video.play('local-player');
            await agoraClient.publish([localTracks.audio, localTracks.video]);
            isStreaming = true;
            document.getElementById('streamStatus').textContent = 'Live';
            startTimer();
        }catch(err){
            console.error('Start stream error:',err);
            document.getElementById('streamStatus').textContent = 'Failed to start';
        }
    }

    function cleanupTracks(){
        if(localTracks.video){try{localTracks.video.stop();localTracks.video.close();}catch(e){} localTracks.video=null;}
        if(localTracks.audio){try{localTracks.audio.stop();localTracks.audio.close();}catch(e){} localTracks.audio=null;}
    }

    async function endStream(){
        if(!isStreaming) return;
        isStreaming=false;
        document.getElementById('endBtn').disabled=true;
        clearInterval(timerInterval);
        cleanupTracks();
        try{ await agoraClient.leave(); }catch(e){}
        try{
            const r=await fetch("{{ route('stream.stop') }}",{method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
            if(r.ok){ document.getElementById('endedOverlay').classList.add('show'); setTimeout(()=>{ window.location.href="{{ route('stream.list') }}"; },2000); }
        }catch(e){ console.error(e); }
    }

    function toggleMute(){
        if(!localTracks.audio) return;
        isAudioMuted=!isAudioMuted;
        localTracks.audio.setEnabled(!isAudioMuted);
        document.getElementById('muteBtn').classList.toggle('off',isAudioMuted);
        document.getElementById('muteIcon').className=isAudioMuted?'fas fa-microphone-slash':'fas fa-microphone';
    }
    function toggleVideo(){
        if(!localTracks.video) return;
        isVideoOff=!isVideoOff;
        localTracks.video.setEnabled(!isVideoOff);
        document.getElementById('videoBtn').classList.toggle('off',isVideoOff);
        document.getElementById('videoIcon').className=isVideoOff?'fas fa-video-slash':'fas fa-video';
    }

    // ── Pusher ───────────────────────────────────────────────────
    const pusher   = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });
    const pChannel = pusher.subscribe('stream');

    pChannel.bind('ViewerJoined', data=>{
        if(!STREAM_KEY || data.stream_key !== STREAM_KEY) return;
        addViewer(data.uid, data.name);
        showJoinToast(data.name);
    });
    pChannel.bind('ViewerLeft', data=>{
        if(!STREAM_KEY || data.stream_key !== STREAM_KEY) return;
        removeViewer(data.uid);
    });
    pChannel.bind('ChatMessage', data=>{
        if(!STREAM_KEY || data.stream_key !== STREAM_KEY) return;
        appendChat(data.name, data.message);
    });

    // ── Auto-start ───────────────────────────────────────────────
    (async()=>{
        try{
            const res  = await fetch("{{ route('stream.active') }}");
            const data = await res.json();
            if(data.stream){
                await startStreaming(data.app_id, data.stream.stream_key, data.token, MY_ID);
                try{
                    const vr=await fetch('/stream/viewers/'+data.stream.stream_key);
                    const vd=await vr.json();
                    if(vd.viewers) vd.viewers.forEach(v=>addViewer(v.uid,v.name));
                }catch(e){}
            } else {
                document.getElementById('streamStatus').textContent='No active stream';
            }
        }catch(e){ console.error('Auto-start error:',e); }
    })();
</script>
</body>
</html>