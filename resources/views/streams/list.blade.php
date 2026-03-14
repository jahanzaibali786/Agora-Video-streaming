<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streams - SYncly</title>
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
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 40px; padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .logo-img { height: 60px; object-fit: contain; }
        .back-btn { color: #10b981; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .back-btn:hover { color: #34d399; }
        .streams-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; }
        .stream-card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 24px; text-align: center; transition: all 0.3s ease;
        }
        .stream-card:hover { background: rgba(255,255,255,0.08); transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .profile-wrapper { position: relative; width: 80px; height: 80px; margin: 0 auto 16px; }
        .profile-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.1); }
        .online-dot { position: absolute; bottom: 4px; right: 4px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #1a1a2e; }
        .online-dot.live { background: #ef4444; }
        .stream-name { color: #fff; font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .stream-status { color: #9ca3af; font-size: 12px; margin-bottom: 16px; }
        .watch-btn { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; }
        .watch-btn:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
        .start-stream-btn { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 12px 24px; border-radius: 25px; cursor: pointer; font-size: 16px; position: fixed; bottom: 24px; right: 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); transition: all 0.3s ease; }
        .start-stream-btn:hover { transform: scale(1.05); }
        .start-stream-btn:disabled { background: #4b5563; cursor: not-allowed; }
        .toast { display: none; position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%); background: rgba(30,30,40,0.95); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 14px 24px; color: #fff; font-size: 14px; z-index: 2000; }
        .toast.show { display: block; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="container">
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

        <div class="streams-grid" id="streamsGrid">
            @foreach ($streams as $stream)
                <div class="stream-card">
                    <div class="profile-wrapper">
                        <img src="{{ asset('user-avatar.jpg') }}" alt="{{ $stream->user->name }}" class="profile-img">
                        <div class="online-dot live"></div>
                    </div>
                    <h3 class="stream-name">{{ $stream->user->name }}</h3>
                    <p class="stream-status">Live Streaming</p>
                    <button class="watch-btn" onclick="watchStream({{ $stream->id }})">
                        <i class="fas fa-play"></i> Watch
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    @php
        $hasActiveStream = $streams->where('user_id', auth()->id())->isNotEmpty();
    @endphp
    @if(!$hasActiveStream)
    <button class="start-stream-btn" id="startStreamBtn" onclick="startStream()">
        <i class="fas fa-video"></i> Start Stream
    </button>
    @endif

    <div class="toast" id="toast"></div>

    <script>
        const CSRF = "{{ csrf_token() }}";

        function showToast(msg, ms = 3500) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), ms);
        }

        function watchStream(streamId) {
            window.location.href = '/stream/' + streamId;
        }

        async function startStream() {
            const btn = document.getElementById('startStreamBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';

            try {
                const res = await fetch('/stream/start', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                const data = await res.json();
                if (data.stream_key) {
                    window.location.href = '{{ route("stream.index") }}';
                } else {
                    showToast('Failed to start stream');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-video"></i> Start Stream';
                }
            } catch(e) {
                console.error(e);
                showToast('Error starting stream');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-video"></i> Start Stream';
            }
        }

        // Pusher for real-time updates
        function initPusher() {
            if (typeof Pusher === 'undefined') { setTimeout(initPusher, 300); return; }

            const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
                cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
                forceTLS: true,
            });

            const channel = pusher.subscribe('streams');
            channel.bind('new-stream', function (data) {
                // Reload the page or add the new stream
                location.reload();
            });
        }

        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', initPusher)
            : initPusher();
    </script>
</body>
</html>
