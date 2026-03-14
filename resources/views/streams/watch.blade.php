<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watching {{ $stream->user->name }} - SYncly</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:#0a0a0f;height:100vh;display:flex;flex-direction:column;overflow:hidden;}

        .header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:rgba(255,255,255,0.03);border-bottom:1px solid rgba(255,255,255,0.07);flex-shrink:0;z-index:20;}
        .header-left{display:flex;align-items:center;gap:12px;}
        .logo-img{height:38px;object-fit:contain;}
        .stream-info h2{color:#fff;font-size:16px;font-weight:600;}
        .stream-info p{color:#10b981;font-size:11px;margin-top:2px;display:flex;align-items:center;gap:5px;}
        .live-dot{width:7px;height:7px;border-radius:50%;background:#ef4444;animation:blink 1.2s infinite;}
        @keyframes blink{0%,100%{opacity:1;}50%{opacity:0.3;}}
        .header-right{display:flex;align-items:center;gap:14px;}
        .viewer-pill{display:flex;align-items:center;gap:6px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:20px;padding:4px 12px;}
        .viewer-pill i{color:#ef4444;font-size:11px;}
        .viewer-pill span{color:#fca5a5;font-size:12px;font-weight:600;}
        .stream-timer{color:#9ca3af;font-size:13px;font-variant-numeric:tabular-nums;}

        .main-area{flex:1;display:flex;min-height:0;overflow:hidden;}

        /* Video — contain so full frame shows, no crop */
        .video-stage{flex:1;position:relative;background:#000;overflow:hidden;}
        #player{position:absolute;inset:0;background:#111;}
        #player video{width:100%!important;height:100%!important;object-fit:contain!important;}

        .waiting-overlay{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0d0d14;z-index:10;transition:opacity 0.4s;}
        .waiting-overlay.hidden{opacity:0;pointer-events:none;}
        .waiting-pulse{width:90px;height:90px;border-radius:50%;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;margin-bottom:20px;animation:pulseRing 2s ease-out infinite;}
        .waiting-pulse i{font-size:38px;color:#10b981;}
        @keyframes pulseRing{0%{box-shadow:0 0 0 0 rgba(16,185,129,0.4);}70%{box-shadow:0 0 0 28px rgba(16,185,129,0);}100%{box-shadow:0 0 0 0 rgba(16,185,129,0);}}
        .waiting-overlay h3{color:#fff;font-size:18px;font-weight:600;margin-bottom:6px;}
        .waiting-overlay p{color:#6b7280;font-size:13px;}

        /* Join toasts — bottom-left, float up */
        .join-toasts{position:absolute;bottom:16px;left:14px;display:flex;flex-direction:column-reverse;gap:8px;z-index:30;pointer-events:none;max-width:260px;}
        .join-toast{display:flex;align-items:center;gap:9px;background:rgba(0,0,0,0.62);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.1);border-radius:30px;padding:6px 14px 6px 6px;animation:toastUp 0.35s ease forwards;opacity:0;}
        @keyframes toastUp{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
        .join-toast.fade-out{animation:toastFade 0.4s ease forwards;}
        @keyframes toastFade{to{opacity:0;transform:translateY(-10px);}}
        .toast-avatar{width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;}
        .join-toast span{color:#fff;font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

        /* Floating chat overlay on video */
        .chat-overlay{position:absolute;bottom:0;right:0;width:270px;height:100%;display:flex;flex-direction:column;pointer-events:none;z-index:25;}
        .chat-messages{flex:1;overflow:hidden;padding:12px 10px 10px;display:flex;flex-direction:column;justify-content:flex-end;gap:5px;}
        .chat-msg{display:flex;align-items:flex-start;gap:7px;animation:msgIn 0.25s ease;}
        @keyframes msgIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
        .chat-msg-avatar{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;flex-shrink:0;margin-top:1px;}
        .chat-msg-body{background:rgba(0,0,0,0.52);backdrop-filter:blur(6px);border-radius:12px;padding:5px 10px;max-width:205px;}
        .chat-msg-name{font-size:10px;font-weight:600;margin-bottom:1px;}
        .chat-msg-text{color:#f3f4f6;font-size:12px;line-height:1.4;word-break:break-word;}

        /* Sidebar */
        .sidebar{width:260px;flex-shrink:0;background:rgba(10,10,20,0.97);border-left:1px solid rgba(255,255,255,0.07);display:flex;flex-direction:column;overflow:hidden;}
        .sidebar-tabs{display:flex;border-bottom:1px solid rgba(255,255,255,0.06);flex-shrink:0;}
        .sidebar-tab{flex:1;padding:11px 0;text-align:center;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;border-bottom:2px solid transparent;transition:all 0.2s;}
        .sidebar-tab.active{color:#10b981;border-bottom-color:#10b981;}
        .sidebar-tab i{margin-right:4px;}
        .tab-panel{display:none;flex:1;flex-direction:column;overflow:hidden;}
        .tab-panel.active{display:flex;}

        .viewers-list{flex:1;overflow-y:auto;padding:8px 10px;}
        .viewers-list::-webkit-scrollbar{width:3px;}
        .viewers-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.08);border-radius:3px;}
        .viewer-item{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:9px;transition:background 0.2s;animation:slideIn 0.25s ease;}
        @keyframes slideIn{from{opacity:0;transform:translateX(8px);}to{opacity:1;transform:translateX(0);}}
        .viewer-item:hover{background:rgba(255,255,255,0.04);}
        .viewer-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;}
        .viewer-name-el{color:#e5e7eb;font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .viewer-time{color:#6b7280;font-size:10px;}
        .viewers-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;color:#4b5563;padding:20px;text-align:center;}
        .viewers-empty i{font-size:30px;}
        .viewers-empty p{font-size:12px;line-height:1.5;}

        .sidebar-chat-msgs{flex:1;overflow-y:auto;padding:10px 12px;display:flex;flex-direction:column;gap:7px;}
        .sidebar-chat-msgs::-webkit-scrollbar{width:3px;}
        .sidebar-chat-msgs::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.08);border-radius:3px;}
        .schat-msg{display:flex;gap:8px;animation:msgIn 0.2s ease;}
        .schat-avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;}
        .schat-name{font-size:11px;font-weight:600;margin-bottom:2px;}
        .schat-text{color:#d1d5db;font-size:13px;line-height:1.4;word-break:break-word;}
        .schat-time{color:#4b5563;font-size:10px;margin-top:2px;}
        .chat-input-wrap{padding:10px 12px;border-top:1px solid rgba(255,255,255,0.06);display:flex;gap:8px;flex-shrink:0;}
        .chat-input{flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:8px 14px;color:#fff;font-size:13px;font-family:'Poppins',sans-serif;outline:none;}
        .chat-input::placeholder{color:#6b7280;}
        .chat-input:focus{border-color:rgba(16,185,129,0.4);}
        .chat-send{width:36px;height:36px;border-radius:50%;border:none;background:#10b981;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.2s;}
        .chat-send:hover{background:#059669;transform:scale(1.08);}
        .chat-send i{font-size:14px;}

        .controls-bar{display:flex;align-items:center;justify-content:center;gap:14px;padding:16px 24px;background:rgba(0,0,0,0.7);backdrop-filter:blur(12px);border-top:1px solid rgba(255,255,255,0.05);flex-shrink:0;}
        .ctrl-btn{width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;background:rgba(255,255,255,0.1);color:#fff;}
        .ctrl-btn i{font-size:18px;}
        .ctrl-btn:hover{transform:scale(1.1);background:rgba(255,255,255,0.18);}
        .ctrl-btn.off{background:#ef4444;}
        .ctrl-label{display:flex;flex-direction:column;align-items:center;gap:5px;}
        .ctrl-label span{font-size:10px;color:#6b7280;}

        .ended-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:200;align-items:center;justify-content:center;flex-direction:column;gap:16px;}
        .ended-overlay.show{display:flex;}
        .ended-overlay i{font-size:56px;color:#ef4444;}
        .ended-overlay h2{color:#fff;font-size:24px;}
        .ended-overlay p{color:#9ca3af;font-size:14px;}
        .redirect-bar{width:200px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;margin-top:8px;}
        .redirect-fill{height:100%;background:#ef4444;animation:fillBar 3s linear forwards;}
        @keyframes fillBar{from{width:0%;}to{width:100%;}}
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <a href="{{ route('stream.list') }}"><img src="{{ asset('dark syncly.png') }}" alt="SYncly" class="logo-img"></a>
        <div class="stream-info">
            <h2><i class="fas fa-play"></i> {{ $stream->user->name }}</h2>
            <p><span class="live-dot"></span><span id="streamStatus">Connecting...</span></p>
        </div>
    </div>
    <div class="header-right">
        <div class="viewer-pill"><i class="fas fa-eye"></i><span id="viewerPillCount">0</span></div>
        <span class="stream-timer" id="streamTimer">00:00</span>
    </div>
</div>

<div class="main-area">

    <div class="video-stage">
        <div id="player"></div>

        <div class="waiting-overlay" id="waitingOverlay">
            <div class="waiting-pulse"><i class="fas fa-broadcast-tower"></i></div>
            <h3>Waiting for stream...</h3>
            <p>{{ $stream->user->name }} will begin shortly</p>
        </div>

        <div class="join-toasts" id="joinToasts"></div>

        <div class="chat-overlay">
            <div class="chat-messages" id="videoChatMsgs"></div>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-tabs">
            <div class="sidebar-tab active" onclick="switchTab('viewers')" id="tab-viewers">
                <i class="fas fa-users"></i> Viewers
            </div>
            <div class="sidebar-tab" onclick="switchTab('chat')" id="tab-chat">
                <i class="fas fa-comments"></i> Chat
            </div>
        </div>

        <div class="tab-panel active" id="panel-viewers">
            <div class="viewers-list" id="viewersList">
                <div class="viewers-empty" id="viewersEmpty">
                    <i class="fas fa-users"></i>
                    <p>Loading viewers...</p>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="panel-chat">
            <div class="sidebar-chat-msgs" id="sidebarChatMsgs"></div>
            <div class="chat-input-wrap">
                <input class="chat-input" id="chatInput" type="text" placeholder="Say something..." maxlength="200">
                <button class="chat-send" onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="controls-bar">
    <div class="ctrl-label">
        <button class="ctrl-btn" id="muteBtn" onclick="toggleMute()"><i class="fas fa-volume-up" id="muteIcon"></i></button>
        <span>Volume</span>
    </div>
    <div class="ctrl-label">
        <button class="ctrl-btn" onclick="leaveStream()"><i class="fas fa-arrow-left"></i></button>
        <span>Leave</span>
    </div>
</div>

<div class="ended-overlay" id="endedOverlay">
    <i class="fas fa-stop"></i>
    <h2>Stream Ended</h2>
    <p>The stream has finished</p>
    <div class="redirect-bar"><div class="redirect-fill"></div></div>
</div>

<script>
    const APP_ID         = "{{ config('services.agora.app_id') }}";
    const STREAM_KEY     = "{{ $stream->stream_key }}";
    const CSRF           = "{{ csrf_token() }}";
    const PUSHER_KEY     = "{{ config('broadcasting.connections.pusher.key') }}";
    const PUSHER_CLUSTER = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
    const MY_NAME        = "{{ auth()->user()->name }}";
    const MY_ID          = {{ auth()->id() }};

    let agoraClient = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
    agoraClient.setClientRole('audience');

    let remoteTracks = {}, isMuted = false;
    let timerSecs = 0, timerInterval = null;
    let isConnected = false, isEnded = false;
    let myAgoraUid  = null;
    let viewers     = {};

    // ── Helpers ─────────────────────────────────────────────────
    function initials(name){ return (name||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2); }
    function randColor(name){
        const colors=['#10b981','#6366f1','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
        let h=0; for(let c of (name||'')) h=c.charCodeAt(0)+((h<<5)-h);
        return colors[Math.abs(h)%colors.length];
    }

    // ── Join toast ───────────────────────────────────────────────
    function showJoinToast(name, isSelf=false){
        const container = document.getElementById('joinToasts');
        const el = document.createElement('div');
        el.className = 'join-toast';
        const color = randColor(name);
        el.innerHTML = `<div class="toast-avatar" style="background:linear-gradient(135deg,${color},${color}99)">${initials(name)}</div>
            <span>${isSelf ? '👋 You joined' : `<b>${name}</b> is watching`}</span>`;
        container.prepend(el);
        setTimeout(()=>{ el.classList.add('fade-out'); setTimeout(()=>el.remove(),400); }, 4000);
        while(container.children.length > 5) container.lastChild.remove();
    }

    // ── Viewers list ─────────────────────────────────────────────
    function renderViewers(){
        const list  = document.getElementById('viewersList');
        const empty = document.getElementById('viewersEmpty');
        const count = Object.keys(viewers).length;
        document.getElementById('viewerPillCount').textContent = count;
        list.querySelectorAll('.viewer-item').forEach(el=>el.remove());
        if(count===0){ empty.style.display='flex'; return; }
        empty.style.display='none';
        Object.values(viewers).forEach(v=>{
            const color = randColor(v.name);
            const el = document.createElement('div');
            el.className = 'viewer-item';
            el.innerHTML = `<div class="viewer-avatar" style="background:linear-gradient(135deg,${color},${color}99)">${initials(v.name)}</div>
                <div><div class="viewer-name-el">${v.name}${v.uid==MY_ID?' (You)':''}</div>
                <div class="viewer-time">${v.joinedAt.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}</div></div>`;
            list.appendChild(el);
        });
    }
    function addViewer(uid, name){ if(viewers[uid]) return; viewers[uid]={uid,name:name||'Viewer #'+uid,joinedAt:new Date()}; renderViewers(); }
    function removeViewer(uid){ delete viewers[uid]; renderViewers(); }

    // ── Chat ─────────────────────────────────────────────────────
    function appendChatMsg(name, text, isSelf=false){
        const color = randColor(name);
        const time  = new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});

        const sb  = document.getElementById('sidebarChatMsgs');
        const sel = document.createElement('div');
        sel.className = 'schat-msg';
        sel.innerHTML = `<div class="schat-avatar" style="background:linear-gradient(135deg,${color},${color}99)">${initials(name)}</div>
            <div><div class="schat-name" style="color:${color}">${isSelf?'You':name}</div>
            <div class="schat-text">${text}</div>
            <div class="schat-time">${time}</div></div>`;
        sb.appendChild(sel);
        sb.scrollTop = sb.scrollHeight;

        const vc  = document.getElementById('videoChatMsgs');
        const vel = document.createElement('div');
        vel.className = 'chat-msg';
        vel.innerHTML = `<div class="chat-msg-avatar" style="background:linear-gradient(135deg,${color},${color}99)">${initials(name)}</div>
            <div class="chat-msg-body"><div class="chat-msg-name" style="color:${color}">${isSelf?'You':name}</div>
            <div class="chat-msg-text">${text}</div></div>`;
        vc.appendChild(vel);
        vc.scrollTop = vc.scrollHeight;
        while(vc.children.length>20) vc.removeChild(vc.firstChild);
        setTimeout(()=>{ vel.style.transition='opacity 0.5s'; vel.style.opacity='0'; setTimeout(()=>vel.remove(),500); },8000);
    }

    function sendChat(){
        const input = document.getElementById('chatInput');
        const text  = input.value.trim();
        if(!text) return;
        input.value = '';
        appendChatMsg(MY_NAME, text, true);
        fetch('/stream/chat',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({ stream_key: STREAM_KEY, message: text })
        }).catch(console.error);
    }
    document.getElementById('chatInput').addEventListener('keydown', e=>{ if(e.key==='Enter') sendChat(); });

    function switchTab(tab){
        document.querySelectorAll('.sidebar-tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
        document.getElementById('tab-'+tab).classList.add('active');
        document.getElementById('panel-'+tab).classList.add('active');
    }

    function startTimer(){
        timerInterval=setInterval(()=>{
            timerSecs++;
            document.getElementById('streamTimer').textContent=
                String(Math.floor(timerSecs/60)).padStart(2,'0')+':'+String(timerSecs%60).padStart(2,'0');
        },1000);
    }

    function showEnded(){
        if(isEnded) return; isEnded=true;
        clearInterval(timerInterval);
        // Use beacon here since the stream ended (not user-initiated), page will redirect
        navigator.sendBeacon('/stream/viewer-left', new Blob(
            [JSON.stringify({stream_key:STREAM_KEY, uid:myAgoraUid})],
            {type:'application/json'}
        ));
        document.getElementById('endedOverlay').classList.add('show');
        setTimeout(()=>{ window.location.href="{{ route('stream.list') }}"; },3000);
    }

    function toggleMute(){
        isMuted=!isMuted;
        Object.values(remoteTracks).forEach(t=>{ if(t.audio) t.audio.setVolume(isMuted?0:100); });
        document.getElementById('muteBtn').classList.toggle('off',isMuted);
        document.getElementById('muteIcon').className=isMuted?'fas fa-volume-mute':'fas fa-volume-up';
    }

    async function leaveStream(){
        // Disable button to prevent double-click
        document.querySelector('.ctrl-btn[onclick="leaveStream()"]').disabled = true;

        // Wait for server to process viewer-left so count updates BEFORE we leave
        try {
            await fetch('/stream/viewer-left', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ stream_key: STREAM_KEY, uid: myAgoraUid })
            });
        } catch(e) {
            // Fallback to beacon if fetch fails
            navigator.sendBeacon('/stream/viewer-left', new Blob(
                [JSON.stringify({ stream_key: STREAM_KEY, uid: myAgoraUid })],
                { type: 'application/json' }
            ));
        }

        // Leave Agora cleanly
        try { await agoraClient.leave(); } catch(e) {}

        window.location.href = "{{ route('stream.list') }}";
    }

    // ── Pusher ───────────────────────────────────────────────────
    const pusher   = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });
    const pChannel = pusher.subscribe('stream');

    pChannel.bind('ViewerJoined', data=>{
        if(data.stream_key !== STREAM_KEY) return;
        addViewer(data.uid, data.name);
        // Show toast for everyone except self (self toast shown on join below)
        if(data.uid !== MY_ID) showJoinToast(data.name);
    });
    pChannel.bind('ViewerLeft', data=>{
        if(data.stream_key !== STREAM_KEY) return;
        removeViewer(data.uid);
    });
    pChannel.bind('ChatMessage', data=>{
        if(data.stream_key !== STREAM_KEY) return;
        if(data.user_id !== MY_ID) appendChatMsg(data.name, data.message);
    });
    pChannel.bind('StreamStopped', data=>{
        if(data.streamKey===STREAM_KEY || data.stream_key===STREAM_KEY) showEnded();
    });

    // ── Join ─────────────────────────────────────────────────────
    async function joinStream(){
        try{
            const res  = await fetch(`/stream/watch-token/${STREAM_KEY}`);
            const data = await res.json();
            if(!data.token) throw new Error('No token');

            myAgoraUid = data.uid;

            await agoraClient.join(APP_ID, STREAM_KEY, data.token, data.uid);
            document.getElementById('streamStatus').textContent = 'Connected — waiting for host...';

            agoraClient.on('user-published', async(user, mediaType)=>{
                await agoraClient.subscribe(user, mediaType);
                if(!remoteTracks[user.uid]) remoteTracks[user.uid]={};
                if(mediaType==='video'){
                    remoteTracks[user.uid].video = user.videoTrack;
                    user.videoTrack.play('player');
                    document.getElementById('waitingOverlay').classList.add('hidden');
                    if(!isConnected){ isConnected=true; document.getElementById('streamStatus').textContent='Live'; startTimer(); }
                }
                if(mediaType==='audio'){ remoteTracks[user.uid].audio=user.audioTrack; user.audioTrack.play(); }
            });

            agoraClient.on('user-unpublished',(user,mediaType)=>{
                if(remoteTracks[user.uid]){
                    if(mediaType==='video'&&remoteTracks[user.uid].video) remoteTracks[user.uid].video.stop();
                    if(mediaType==='audio'&&remoteTracks[user.uid].audio) remoteTracks[user.uid].audio.stop();
                }
            });

            agoraClient.on('user-left', ()=>showEnded());

            // Notify backend → triggers ViewerJoined Pusher event
            await fetch('/stream/viewer-joined',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                body:JSON.stringify({ stream_key: STREAM_KEY, uid: data.uid, name: MY_NAME, user_id: MY_ID })
            });

            // Show own join toast
            showJoinToast(MY_NAME, true);
            addViewer(MY_ID, MY_NAME);

            // Load existing viewers
            try{
                const vr = await fetch('/stream/viewers/'+STREAM_KEY);
                const vd = await vr.json();
                if(vd.viewers) vd.viewers.forEach(v=>addViewer(v.uid||v.user_id, v.name));
            }catch(e){}

        }catch(err){
            console.error('Join error:', err);
            document.getElementById('streamStatus').textContent='Failed to connect';
        }
    }

    window.addEventListener('beforeunload', ()=>{
        // keepalive:true lets the request complete even as page unloads
        // This is the fallback if leaveStream() wasn't called (e.g. browser close)
        if(!isEnded && myAgoraUid) {
            fetch('/stream/viewer-left', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ stream_key: STREAM_KEY, uid: myAgoraUid }),
                keepalive: true
            });
        }
    });

    joinStream();
</script>
</body>
</html>