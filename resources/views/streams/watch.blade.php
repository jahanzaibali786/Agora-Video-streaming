<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.jsdelivr.net/npm/agora-rtc-sdk-ng"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>

<body>
    <h1>Watching Stream: {{ $stream->name }}</h1>
    <div id="player" style="height: 200px; height:200px;"></div>

    <script>
        const APP_ID = "{{ config('services.agora.app_id') }}";
        const STREAM_KEY = "{{ $stream->stream_key }}";
        const PLAYER_CONTAINER_ID = "player";

        let client = AgoraRTC.createClient({
            mode: "live",
            codec: "vp8"
        });

        async function fetchToken() {
            const response = await fetch(`/stream/watch-token/${STREAM_KEY}`);
            if (!response.ok) {
                throw new Error("Failed to fetch token");
            }
            return response.json();
        }

        async function joinStream() {
            try {
                const {
                    token,
                    uid
                } = await fetchToken();
                await client.join(APP_ID, STREAM_KEY, token, uid);

                client.on("user-published", async (user, mediaType) => {
                    await client.subscribe(user, mediaType);

                    if (mediaType === "video") {
                        const remoteVideoTrack = user.videoTrack;
                        const playerContainer = document.createElement("div");
                        playerContainer.id = user.uid;
                        playerContainer.style.width = "640px";
                        playerContainer.style.height = "480px";
                        playerContainer.style.background =
                            "url('{{ asset('random/beach-5483065_1920.jpg') }}') no-repeat center";
                        playerContainer.style.backgroundSize = "cover";
                        document.getElementById(PLAYER_CONTAINER_ID).appendChild(playerContainer);

                        remoteVideoTrack.play(playerContainer);
                    }
                });

                console.log("Joined the stream successfully!");
            } catch (error) {
                console.error("Error joining stream:", error);
            }
        }

        joinStream();


        const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            encrypted: true,
        });

        const channel = pusher.subscribe('stream');

        channel.bind('StreamStopped', function(data) {
            if (data.streamKey === "{{ $stream->stream_key }}") {
                alert("The stream has ended.");
                window.location.href = "/stream";
            }
        });
    </script>
</body>

</html>
