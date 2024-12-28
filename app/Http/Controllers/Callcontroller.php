<?php

namespace App\Http\Controllers;

use App\Events\IncomingCall;
use App\Models\User;
use Illuminate\Http\Request;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class Callcontroller extends Controller
{
    public function index()
    {
        $users = User::with('userProfile')->whereNot('id', auth()->id())->get();
        return view('call.index', compact('users'));
    }
    public function initiateCall(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);
        $caller = auth()->user();
        $recipientId = $request->recipient_id;
        $channelName = $request->channel_name;
        try {
            broadcast(new IncomingCall($caller, $recipientId, $channelName));

            return response()->json(['success' => true, 'channel_name' => $channelName]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function generateToken(Request $request)
{
    try {
        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = $request->input('channel_name');
        $uid = $request->input('uid');
        if($uid== auth()->id()){
        $role = 1; // 1 for publisher
        }else{
        $role = 2; // 1 for publisher
        }
        $expirationTimeInSeconds = 3600;
        $currentTimestamp = now()->timestamp;
        $privilegeExpiredTs = $currentTimestamp + $expirationTimeInSeconds;

        // Ensure all required arguments are valid
        if (empty($appId) || empty($appCertificate) || empty($channelName) || empty($uid)) {
            throw new \Exception('Missing required parameters for token generation.');
        }

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );

        return response()->json(['token' => $token]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
