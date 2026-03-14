<?php

namespace App\Http\Controllers;

use App\Events\IncomingCall;
use App\Events\CallCancelled;   // ← THIS WAS MISSING — caused silent fatal error
use App\Models\User;
use App\Models\CallLog;
use Illuminate\Http\Request;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class CallController extends Controller
{
    public function index()
    {
        $users = User::whereNot('id', auth()->id())->get();
        return view('call.index', compact('users'));
    }

    public function videoCall(Request $request)
    {
        $channel  = $request->query('channel');
        $type     = $request->query('type', 'video');
        $userId   = $request->query('user_id');
        return view('call.video', compact('channel', 'type', 'userId'));
    }

    public function initiateCall(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $caller      = auth()->user();
        $recipientId = $request->recipient_id;
        $channelName = $request->channel_name;
        $callType    = $request->call_type ?? 'audio';

        try {
            broadcast(new IncomingCall($caller, $recipientId, $channelName, $callType));

            // Create call logs
            CallLog::create([
                'user_id' => $caller->id,
                'other_user_id' => $recipientId,
                'call_type' => $callType,
                'status' => 'outgoing_missed',
                'channel_name' => $channelName,
            ]);
            CallLog::create([
                'user_id' => $recipientId,
                'other_user_id' => $caller->id,
                'call_type' => $callType,
                'status' => 'incoming_missed',
                'channel_name' => $channelName,
            ]);

            return response()->json(['success' => true, 'channel_name' => $channelName]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function cancelCall(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'channel_name' => 'required|string',
        ]);

        try {
            broadcast(new CallCancelled(
                (int) $request->recipient_id,
                (string) $request->channel_name
            ));

            // Update call logs
            $logs = CallLog::where('channel_name', $request->channel_name)->get();
            foreach ($logs as $log) {
                if ($log->user_id == auth()->id()) {
                    $log->update(['status' => 'outgoing_cancelled']);
                } else {
                    $log->update(['status' => 'incoming_cancelled']);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('CallCancelled failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateToken(Request $request)
    {
        try {
            $appId           = env('AGORA_APP_ID');
            $appCertificate  = env('AGORA_APP_CERTIFICATE');
            $channelName     = $request->input('channel_name');
            $uid             = $request->input('uid');
            $role            = ($uid == auth()->id()) ? 1 : 2;

            $expirationTimeInSeconds = 3600;
            $privilegeExpiredTs      = now()->timestamp + $expirationTimeInSeconds;

            if (empty($appId) || empty($appCertificate) || empty($channelName) || empty($uid)) {
                throw new \Exception('Missing required parameters for token generation.');
            }

            $token = RtcTokenBuilder::buildTokenWithUid(
                $appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs
            );

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectCall(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
        ]);

        try {
            $logs = CallLog::where('channel_name', $request->channel_name)->get();
            foreach ($logs as $log) {
                if ($log->user_id == auth()->id()) {
                    $log->update(['status' => 'incoming_missed']);
                } else {
                    $log->update(['status' => 'outgoing_missed']);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function pickCall(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
        ]);

        try {
            $logs = CallLog::where('channel_name', $request->channel_name)->get();
            foreach ($logs as $log) {
                if (str_starts_with($log->status, 'outgoing')) {
                    $log->update(['status' => 'outgoing_picked']);
                } elseif (str_starts_with($log->status, 'incoming')) {
                    $log->update(['status' => 'incoming_picked']);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateCallDuration(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
            'duration' => 'required|integer',
        ]);

        try {
            CallLog::where('channel_name', $request->channel_name)
                ->whereIn('status', ['outgoing_picked', 'incoming_picked'])
                ->update(['duration' => $request->duration]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getCallLogs()
    {
        $logs = CallLog::where('user_id', auth()->id())
            ->with('otherUser')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            });

        return response()->json($logs);
    }
}