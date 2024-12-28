<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<style>
    .page-wrapper {
        padding: 0px !important;
    }

    .card-container {
        display: flex;
        flex-direction: row;
        background: #000 !important;
        padding: 10px;
        gap: 30px;
        row-gap: 30px;
    }

    .card {
        background: #000 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        text-align: center;
        height: 200px;
        width: 200px;
        transition: transform 0.3s ease-in-out;
    }

    .card .profile-img {
        height: 100%;
        width: 100%;
    }

    .card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .call-btn {
        background: #4dbc09;
        color: #fff;
        border: none;
        padding: 10px 20px;
    }

    .name {
        position: absolute;
        bottom: 0px;
        background: rgba(0, 0, 0, 0.4);
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        height: 40px;
        padding: 0px 0px 0px 10px;
    }

    #incomingCallModal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        text-align: center;
        z-index: 1000;
    }

    #local-player, #remote-player {
        width: 100%;
        height: 50vh;
        background-color: black;
    }
</style>
<div class="card-container">
    @foreach ($users as $user)
        <div class="card">
            <div class="profile-img">
                <div class="name">
                    <p>{{ @$user->name }}</p>
                    <button class="call-btn" data-user-id="{{ $user->id }}"><i class="fa fa-phone"></i></button>
                </div>
                <img src="{{ @$user->userProfile->profile_image ? asset('storage/' . @$user->userProfile->profile_image) : asset('avatar.png') }}" alt="Profile">
            </div>
        </div>
    @endforeach
</div>

<!-- Video Call Modals -->
<div id="incomingCallModal" style="display: none;">
    <p class="caller-name"></p>
    <button id="answerCall">Answer</button>
    <button id="rejectCall">Reject</button>
</div>

<div id="local-player" style="width: 100%; height: 50vh; background-color: black;"></div>
<div id="remote-player" style="width: 100%; height: 50vh; background-color: black;"></div>

<script>
    const APP_ID = "{{ env('AGORA_APP_ID') }}";
    const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
    let localTracks = [];
    let remoteTracks = {};

    // Pusher setup
    const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        encrypted: true
    });

    const channel = pusher.subscribe(`user.{{ auth()->id() }}`);

    channel.bind('incoming-call', (data) => {
        console.log('Incoming call:', data);
        document.getElementById('incomingCallModal').style.display = 'block';
        document.querySelector('.caller-name').innerText = data.caller.name;

        document.getElementById('answerCall').addEventListener('click', () => {
            startReceiverVideoCall(data.channelName);
            document.getElementById('incomingCallModal').style.display = 'none';
        });

        document.getElementById('rejectCall').addEventListener('click', () => {
            document.getElementById('incomingCallModal').style.display = 'none';
        });
    });

    document.querySelectorAll('.call-btn').forEach(button => {
        button.addEventListener('click', async (event) => {
            const recipientId = event.target.closest('.call-btn').dataset.userId;
            const channelName = `call_{{ auth()->id() }}_${recipientId}_${Date.now()}`;

            await startCallerVideoCall(channelName);

            const response = await fetch("{{ route('call.initiate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                body: JSON.stringify({
                    recipient_id: recipientId,
                    channel_name: channelName,
                })
            });

            const data = await response.json();
            console.log('Call initiated:', data.channel_name);
        });
    });

    async function startCallerVideoCall(channelName) {
        const tokenResponse = await fetch("{{ route('call.token') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                channel_name: channelName,
                uid: "{{ auth()->id() }}",
            })
        });

        const tokenData = await tokenResponse.json();
        const token = tokenData.token;

        await client.join(APP_ID, channelName, token, "{{ auth()->id() }}");

        const localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
        const localVideoTrack = await AgoraRTC.createCameraVideoTrack();
        localTracks.push(localAudioTrack, localVideoTrack);

        localVideoTrack.play("local-player");
        await client.publish(localTracks);
        
         client.on("user-published", async (user, mediaType) => {
            console.log("Remote user published:", user.uid);

            // Subscribe to the remote user's media
            await client.subscribe(user, mediaType);

            // Handle video track
            if (mediaType === "video") {
                const remoteVideoTrack = user.videoTrack;
                if (remoteVideoTrack) {
                    console.log("Playing remote video for user:", user.uid);
                    remoteVideoTrack.play("remote-player"); // Update with your remote player container ID
                } else {
                    console.error("No video track found for user:", user.uid);
                }
            }

            // Handle audio track
            if (mediaType === "audio") {
                const remoteAudioTrack = user.audioTrack;
                if (remoteAudioTrack) {
                    console.log("Playing remote audio for user:", user.uid);
                    remoteAudioTrack.play();
                } else {
                    console.error("No audio track found for user:", user.uid);
                }
            }
        });

        // Handle other connection events
        client.on("connection-state-change", (curState, prevState) => {
            console.log(`Connection state changed from ${prevState} to ${curState}`);
        });
    }

