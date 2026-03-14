<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\StreamingController;
use App\Http\Controllers\Callcontroller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Events\TestPusherEvent;
use App\Models\Group;
use App\Models\GroupUsers;
use App\Models\GroupMessage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/test-pusher', function () {
    broadcast(new TestPusherEvent('Hello, Pusher!'));
    return 'Event has been sent!';
});
Route::get('messanger', function () {
    $user = auth()->user();
    
    // Groups where user is a participant (accepted)
    $groups = $user->groups()
        ->wherePivot('status', 'accepted')
        ->with(['messages' => function($q) {
            $q->latest()->limit(1);
        }])
        ->orderBy('id', 'desc')
        ->get();

    // Pending invitations
    $invitations = $user->groups()->wherePivot('status', 'pending')->get();

    // Map the latest message and unread count to each group
    foreach ($groups as $group) {
        $group->latestMessage = \App\Models\GroupMessages::where('group_id', $group->id)
            ->latest()
            ->first();
        
        $group->unread_count = \App\Models\GroupMessages::where('group_id', $group->id)
            ->where('from_id', '!=', $user->id)
            ->where('seen', false)
            ->count();
    }

    $displayedGroupIds = $groups->pluck('id')->toArray();

    return view('messanger.chat', compact('groups', 'invitations', 'displayedGroupIds'));
})->middleware(['auth'])->name('messanger');

// ── Messenger / Group Chat AJAX Routes ─────────────────────────────────────
Route::prefix('messenger')->group(function () {
    Route::get('create-group',          [MessengerController::class, 'createGroupForm'])->name('create_group');
    Route::post('store-group',           [MessengerController::class, 'storeGroup'])->name('store_group');
    Route::get('messages/{groupId}',     [MessengerController::class, 'fetchMessages'])->name('fetch_messages');
    Route::post('messages/{groupId}',    [MessengerController::class, 'storeMessage'])->name('store_messages');
    Route::post('file/{groupId}',        [MessengerController::class, 'sendFile'])->name('send_file');
    Route::get('users/{groupId}',        [MessengerController::class, 'fetchUsers'])->name('fetch_users');
    Route::post('add-user/{groupId}',    [MessengerController::class, 'addUserToGroup'])->name('add_user_to_group');
    Route::post('remove-user/{groupId}', [MessengerController::class, 'removeUserFromGroup'])->name('remove_user_from_group');
    Route::get('seen/{groupId}',         [MessengerController::class, 'markSeen'])->name('seen_messages');
    Route::get('group-details/{groupId}', [MessengerController::class, 'groupDetails'])->name('group_details');
    Route::post('promote-admin/{groupId}', [MessengerController::class, 'promoteToAdmin'])->name('promote_admin');
    Route::post('accept-invitation/{groupId}', [MessengerController::class, 'acceptInvitation'])->name('accept_invitation');
    Route::post('reject-invitation/{groupId}', [MessengerController::class, 'rejectInvitation'])->name('reject_invitation');
    Route::post('call/{groupId}',        [MessengerController::class, 'initiateCall'])->name('initiate_group_call');
    Route::get('user-info/{id}',         [MessengerController::class, 'userInfo'])->name('user_info');
});
// ───────────────────────────────────────────────────────────────────────────


// Route::middleware('auth')->group(function () {  \\ can apply if authentication is integrated in project
    Route::get('/stream/noposter', function () {
        // dd(auth()->id());
        return response()->file(public_path('frontend/girl/img16.png'));
    });
    Route::get('/stream/index', [StreamingController::class, 'index'])->name('stream.index');
    Route::middleware('auth')->group(function () {
        Route::get('/streams', [StreamingController::class, 'listStreams'])->name('stream.list');
        Route::get('/stream/index', [StreamingController::class, 'index'])->name('stream.index');
        Route::get('/stream/active', [StreamingController::class, 'getActiveStream'])->name('stream.active');
        Route::get('/stream/watch-token/{streamKey}', [StreamingController::class, 'getWatchToken']);
        Route::get('/stream/{id}', [StreamingController::class, 'watchStream'])->name('stream.watch');
        Route::post('/stream/start', [StreamingController::class, 'startStream'])->name('stream.start');
        Route::post('/stream/end', [StreamingController::class, 'endStream'])->name('stream.end');
        Route::post('/stream/stop', [StreamingController::class, 'stop'])->name('stream.stop');
        Route::post('/stream/viewer-joined',    [StreamingController::class, 'viewerJoined']);
        Route::post('/stream/viewer-left',      [StreamingController::class, 'viewerLeft']);
        Route::post('/stream/chat',             [StreamingController::class, 'sendChat']);
        Route::get('/stream/viewers/{key}',     [StreamingController::class, 'getViewers']);
        Route::get('/stream/watch-token/{key}', [StreamingController::class, 'watchToken']);
    });

    //calling 
    Route::get('/privatecall', [Callcontroller::class, 'index'])->name('privatecall');
    Route::get('/call/video', [Callcontroller::class, 'videoCall'])->name('call.video');
    Route::post('/call', [Callcontroller::class, 'initiateCall'])->name('call.initiate');
    Route::post('/call/token', [Callcontroller::class, 'generateToken'])->name('call.token');
    Route::post('/call/cancel', [Callcontroller::class, 'cancelCall'])->name('call.cancel');
    Route::post('/call/reject', [Callcontroller::class, 'rejectCall'])->name('call.reject');
    Route::post('/call/pick', [Callcontroller::class, 'pickCall'])->name('call.pick');
    Route::post('/call/duration', [Callcontroller::class, 'updateCallDuration'])->name('call.duration');
    Route::get('/call/logs', [Callcontroller::class, 'getCallLogs'])->name('call.logs');
// });
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
