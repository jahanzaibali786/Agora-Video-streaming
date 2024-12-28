<!-- resources/views/streams/stream.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.jsdelivr.net/npm/agora-rtc-sdk-ng"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>

<body>
    <h1>Start Streaming</h1>
    <button id="startStream">Start Stream</button>
    <button id="stopStream">Stop Stream</button>
    <div id="local-player" style="width: 640px; height: 360px; background-color: #000;"></div>

    <script>
        const APP_ID = "{{ config('services.agora.app_id') }}";
        let client = AgoraRTC.createClient({
            mode: "live",
            codec: "vp8"
        });

        document.getElementById('startStream').addEventListener('click', async () => {
            const response = await fetch("{{ route('stream.start') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
            });
            const data = await response.json();
            const appId = data.app_id;
            const channelName = data.stream_key;
            const token = data.token;

            const streamKey = data.stream_key;

            await client.join(appId, channelName, token, 1);
            // await client.join(APP_ID, streamKey, null, "{{ auth()->id() }}");
            await client.setClientRole("host");
            const localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            const localVideoTrack = await AgoraRTC.createCameraVideoTrack();
            localVideoTrack.play("local-player");

            client.publish([localAudioTrack, localVideoTrack]);
        });

        document.getElementById('stopStream').addEventListener('click', async () => {
            try {
                client.localTracks.forEach(track => {track.stop(); track.close();});
                localTracks = [];
                await client.leave();
                const response = await fetch("{{ route('stream.stop') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                });

                if (!response.ok) {
                    throw new Error("Failed to stop stream");
                }
                const data = await response.json();
                alert('Stream stopped successfully');
            } catch (error) {
                console.error("Error stopping stream:", error);
            }
        });
    </script>
</body>

</html>