//   async function startReceiverVideoCall(channelName) {
//       console.log(channelName);
//     try {
//         const tokenResponse = await fetch("{{ route('call.token') }}", {
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json",
//                 "X-CSRF-TOKEN": "{{ csrf_token() }}",
//             },
//             body: JSON.stringify({
//                 channel_name: channelName,
//                 uid: "{{ auth()->id() }}",
//             }),
//         });

//         const tokenData = await tokenResponse.json();
//         const token = tokenData.token;

//         await client.join(APP_ID, channelName, token, "{{ auth()->id() }}");
//         console.log("Joined channel:", channelName);

//         client.on("user-published", async (user, mediaType) => {
//             console.log("User published:", user.uid, mediaType);

//             try {
//                 await client.subscribe(user, mediaType);
//                 console.log("Subscribed to user:", user.uid);

//                 if (mediaType === "video") {
//                     const remoteVideoTrack = user.videoTrack;
//                     if (remoteVideoTrack) {
//                         console.log("Playing remote video for user:", user.uid);
//                         remoteVideoTrack.play("remote-player");
//                     } else {
//                         console.error("No video track found for user:", user.uid);
//                     }
//                 }
//             } catch (err) {
//                 console.error("Subscription failed:", err);
//             }
//         });

//         client.on("connection-state-change", (curState, prevState) => {
//             console.log(`Connection state changed from ${prevState} to ${curState}`);
//         });
//     } catch (error) {
//         console.error("Error in video call setup:", error);
//     }
// }
async function startReceiverVideoCall(channelName) {
    try {
        // Fetch token for receiver
        const tokenResponse = await fetch("{{ route('call.token') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                channel_name: channelName,
                uid: "{{ auth()->id() }}",
            }),
        });
        const tokenData = await tokenResponse.json();
        const token = tokenData.token;

        // Join the channel
        await client.join(APP_ID, channelName, token, "{{ auth()->id() }}");

        // Log channel name
        console.log("Joined channel:", channelName);

        // Listen for remote users publishing streams
        client.on("user-published", async (user, mediaType) => {
            console.log("Remote user published:", user.uid);

            // Subscribe to the remote user's media
            await client.subscribe(user, mediaType);

            // Handle video track
            if (mediaType === "video") {
                const remoteVideoTrack = user.videoTrack;
                if (remoteVideoTrack) {
                    console.log("Playing remote video for user:", user.uid);
                    remoteVideoTrack.play("remote-player"); // Update with your remote player container ID
                } else {
                    console.error("No video track found for user:", user.uid);
                }
            }

            // Handle audio track
            if (mediaType === "audio") {
                const remoteAudioTrack = user.audioTrack;
                if (remoteAudioTrack) {
                    console.log("Playing remote audio for user:", user.uid);
                    remoteAudioTrack.play();
                } else {
                    console.error("No audio track found for user:", user.uid);
                }
            }
        });

        // Handle other connection events
        client.on("connection-state-change", (curState, prevState) => {
            console.log(`Connection state changed from ${prevState} to ${curState}`);
        });
        const localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
        const localVideoTrack = await AgoraRTC.createCameraVideoTrack();
        localTracks.push(localAudioTrack, localVideoTrack);

        localVideoTrack.play("local-player");
        await client.publish(localTracks);
    } catch (error) {
        console.error("Error in startReceiverVideoCall:", error);
    }
}

</script>
