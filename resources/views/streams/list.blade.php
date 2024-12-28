<!-- resources/views/streams/list.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>
<body>
    <h1>Active Streams</h1>
    <ul id="streamList">
        @foreach ($streams as $stream)
            <li>
                <a href="{{ route('stream.watch', $stream->id) }}">{{ $stream->name }}</a>
            </li>
        @endforeach
    </ul>

    <script>
        const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        });

        const channel = pusher.subscribe('streams');
        channel.bind('new-stream', function (data) {
            const list = document.getElementById('streamList');
            const item = document.createElement('li');
            item.innerHTML = `<a href="/stream/${data.stream_id}">${data.name}</a>`;
            list.appendChild(item);
        });
    </script>
</body>
</html>
