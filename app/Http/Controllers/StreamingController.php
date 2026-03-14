<?php

namespace App\Http\Controllers;

use App\Events\StreamStopped;
use App\Events\ViewerJoined;
use App\Events\ViewerLeft;
use App\Events\ChatMessage;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;  // ← was missing, caused Cache error
use Pusher\Pusher;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class StreamingController extends Controller
{
    // ── Pages ────────────────────────────────────────────────────

    public function index()
    {
        return view('streams.stream');
    }

    public function listStreams()
    {
        $streams = Stream::where('status', 'active')->with('user')->get();
        return view('streams.list', compact('streams'));
    }

    public function watchStream($id)
    {
        $stream = Stream::findOrFail($id);
        return view('streams.watch', compact('stream'));
    }

    // ── Start stream ─────────────────────────────────────────────

    public function startStream(Request $request)
    {
        $streamKey = uniqid('stream_', true);
        $token     = $this->generateToken($streamKey, auth()->id());

        $stream = Stream::create([
            'stream_key' => $streamKey,
            'name'       => auth()->user()->name,
            'user_id'    => auth()->id(),
            'status'     => 'active',
        ]);

        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
        );

        $pusher->trigger('streams', 'new-stream', [
            'stream_id' => $stream->id,
            'name'      => $stream->name,
        ]);

        return response()->json([
            'stream_key' => $streamKey,
            'token'      => $token,
            'app_id'     => config('services.agora.app_id'),
        ]);
    }

    // ── Stop stream ──────────────────────────────────────────────

    public function stop(Request $request)
    {
        $stream = Stream::where('user_id', auth()->id())
                        ->where('status', 'active')
                        ->first();

        if (!$stream) {
            return response()->json(['message' => 'No active stream found'], 404);
        }

        $streamKey = $stream->stream_key;

        // Clear viewer cache for this stream
        Cache::forget('stream_viewers_' . $streamKey);

        $stream->delete();

        broadcast(new StreamStopped($streamKey));

        return response()->json(['message' => 'Stream stopped']);
    }

    // Keep old endStream for backward compat
    public function endStream(Request $request)
    {
        return $this->stop($request);
    }

    // ── Active stream (streamer page auto-start) ─────────────────

    public function getActiveStream()
    {
        $stream = Stream::where('user_id', auth()->id())
                        ->where('status', 'active')
                        ->first();

        if ($stream) {
            $token = $this->generateToken($stream->stream_key, auth()->id());
            return response()->json([
                'stream'  => $stream,
                'token'   => $token,
                'app_id'  => config('services.agora.app_id'),
            ]);
        }

        return response()->json(['stream' => null]);
    }

    // ── Viewer token (single method, no duplicate) ───────────────

    public function getWatchToken($streamKey)
    {
        $uid    = random_int(100000, 999999);
        $token  = $this->generateToken($streamKey, $uid, 2); // role 2 = audience

        return response()->json(['token' => $token, 'uid' => $uid]);
    }

    // ── Viewers tracking ─────────────────────────────────────────

    public function viewerJoined(Request $request)
    {
        $request->validate([
            'stream_key' => 'required|string',
            'uid'        => 'required',
            'name'       => 'required|string',
        ]);

        $cacheKey = 'stream_viewers_' . $request->stream_key;
        $viewers  = Cache::get($cacheKey, []);
        $uid      = (string) $request->uid;

        if (!isset($viewers[$uid])) {
            $viewers[$uid] = [
                'uid'       => $uid,
                'user_id'   => auth()->id(),
                'name'      => $request->name,
                'joined_at' => now()->toISOString(),
            ];
            Cache::put($cacheKey, $viewers, now()->addHours(4));
        }

        // toOthers() so the viewer doesn't receive their own join event
        broadcast(new ViewerJoined(
            $request->stream_key,
            $uid,
            $request->name,
            auth()->id()
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function viewerLeft(Request $request)
    {
        // sendBeacon sends raw JSON body — handle both cases
        $data = $request->all();
        if (empty($data)) {
            $data = json_decode($request->getContent(), true) ?? [];
        }

        $streamKey = $data['stream_key'] ?? null;
        $uid       = (string) ($data['uid'] ?? '');

        if ($streamKey && $uid) {
            $cacheKey = 'stream_viewers_' . $streamKey;
            $viewers  = Cache::get($cacheKey, []);
            unset($viewers[$uid]);
            Cache::put($cacheKey, $viewers, now()->addHours(4));

            broadcast(new ViewerLeft($streamKey, $uid))->toOthers();
        }

        return response()->json(['success' => true]);
    }

    public function getViewers($streamKey)
    {
        $viewers = Cache::get('stream_viewers_' . $streamKey, []);
        return response()->json(['viewers' => array_values($viewers)]);
    }

    // ── Chat ─────────────────────────────────────────────────────

    public function sendChat(Request $request)
    {
        $request->validate([
            'stream_key' => 'required|string',
            'message'    => 'required|string|max:200',
        ]);

        broadcast(new ChatMessage(
            $request->stream_key,
            auth()->user()->name,
            auth()->id(),
            $request->message
        ));

        return response()->json(['success' => true]);
    }

    // ── Token generator ──────────────────────────────────────────

    private function generateToken(string $channelName, int $uid, int $role = 1): string
    {
        $appId          = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');
        $expiry         = now()->timestamp + 3600;

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $expiry
        );
    }
}