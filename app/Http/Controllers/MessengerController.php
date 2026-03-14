<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Events\UserInvitedToGroup;
use App\Models\Group;
use App\Models\GroupFile;
use App\Models\GroupMessages;
use App\Models\GroupUsers;
use App\Models\MessageSeen;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;

class MessengerController extends Controller
{
    // -----------------------------------------------------------------------
    // Helper: boot a Pusher client from .env values
    // -----------------------------------------------------------------------
    private function pusher()
    {
        return new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster'   => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS'    => true,
            ]
        );
    }

    // -----------------------------------------------------------------------
    // GET /messenger/create-group
    // Returns the HTML partial for the create-group modal body.
    // -----------------------------------------------------------------------
    public function createGroupForm()
    {
        $html = '
        <form id="createGroupForm" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Group Name</label>
                <input type="text" id="groupNameInput" name="name"
                       class="w-full bg-dark-600 border border-dark-600 text-white rounded-lg px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-brand-600 placeholder-gray-500"
                       placeholder="Enter group name" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Group Image</label>
                <input type="file" id="groupImageInput" name="profile_image" accept="image/*"
                       class="w-full text-gray-300 bg-dark-600 border border-dark-600 rounded-lg px-3 py-2
                              file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                              file:bg-brand-600 file:text-white file:cursor-pointer">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="cancelCreateGroup"
                        class="px-4 py-2 rounded-lg bg-dark-500 hover:bg-dark-600 text-gray-100 text-sm transition border border-dark-600">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm transition font-semibold">
                    Create Group
                </button>
            </div>
        </form>';

        return response($html);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/store-group
    // Creates a new group and adds the creator as a participant.
    // -----------------------------------------------------------------------
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:4096',
        ]);

        $imagePath = 'groups/default.png'; // fallback
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('groups', 'public');
        }

        $group = Group::create([
            'name'          => $request->input('name'),
            'profile_image' => $imagePath,
            'creatorId'     => Auth::id(),
            'limit'         => 100,
        ]);

        // Add creator as a participant with admin role
        GroupUsers::create([
            'group_id' => $group->id,
            'user_id'  => Auth::id(),
            'role'     => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'            => $group->id,
                'name'          => $group->name,
                'profile_image' => asset('storage/' . $group->profile_image),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /messenger/messages/{groupId}
    // Returns JSON array of messages (text + files) for a group.
    // -----------------------------------------------------------------------
    public function fetchMessages(int $groupId)
    {
        // Mark messages as seen while we're here
        $this->markSeenInternal($groupId);

        $messages = GroupMessages::with('user')
            ->where('group_id', $groupId)
            ->get();

        $files = GroupFile::with('user')
            ->where('group_id', $groupId)
            ->get();

        // Merge models first, then sort by timestamp, then map to array
        $all = $messages->concat($files)->sortBy('created_at')->values()->map(function($item) {
            $isMsg = ($item instanceof GroupMessages);
            return [
                'id'        => $item->id,
                'from_id'   => $isMsg ? $item->from_id : $item->user_id,
                'user_name' => optional($item->user)->name ?? 'Unknown',
                'type'      => $isMsg ? 'body' : 'file_path',
                'content'   => $isMsg ? $item->body : $item->file_path,
                'time'      => $item->created_at ? $item->created_at->format('h:i A') : '',
                'created_at'=> $item->created_at // keeping for any future sub-sorts if needed
            ];
        });

        $group   = Group::with(['members' => function($q) {
            $q->select('users.id', 'users.name');
        }])->find($groupId);

        $authId  = Auth::id();
        $members = $group->members;
        
        // WhatsApp style: "You, Member1, Member2 (+ X others)"
        $names = [];
        $hasYou = false;
        foreach ($members as $m) {
            if ($m->id == $authId) {
                $hasYou = true;
            } else {
                $names[] = $m->name;
            }
        }

        $displayNames = $hasYou ? ['You'] : [];
        $displayNames = array_merge($displayNames, array_slice($names, 0, 3 - count($displayNames)));
        $memberString = implode(', ', $displayNames);
        
        if (count($members) > 3) {
            $memberString .= '...';
        }

        $currentUserPivot = GroupUsers::where('group_id', $groupId)
            ->where('user_id', $authId)
            ->first();

        return response()->json([
            'messages' => $all,
            'group'    => [
                'name'    => $group->name,
                'members' => $memberString,
            ],
            'my_status' => ($currentUserPivot ? $currentUserPivot->status : null),
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /messenger/group-details/{groupId}
    // Returns full group details and member list with roles.
    // -----------------------------------------------------------------------
    public function groupDetails(int $groupId)
    {
        $group = Group::with(['members' => function($q) {
            $q->select('users.id', 'users.name', 'users.email')
              ->withPivot('role');
        }])->findOrFail($groupId);

        $currentUserPivot = GroupUsers::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'group' => [
                'id'            => $group->id,
                'name'          => $group->name,
                'profile_image' => asset('storage/' . $group->profile_image),
                'creator_id'    => $group->creatorId,
                'created_at'    => $group->created_at->format('M d, Y'),
            ],
            'members' => $group->members->map(function($m) {
                return [
                    'id'    => $m->id,
                    'name'  => $m->id == Auth::id() ? 'You' : $m->name,
                    'email' => $m->email,
                    'role'  => $m->pivot->role,
                ];
            }),
            'is_admin' => ($currentUserPivot && $currentUserPivot->role === 'admin'),
            'my_status' => ($currentUserPivot ? $currentUserPivot->status : null),
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/messages/{groupId}
    // Stores a text message and broadcasts via Pusher.
    // -----------------------------------------------------------------------
    public function storeMessage(Request $request, int $groupId)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        $message = GroupMessages::create([
            'group_id' => $groupId,
            'from_id'  => $user->id,
            'to_id'    => $groupId, // group context; to_id = group_id
            'body'     => $request->input('content'),
            'seen'     => false,
        ]);

        // Broadcast to all listeners on this channel
        try {
            event(new GroupMessageSent(
                $groupId,
                $user->id,
                $message->body,
                $user->name,
                $message->id
            ));
        } catch (\Exception $e) {
            // Broadcasting failure should not block the response
            \Log::warning('Pusher broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'   => true,
            'message'   => [
                'id'        => $message->id,
                'from_id'   => $user->id,
                'user_name' => $user->name,
                'type'      => 'body',
                'content'   => $message->body,
                'time'      => $message->created_at->format('h:i A'),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/file/{groupId}
    // Uploads file(s) and broadcasts via Pusher.
    // -----------------------------------------------------------------------
    public function sendFile(Request $request, int $groupId)
    {
        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'file|max:20480',
        ]);

        $user      = Auth::user();
        $uploaded  = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('group-files', 'public');

            GroupFile::create([
                'group_id'  => $groupId,
                'user_id'   => $user->id,
                'file_path' => $path,
            ]);

            // Broadcast file event
            try {
                $this->pusher()->trigger(
                    'group-files.' . $groupId,
                    'group-files',
                    ['file' => $path, 'from_id' => $user->id]
                );
            } catch (\Exception $e) {
                \Log::warning('Pusher file broadcast failed: ' . $e->getMessage());
            }

            $uploaded[] = $path;
        }

        return response()->json(['success' => true, 'files' => $uploaded]);
    }

    // -----------------------------------------------------------------------
    // GET /messenger/users/{groupId}
    // Returns users NOT already in the group (for the add-user modal).
    // -----------------------------------------------------------------------
    public function fetchUsers(int $groupId)
    {
        $existingUserIds = GroupUsers::where('group_id', $groupId)
            ->pluck('user_id')
            ->toArray();

        $users = User::whereNotIn('id', $existingUserIds)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($users);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/add-user/{groupId}
    // Adds a user to the group (idempotent – won't duplicate).
    // -----------------------------------------------------------------------
    public function addUserToGroup(Request $request, int $groupId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $already = GroupUsers::where('group_id', $groupId)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($already) {
            return response()->json(['success' => false, 'message' => 'User is already in this group.']);
        }

        // Check if current user is admin
        $isAdmin = GroupUsers::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->where('role', 'admin')
            ->exists();
        
        if (!$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only admins can add members.'], 403);
        }

        $pivot = GroupUsers::create([
            'group_id' => $groupId,
            'user_id'  => $request->user_id,
            'role'     => 'member',
            'status'   => 'pending',
        ]);

        // Broadcast invitation
        $group = Group::find($groupId);
        broadcast(new UserInvitedToGroup($request->user_id, $groupId, $group->name, $group->profile_image));

        return response()->json(['success' => true, 'message' => 'Invitation sent successfully.']);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/accept-invitation/{groupId}
    // -----------------------------------------------------------------------
    public function acceptInvitation($groupId)
    {
        GroupUsers::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/reject-invitation/{groupId}
    // -----------------------------------------------------------------------
    public function rejectInvitation($groupId)
    {
        GroupUsers::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->delete();

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/promote-admin/{groupId}
    // Promotes a member to admin. Only existing admins can do this.
    // -----------------------------------------------------------------------
    public function promoteToAdmin(Request $request, int $groupId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $currentAdmin = GroupUsers::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->where('role', 'admin')
            ->first();

        if (!$currentAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only admins can promote members.'], 403);
        }

        GroupUsers::where('group_id', $groupId)
            ->where('user_id', $request->user_id)
            ->update(['role' => 'admin']);

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/remove-user/{groupId}
    // Removes a user from the group. Only admins can remove others.
    // -----------------------------------------------------------------------
    public function removeUserFromGroup(Request $request, int $groupId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $userToRemove = $request->user_id;
        $authId       = Auth::id();

        // If not removing self, check if auth is admin
        if ($userToRemove != $authId) {
            $isAdmin = GroupUsers::where('group_id', $groupId)
                ->where('user_id', $authId)
                ->where('role', 'admin')
                ->exists();
            
            if (!$isAdmin) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only admins can remove members.'], 403);
            }
        }

        GroupUsers::where('group_id', $groupId)
            ->where('user_id', $userToRemove)
            ->delete();

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // GET /messenger/seen/{groupId}
    // Marks all group messages as seen for the current user.
    // -----------------------------------------------------------------------
    public function markSeen(int $groupId)
    {
        $this->markSeenInternal($groupId);

        // Broadcast "seen" event so sender can update ticks
        try {
            $this->pusher()->trigger("group-chat.$groupId", 'was-seen', [
                'user_id' => Auth::id(),
            ]);
        } catch(\Exception $e) {}

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // Internal: mark seen without returning a response
    // -----------------------------------------------------------------------
    private function markSeenInternal(int $groupId)
    {
        GroupMessages::where('group_id', $groupId)
            ->where('seen', false)
            ->where('from_id', '!=', Auth::id())
            ->update(['seen' => true]);
    }

    // -----------------------------------------------------------------------
    // GET /messenger/user-info/{id}
    // Returns basic info for a remote user joining a call.
    // -----------------------------------------------------------------------
    public function userInfo(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['name' => 'Unknown User']);
        }

        // Removed userProfile relation check as it doesn't exist on User model
        $avatar = asset('default-avatar.jpg');

        return response()->json([
            'name' => $user->name,
            'avatar' => $avatar
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /messenger/call/{groupId}
    // Initiates an audio/video call for the group and broadcasts it.
    // -----------------------------------------------------------------------
    public function initiateCall(Request $request, int $groupId)
    {
        $request->validate([
            'media_type' => 'required|in:audio,video',
            'channel_name' => 'required|string',
        ]);

        $user = Auth::user();
        $group = \App\Models\Group::find($groupId);
        
        // Broadcast the call to the group
        try {
            event(new \App\Events\GroupCallIncoming(
                $groupId, 
                $request->channel_name, 
                $user->name, 
                $user->id, 
                $request->media_type,
                $group ? $group->name : 'Group',
                $group ? asset('storage/' . $group->profile_image) : asset('default-avatar.jpg')
            ));
        } catch (\Exception $e) {
            \Log::warning('Group call broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'channel_name' => $request->channel_name,
        ]);
    }
}
