<?php

namespace App\Http\Controllers;

use App\Events\StreamStopped;
use App\Models\Stream;
use Illuminate\Http\Request;
use Pusher\Pusher;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class StreamingController extends Controller
{
    public function index()
    {
        return view('streams.stream');
    }
    public function startStream(Request $request)
    {
        $streamKey = uniqid('stream_', true);
        $token = $this->generateToken($streamKey, 1);
        $stream = Stream::create([
            'stream_key' => $streamKey,
            'name' => 'jahanzaib',
            'user_id' => 1,
            'status' => 'active',
        ]);
        // if authentication works then uncomment this  and comment above 

        // $stream = Stream::create([
        //     'stream_key' => $streamKey,
        //     'name' => auth()->user()->name,
        //     'user_id' => auth()->id(),
        //     'status' => 'active',
        // ]);

        // Broadcast to Pusher
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
        );

        $pusher->trigger('streams', 'new-stream', [
            'stream_id' => $stream->id,
            'name' => $stream->name,

        ]);

        return response()->json(['stream_key' => $streamKey, 'token' => $token, 'app_id' => config('services.agora.app_id')]);
    }

    public function listStreams()
    {
        $streams = Stream::where('status', 'active')->get();
        return view('streams.list', compact('streams'));
    }

    public function watchStream($id)
    {
        $stream = Stream::findOrFail($id);
        return view('streams.watch', compact('stream'));
    }

    public function endStream(Request $request)
    {
        $stream = Stream::where('user_id', auth()->id())->where('status', 'active')->firstOrFail();
        $stream->status = 'inactive';
        $stream->save();

        return response()->json(['message' => 'Stream ended.']);
    }

    private function generateToken($channelName, $userId)
    {
        $appID = config('services.agora.app_id'); // Ensure this is set in your config/services.php
        $appCertificate = config('services.agora.app_certificate'); // Ensure this is set in your config/services.php
        $expirationTimeInSeconds = 3600; // Token valid for 1 hour

        $currentTimestamp = now()->timestamp;
        $privilegeExpiredTs = $currentTimestamp + $expirationTimeInSeconds;

        // Manually define ROLE_PUBLISHER as 1
        $role = 1;

        return RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $userId,
            $role,
            $privilegeExpiredTs
        );
    }
    public function getWatchToken($streamKey)
    {
        $appID = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');
        $expirationTimeInSeconds = 3600;

        $currentTimestamp = now()->timestamp;
        $privilegeExpiredTs = $currentTimestamp + $expirationTimeInSeconds;

        $userId = random_int(10000, 99999); // Unique ID for the watcher
        $role = 2; // Audience role

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $streamKey,
            $userId,
            $role,
            $privilegeExpiredTs
        );

        return response()->json(['token' => $token, 'uid' => $userId]);
    }
    public function stop(Request $request)
    {
        $user = auth()->user();
        $stream = Stream::where('user_id', 1)->where('status', 'active')->first();
        // $stream = Stream::where('user_id', $user->id)->where('status', 'active')->first();

        if (!$stream) {
            return response()->json(['message' => 'No active stream found'], 404);
        }
        $stream->delete();

        broadcast(new StreamStopped($stream->stream_key));

        return response()->json(['message' => 'Stream stopped and deleted successfully']);
    }
}
