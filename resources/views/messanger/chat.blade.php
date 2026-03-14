<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Group Messenger</title>

    {{-- Font Awesome icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    {{-- Tailwind CDN (with plugins) --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#ecfdf5',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        },
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#273449',
                            600: '#334155',
                            500: '#475569',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Pusher + Laravel Echo --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    {{-- Emoji Picker (Picmo) --}}
    <script src="https://cdn.jsdelivr.net/npm/picmo@latest/dist/umd/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@picmo/renderer-fontawesome@latest/dist/umd/index.js"></script>

    <style>
        /* ── Theme Variables ─────────────────────────────────── */
        :root {
            --bg-deep: #0a0a0b;
            --bg-sidebar: #0f1012;
            --bg-card: #16171a;
            --bg-input: #1b1c20;
            --bg-chat-gradient: radial-gradient(ellipse at top left, #0f1f14 0%, #0f172a 60%);
            --brand: #0d9488;
            --brand-hover: #14b8a6;
            --text-main: #f3f4f6;
            --text-dim: #9ca3af;
            --text-bright: #ffffff;
            --border: #27272a;
            --item-hover: #1e293b;
            --bubble-receiver: #273449;
            --bubble-receiver-text: #e2e8f0;
            --scrollbar-track: #1e293b;
            --scrollbar-thumb: #475569;
        }

        .theme-light {
            --bg-deep: #f9fafb;
            --bg-sidebar: #f3f4f6;
            --bg-card: #ffffff;
            --bg-input: #f3f4f6;
            --bg-chat-gradient: radial-gradient(ellipse at top left, #f1f5f9 0%, #cbd5e1 100%);
            --text-main: #1f2937;
            --text-dim: #4b5563;
            --text-bright: #111827;
            --border: #e5e7eb;
            --item-hover: #f1f5f9;
            --bubble-receiver: #f3f4f6;
            --bubble-receiver-text: #1f2937;
            --scrollbar-track: #f1f5f9;
            --scrollbar-thumb: #cbd5e1;
        }

        .theme-blue { --brand: #2563eb; --brand-hover: #3b82f6; }
        .theme-green { --brand: #16a34a; --brand-hover: #22c55e; }

        body { background: var(--bg-deep); color: var(--text-main); transition: background 0.3s, color 0.3s; }
        
        /* Global utility overrides for absolute theme adherence */
        .bg-dark-900 { background: var(--bg-deep) !important; }
        .bg-dark-800 { background: var(--bg-sidebar) !important; }
        .bg-dark-700 { background: var(--bg-card) !important; }
        .bg-dark-600, .bg-dark-500 { background: var(--bg-input) !important; }

        .text-white, .text-gray-100 { color: var(--text-bright) !important; }
        .text-gray-200, .text-gray-300 { color: var(--text-main) !important; }
        .text-gray-400, .text-gray-500 { color: var(--text-dim) !important; }

        .border-dark-600, .border-dark-500 { border-color: var(--border) !important; }
        .bg-brand-600 { background: var(--brand) !important; }
        .text-brand-400 { color: var(--brand) !important; }
        .hover\:bg-brand-500:hover { background: var(--brand-hover) !important; }
        
        .hover\:bg-dark-700:hover { background: var(--item-hover) !important; }
        .hover\:bg-dark-600:hover { background: var(--item-hover) !important; }
        .hover\:text-white:hover { color: var(--text-bright) !important; }

        .ring-brand-600\/40 { --tw-ring-color: rgba(var(--brand-rgb, 13, 148, 136), 0.4); } 

        /* ── Scrollbar ──────────────────────────────────────── */
        .chat-scroll::-webkit-scrollbar          { width: 5px; }
        .chat-scroll::-webkit-scrollbar-track    { background: var(--scrollbar-track); }
        .chat-scroll::-webkit-scrollbar-thumb    { background: var(--scrollbar-thumb); border-radius: 99px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: var(--brand); }
        .chat-scroll { scrollbar-width: thin; scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track); }

        /* Emoji Picker Styling */
        #emojiPickerContainer {
            position: absolute;
            bottom: 80px;
            right: 20px;
            z-index: 1000;
        }
        .picmo__picker {
            border-radius: 1rem !important;
            border: 1px solid var(--border) !important;
            background-color: var(--bg-card) !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5) !important;
        }

        /* ── Message bubbles ────────────────────────────────── */
        #messages { display: flex; flex-direction: column; gap: 6px; }

        .msg-bubble {
            max-width: 68%;
            padding: 8px 14px;
            border-radius: 18px;
            word-break: break-word;
            line-height: 1.45;
            font-size: 0.9rem;
            position: relative;
        }
        .msg-bubble.sender {
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .msg-bubble.receiver {
            background: var(--bubble-receiver);
            color: var(--bubble-receiver-text);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .msg-time  { font-size: 0.65rem; opacity: .65; margin-top: 2px; }
        .msg-name  { font-size: 0.68rem; font-style: italic; color: var(--text-dim); margin-bottom: 2px; }

        /* ── Sidebar group item hover ───────────────────────── */
        .group-item { transition: background .15s ease; border-left: 3px solid transparent; }
        .group-item:hover { background: var(--item-hover) !important; }
        .group-item.active { background: var(--item-hover) !important; border-left: 3px solid var(--brand); }

        /* ── Input glow ─────────────────────────────────────── */
        #messageInput:focus { outline: none; box-shadow: 0 0 0 2px var(--brand); }

        /* ── Badge ──────────────────────────────────────────── */
        .unread-badge {
            background: var(--brand);
            color: #fff !important;
            font-size: 0.65rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 99px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }

        /* ── Drag-over highlight ────────────────────────────── */
        .drag-over { border-color: var(--brand) !important; background: var(--item-hover) !important; opacity: 0.8; }

        /* ── Fade-in animation ──────────────────────────────── */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .msg-bubble { animation: fadeSlideIn .2s ease; position: relative; }
        .msg-status { font-size: 0.65rem; margin-left: 5px; opacity: 0.8; transition: color 0.2s; }
        .msg-status.seen { color: #10b981 !important; } /* Green double tick */

        /* ── Dynamic Logo ───────────────────────────────────────────── */
        .logo-light { display: none !important; }
        .logo-dark { display: block !important; }
        .theme-light .logo-light { display: block !important; height:100px !important; }
        .theme-light .logo-dark { display: none !important; height:100px !important; }
        
        /* ── Mobile Responsiveness ───────────────────────────────────── */
        @media (max-width: 768px) {
            #sidebar { width: 100% !important; min-width: 100% !important; border-right: none; }
            #chatMain { display: none !important; }
            
            #messengerApp.mobile-chat-active #sidebar { display: none !important; }
            #messengerApp.mobile-chat-active #chatMain { display: flex !important; }
        }
    </style>
</head>

{{-- Force whole page dark ──────────────────────────────────────────────── --}}
<body class="dark bg-dark-900 text-gray-100 h-screen flex flex-col overflow-hidden">
    {{-- Audio notification sounds --}}
    <audio id="soundSent" src="https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3" preload="auto"></audio>
    <audio id="soundReceived" src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" preload="auto"></audio>
    <audio id="soundInvite" src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" preload="auto"></audio>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MAIN LAYOUT                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="messengerApp" class="flex flex-1 overflow-hidden" style="height:100vh;">

    {{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
    <aside id="sidebar"
           class="flex flex-col bg-dark-800 border-r border-dark-600 relative"
           style="width:300px; min-width:260px; flex-shrink:0;">

        {{-- Sidebar header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-dark-600">
            <div class="w-10 h-10 flex-shrink-0 rounded-full overflow-hidden ring-2 ring-brand-600/20 shadow-lg">
                <img src="{{ asset(auth()->user()->profile_image ?? 'user-avatar.jpg') }}" class="w-full h-full object-cover">
            </div>
            
            <!-- Dynamic Theme Logos -->
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('dark syncly.png') }}" alt="Syncly" height="150px" width="150px" class="logo-dark object-contain">
                <img src="{{ asset('light syncly.png') }}" alt="Syncly" height="150px" width="150px" class="logo-light object-contain">
            </a>

            <!-- Theme Settings -->
             <div class="flex items-center gap-2">
            <div class="relative">
                <button id="btnThemeToggle" 
                        onclick="event.stopPropagation(); document.getElementById('themeMenu').classList.toggle('hidden')"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-dark-700 border border-dark-600 text-gray-400 hover:text-brand-400 transition shadow-sm">
                    <i class="fa-solid fa-palette text-lg"></i>
                </button>
                <div id="themeMenu" class="absolute right-0 top-full mt-2 w-48 bg-dark-800 border border-dark-600 rounded-xl shadow-2xl hidden z-50 overflow-hidden">
                    <div class="p-2 space-y-1">
                        <p class="px-3 py-1 text-[0.65rem] font-bold text-gray-500 uppercase tracking-widest">Theme Mode</p>
                        <button onclick="setThemeMode('dark')" class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-dark-700 flex items-center gap-2">
                            <i class="fa-solid fa-moon"></i> Dark Mode
                        </button>
                        <button onclick="setThemeMode('light')" class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-dark-700 flex items-center gap-2">
                            <i class="fa-solid fa-sun"></i> Light Mode
                        </button>
                        <hr class="border-dark-600 my-1">
                        <p class="px-3 py-1 text-[0.65rem] font-bold text-gray-500 uppercase tracking-widest">Accent Color</p>
                        <button onclick="setAccentColor('default')" class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-dark-700 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-teal-500"></span> Teal (Default)
                        </button>
                        <button onclick="setAccentColor('blue')" class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-dark-700 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-600"></span> Blue
                        </button>
                        <button onclick="setAccentColor('green')" class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-dark-700 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-600"></span> Green
                        </button>
                    </div>
                </div>
            </div>

            
            </div>
        </div>

        {{-- Search bar --}}
        <div class="px-4 py-3">
            <div class="flex items-center gap-3 bg-dark-600 border border-dark-500 rounded-xl px-4 py-2.5 
                        transition-all duration-200 focus-within:border-brand-500/50 shadow-sm group">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm group-focus-within:text-brand-400"></i>
                <input id="groupSearch" type="text" placeholder="Search for groups..."
                       class="bg-transparent w-full text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-0 border-none p-0 ml-1">
                <button id="clearSearch" class="hidden text-gray-500 hover:text-white transition">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Group list --}}
        <div class="flex-1 overflow-y-auto chat-scroll px-2 py-1 space-y-1" id="groupList">

            {{-- Invitations Section --}}
            <div id="invitationSection" class="{{ count($invitations) > 0 ? '' : 'hidden' }}">
                <div id="invitationItems" class="px-2 py-1 mb-4 space-y-2">
                    <div id="invitationHeader" class="flex items-center justify-between mb-2">
                        <p class="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider">Pending Invitations <span id="invitationCount">({{ count($invitations) }})</span></p>
                        @if(count($invitations) > 2)
                            <button id="btnSeeAllInvites" onclick="showModal('allInvitationsModal')" class="text-[0.6rem] font-bold text-brand-400 hover:text-brand-300 uppercase tracking-tight">See All</button>
                        @endif
                    </div>
                    <div id="invitationList" class="space-y-2">
                        @foreach($invitations->take(2) as $invite)
                            @include('messanger.partials.invitation_item', ['invite' => $invite])
                        @endforeach
                    </div>
                </div>
                <hr class="mx-2 mt-4 mb-2 border-dark-600/50">
            </div>

            <p class="px-2 text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mt-4 mb-2">Groups</p>
            <div id="joinedGroupList" class="space-y-1">
            @php $displayedGroupIds = []; @endphp

            {{-- List of all groups (created and joined) --}}
            @foreach ($groups as $group)
                @php 
                    $displayedGroupIds[] = $group->id; 
                    $memberIds = $group->members->pluck('id')->toArray();
                @endphp
                <div class="group-item flex items-center gap-3 px-3 py-2 rounded-xl cursor-pointer select-none"
                     data-group-id="{{ $group->id }}"
                     data-group-name="{{ $group->name }}"
                     data-group-img="{{ asset('storage/' . $group->profile_image) }}"
                     data-member-ids="{{ implode(',', $memberIds) }}">
                    <div class="relative flex-shrink-0">
                        <img src="{{ asset('storage/' . $group->profile_image) }}" alt="avatar"
                             class="w-11 h-11 rounded-full object-cover ring-2 ring-brand-600/40">
                        {{-- Active dot (controlled by JS presence logic) --}}
                        <span class="active-dot absolute bottom-0 right-0 w-3 h-3 bg-brand-400 rounded-full border-2 border-dark-800 hidden"
                               data-group-id="{{ $group->id }}"></span>
                    </div>
                    <div class="flex-1 min-w-0 pr-1">
                        <div class="flex items-center justify-between mb-0.5">
                            <h3 class="text-sm font-semibold text-gray-100 truncate group-hover:text-white transition">
                                {{ $group->name }}
                            </h3>
                            <span class="unread-badge {{ ($group->unread_count ?? 0) > 0 ? '' : 'hidden' }}"
                                  data-group-id="{{ $group->id }}">
                                {{ $group->unread_count ?? 0 }}
                            </span>
                        </div>
                        <p class="text-[0.7rem] text-gray-400 truncate last-message-preview" data-group-id="{{ $group->id }}">
                            @if($group->latestMessage)
                                @if($group->latestMessage->type === 'file_path')
                                    <i class="fa-regular fa-image text-[0.6rem] mr-0.5 opacity-70"></i><span>Image</span>
                                @else
                                    {{ $group->latestMessage->body }}
                                @endif
                            @else
                                <span class="opacity-70 italic text-[0.65rem]">{{ $group->creator->name ?? 'User' }} created group</span>
                            @endif  
                        </p>
                    </div>
                </div>
            @endforeach
            </div>

            @if (empty($displayedGroupIds))
                <div class="text-center text-gray-500 text-sm py-10">
                    <i class="fa-solid fa-comments text-3xl mb-2 block opacity-30"></i>
                    No groups yet.<br>Click <strong class="text-brand-400">+</strong> to create one.
                </div>
            @endif

        </div>

        {{-- Floating Create Group Button --}}
        <button id="createGroupButton"
                title="New Group"
                class="absolute bottom-6 right-6 w-12 h-12 flex items-center justify-center rounded-full 
                       bg-brand-600 hover:bg-brand-500 text-white transition shadow-2xl z-40
                       transform hover:scale-110 active:scale-95 group/btn">
            <i class="fa-solid fa-plus text-xl"></i>
        </button>
    </aside>

    {{-- ── MAIN CHAT PANEL ──────────────────────────────────────────────── --}}
    <main id="chatMain" class="flex flex-col flex-1 overflow-hidden bg-dark-900">

        {{-- Pusher not-configured warning banner (Simplified PHP check) --}}
        @php
            $pusherKey = config('broadcasting.connections.pusher.key');
            $pusherSec = config('broadcasting.connections.pusher.secret');
            $pusherApp = config('broadcasting.connections.pusher.app_id');
        @endphp
        @if(!$pusherKey || !$pusherSec || !$pusherApp)
        <div id="pusherWarning" class="flex items-center gap-3 px-4 py-2.5 bg-amber-900/80 border-b border-amber-600/50 text-amber-200 text-xs">
            <i class="fa-solid fa-triangle-exclamation text-amber-400 flex-shrink-0"></i>
            <span><strong>Real-time messaging is unavailable</strong> — Pusher is not configured in your .env file. Real-time updates will not work.</span>
            <button onclick="this.parentElement.remove()" class="ml-auto flex-shrink-0 text-amber-400 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        {{-- Chat header --}}
        <div id="chatHeader"
             class="flex items-center justify-between px-5 py-3 bg-dark-800 border-b border-dark-600 shadow-sm overflow-hidden">
            {{-- Header info (85% Clickable area for group details) --}}
            <div id="headerInfoArea" style="width: 85%;" class="flex items-center gap-3 cursor-pointer hover:bg-dark-700 rounded-xl px-2 py-1 -ml-2 transition">
                <button id="mobileBackBtn" class="md:hidden p-1 text-gray-400 hover:text-white transition rounded-full" onclick="event.stopPropagation(); closeMobileChat()">
                    <i class="fa-solid fa-chevron-left text-lg"></i>
                </button>
                <div class="relative flex-shrink-0">
                    <img id="chatGroupprofile"
                         src="{{ asset('default-avatar.jpg') }}"
                         alt="avatar"
                         class="w-10 h-10 rounded-full object-cover ring-2 ring-brand-600/30">
                </div>
                <div class="min-w-0">
                    <p id="chatGroupName" class="font-semibold text-white text-sm truncate">Select a group to start chatting</p>
                    <p id="chatGroupMembers" class="text-[0.68rem] text-gray-400 truncate"></p>
                </div>
            </div>
            <span id="groupIdd" class="hidden"></span>

            {{-- Action buttons (15% Area) --}}
            <div style="width: 15%;" class="flex items-center justify-end gap-3 text-gray-400">
                <button id="startAudioCallBtn" disabled title="Start Audio Call"
                        class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-dark-700 hover:text-white transition disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-phone text-sm"></i>
                </button>
                <button id="startVideoCallBtn" disabled title="Start Video Call"
                        class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-dark-700 hover:text-white transition disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-video text-sm"></i>
                </button>
                <button id="addUserIcon" data-group-id="" disabled title="Add member"
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-brand-600/10 text-brand-400 border border-brand-600/20
                               hover:bg-brand-600 hover:text-white transition shadow-sm
                               disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Pending Invitation Join Bar --}}
        <div id="pendingJoinBar" class="hidden px-6 py-4 bg-amber-900/20 border-b border-amber-600/30 text-center">
            <p class="text-xs text-amber-200 mb-3 font-medium">You have been invited to join this group.</p>
            <div class="flex justify-center gap-3">
                <button id="btnAcceptJoin" class="px-6 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[0.65rem] font-bold rounded-lg transition shadow-lg shadow-emerald-600/20 uppercase tracking-wider">
                    Accept Invitation
                </button>
                <button id="btnRejectJoin" class="px-6 py-1.5 bg-dark-600 hover:bg-dark-500 text-gray-300 text-[0.65rem] font-bold rounded-lg transition border border-dark-500 uppercase tracking-wider">
                    Reject
                </button>
            </div>
        </div>

        {{-- Message area --}}
        <div id="chat-box"
             class="flex-1 overflow-y-auto chat-scroll px-4 py-4 transition-all duration-300"
             style="background: var(--bg-chat-gradient);">
            <div id="messages" class="flex flex-col gap-2 min-h-full justify-end">
                {{-- JS-rendered messages go here --}}
            </div>
        </div>

        {{-- Message input bar (chatControls wrapper) --}}
        <div id="chatControls" class="hidden">
            <div id="messageInputContainer"
                 class="flex items-center gap-2 px-4 py-3 bg-dark-800 border-t border-dark-600"
                 data-group-id="">

            {{-- Attachment button --}}
            <button id="sendFileIcon" data-group-id="" disabled title="Attach file"
                    class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full
                           bg-dark-700 hover:bg-dark-600 text-gray-400 hover:text-brand-400
                           transition disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-solid fa-paperclip"></i>
            </button>

            {{-- Text input --}}
            <div class="flex-1 relative">
                <input type="text" id="messageInput"
                       placeholder="Type a message…" disabled
                       class="w-full bg-dark-700 text-gray-100 placeholder-gray-500
                              rounded-2xl px-4 py-2.5 pr-12 text-sm border border-transparent
                              focus:border-brand-600 focus:ring-0 transition
                              disabled:opacity-40 disabled:cursor-not-allowed">
                
                {{-- Emoji Button --}}
                <button id="emojiButton" disabled title="Pick an emoji"
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center 
                               text-gray-500 hover:text-brand-400 transition disabled:opacity-0 pointer-events-auto">
                    <i class="fa-regular fa-face-smile text-lg"></i>
                </button>
            </div>

            {{-- Emoji Picker Container --}}
            <div id="emojiPickerContainer" class="hidden"></div>

            {{-- Send button --}}
            <button id="sendMessageButton" disabled title="Send message"
                    class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full
                           bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/30
                           transition disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fa-regular fa-paper-plane text-sm"></i>
            </button>
        </div>

        {{-- ── MULTI-USER CALL OVERLAY & GRID ───────────────────────────────────── --}}
        <div id="activeCallOverlay" class="hidden absolute inset-0 bg-dark-900 z-40 flex-col">
            <div class="flex items-center justify-between p-4 bg-dark-800 border-b border-dark-600 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse shadow-lg shadow-emerald-500/50 flex-shrink-0"></div>
                    <img id="activeCallGroupImg" src="" class="hidden w-10 h-10 rounded-full object-cover ring-2 ring-brand-600/30">
                    <h2 id="activeCallGroupName" class="text-white font-bold tracking-wide truncate max-w-[150px] md:max-w-xs">Group Call in Progress</h2>
                </div>
                <div class="flex gap-4">
                    <button id="toggleMicBtn" class="w-11 h-11 rounded-full bg-dark-600 hover:bg-dark-500 text-white transition ring-1 ring-dark-500"><i class="fa-solid fa-microphone"></i></button>
                    <button id="toggleVideoBtn" class="w-11 h-11 rounded-full bg-dark-600 hover:bg-dark-500 text-white transition ring-1 ring-dark-500"><i class="fa-solid fa-video"></i></button>
                    <button id="endCallBtn" class="w-11 h-11 rounded-full bg-red-600 hover:bg-red-500 text-white transition shadow-lg shadow-red-600/40"><i class="fa-solid fa-phone-slash"></i></button>
                </div>
            </div>
            <!-- Video Grid -->
            <div id="videoGrid" class="flex-1 p-4 flex flex-wrap justify-center content-start gap-4 overflow-y-auto bg-dark-950">
                <div id="local-player" class="bg-black rounded-xl overflow-hidden shadow-lg relative border border-dark-600 w-[150px] h-[150px] sm:w-[180px] sm:h-[180px] md:w-[200px] md:h-[200px] flex-shrink-0">
                    <span class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md text-white text-xs font-medium px-2 py-1 rounded-md z-10">You</span>
                </div>
                <!-- Remote players will be injected here -->
            </div>
        </div>

    </main>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}

{{-- ── Incoming Group Call Modal ────────────────────────────────────────── --}}
<div id="incomingGroupCallModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-3xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden text-center p-8 border border-dark-600 relative">
        <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-brand-600/20 to-transparent"></div>
        <div class="relative w-28 h-28 mx-auto mb-5 animate-[pulse_2s_ease-in-out_infinite] ring-4 ring-brand-600/30 rounded-full bg-dark-900 border-4 border-dark-800 shadow-xl overflow-hidden shadow-brand-500/20">
            <img id="incomingCallGroupImg" src="" alt="Group Image" class="w-full h-full object-cover">
        </div>
        <h3 id="incomingCallGroupName" class="text-2xl font-extrabold text-white mb-1 tracking-tight">Group Name</h3>
        <p id="incomingCallDetails" class="text-brand-400 text-sm mb-8 font-medium">Caller Name is initiating a video call...</p>
        <div class="flex justify-center gap-6 mt-4 relative z-10">
            <button id="btnRejectCall" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 bg-red-500/10 group-hover:bg-red-500 text-red-500 group-hover:text-white rounded-full flex items-center justify-center transition-all duration-300 ring-1 ring-red-500/50 group-hover:ring-red-500 shadow-lg group-hover:shadow-red-500/40">
                    <i class="fa-solid fa-phone-slash text-2xl"></i>
                </div>
                <span class="text-xs text-gray-400 font-medium group-hover:text-red-400 transition-colors">Decline</span>
            </button>
            <button id="btnAcceptCall" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center transition-all duration-300 shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-1">
                    <i class="fa-solid fa-phone text-2xl animate-spin-slow"></i>
                </div>
                <span class="text-xs text-emerald-400 font-bold transition-colors">Answer</span>
            </button>
        </div>
    </div>
</div>

{{-- ── Create Group Modal ───────────────────────────────────────────────── --}}
<div id="createGroupModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600">
            <h2 class="font-bold text-white text-base">
                <i class="fa-solid fa-users-gear mr-2 text-brand-400"></i>Create New Group
            </h2>
            <button class="closeGroupModal text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div id="modalContent" class="p-5">
            {{-- Form injected by JS --}}
            <div class="text-center text-gray-500 py-4">
                <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- ── Add User Modal ────────────────────────────────────────────────────── --}}
<div id="addUserModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600">
            <h2 class="font-bold text-white text-base">
                <i class="fa-solid fa-user-plus mr-2 text-brand-400"></i>Add Member
            </h2>
            <button class="closeuserModal text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-4">
            <input type="hidden" id="groupIdInput">
            <div id="userList" class="space-y-2 max-h-72 overflow-y-auto chat-scroll">
                {{-- User rows injected by JS --}}
            </div>
        </div>
    </div>
</div>

{{-- ── Upload File Modal ─────────────────────────────────────────────────── --}}
<div id="uploadFileModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600">
            <h2 class="font-bold text-white text-base">
                <i class="fa-solid fa-file-arrow-up mr-2 text-brand-400"></i>Upload File
            </h2>
            <button class="closeFileModal text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-5 space-y-4">
            
            {{-- AUDIO ELEMENTS FOR CALLS --}}
            <!-- <audio id="soundInvite" src="{{ asset('sounds/invite.mp3') }}" preload="auto"></audio> -->
            <audio id="soundRing" src="{{ asset('sounds/ringtone.mp3') }}" loop preload="auto"></audio>
            <audio id="soundRingback" src="{{ asset('sounds/ringback.mp3') }}" loop preload="auto"></audio>

            <form id="uploadFileForm" enctype="multipart/form-data">
                <div id="dropZone"
                     class="border-2 border-dashed border-dark-500 rounded-xl p-6 text-center
                            transition cursor-pointer hover:border-brand-600"
                     ondragover="event.preventDefault(); this.classList.add('drag-over')"
                     ondragleave="this.classList.remove('drag-over')"
                     ondrop="handleFileDrop(event); this.classList.remove('drag-over')">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-500 mb-2 block"></i>
                    <p class="text-gray-400 text-sm">Drag &amp; drop or
                        <button type="button" id="fileSelectButton"
                                class="text-brand-400 hover:underline font-medium">browse</button>
                    </p>
                    <input type="file" id="fileInput" name="files[]" class="hidden" multiple>
                    <div id="filenames" class="mt-3 space-y-1 text-xs text-gray-400 text-left"></div>
                </div>
            </form>
            <div class="flex justify-end">
                <button id="sendFileButton" disabled
                        class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm
                               font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Send File
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── All Invitations Modal ──────────────────────────────────────────────── --}}
<div id="allInvitationsModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col max-h-[80vh]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600">
            <h2 class="font-bold text-white text-base">
                <i class="fa-solid fa-envelope-open-text mr-2 text-brand-400"></i>All Invitations
            </h2>
            <button onclick="hideModal('allInvitationsModal')" class="text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div id="modalInvitationList" class="p-5 space-y-3 overflow-y-auto chat-scroll">
            @foreach($invitations as $invite)
                @include('messanger.partials.invitation_item', ['invite' => $invite])
            @endforeach
        </div>
    </div>
</div>

{{-- ── Group Detail Modal ─────────────────────────────────────────────────── --}}
<div id="groupDetailModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600 flex-shrink-0">
            <h2 class="font-bold text-white text-base">
                <i class="fa-solid fa-circle-info mr-2 text-brand-400"></i>Group Details
            </h2>
            <button class="closeDetailModal text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div id="groupDetailContent" class="flex-1 overflow-y-auto chat-scroll p-0 space-y-0">
            <div class="text-center py-10 text-gray-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- SCRIPTS                                                                --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}

{{-- Global vars (separate block so they survive Pusher init errors) --}}
<script>
    var AUTH_USER_ID = {{ auth()->id() ?? 0 }};
    var groupIds     = @json($displayedGroupIds);
</script>

{{-- Pusher bootstrap --}}
<script>
    function showPusherWarning() {
        // Warning is now handled via PHP if check in Blade
    }

    window.Pusher = Pusher;
    try {
        window.Echo = new LaravelEcho({
            broadcaster: 'pusher',
            key:      "{{ config('broadcasting.connections.pusher.key') }}",
            cluster:  "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            forceTLS: true,
            encrypted: true,
        });
    } catch(e) { 
        console.warn('Laravel Echo init failed:', e); 
    }

    // Raw Pusher client for legacy file-upload broadcasts
    var pusher = null;
    try {
        if ("{{ config('broadcasting.connections.pusher.key') }}") {
            pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
                cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}"
            });

        } else {
            showPusherWarning();
        }
    } catch(e) { 
        console.warn('Pusher init failed:', e); 
    }
</script>

{{-- ── Core messenger JS ──────────────────────────────────────────────────── --}}
<script>
    let currentGroupId  = null;
    const unreadCounts  = {};

    // ── Helpers ──────────────────────────────────────────────────────────
    function scrollToBottom(smooth = false) {
        const box = document.getElementById('chat-box');
        if (!box) return;
        
        // Small delay to ensure browser has calculated the new height
        setTimeout(() => {
            box.scrollTo({
                top: box.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }, 30);
    }

    function showModal(id)  { document.getElementById(id).classList.replace('hidden','flex'); }
    function hideModal(id)  { document.getElementById(id).classList.replace('flex','hidden'); }

    function createBubble({ from_id, user_name, type, content, time }) {
        const wrap = document.createElement('div');
        wrap.className  = 'flex flex-col';
        const isSender  = (from_id == AUTH_USER_ID);
        wrap.style.alignItems = isSender ? 'flex-end' : 'flex-start';

        const bubble = document.createElement('div');
        bubble.className = `msg-bubble ${isSender ? 'sender' : 'receiver'}`;
        // Track message ID for status updates
        if (arguments[0].id) {
            bubble.dataset.msgId = arguments[0].id;
        }

        if (!isSender) {
            const nameEl = document.createElement('p');
            nameEl.className = 'msg-name';
            nameEl.textContent = user_name || '';
            bubble.appendChild(nameEl);
        }

        if (type === 'body') {
            const txt = document.createElement('p');
            txt.textContent = content;
            bubble.appendChild(txt);
        } else if (type === 'file_path') {
            const img = document.createElement('img');
            img.src = `/storage/${content}`;
            img.alt = 'attachment';
            img.className = 'max-w-xs rounded-xl mt-1';
            img.style.width = '100%';
            
            // Scroll again once the image actually loads and takes up space
            img.onload = () => scrollToBottom(false); 
            bubble.appendChild(img);
        }

        if (time || isSender) {
            const footer = document.createElement('div');
            footer.className = `flex items-center gap-1 mt-1 ${isSender ? 'justify-end' : 'justify-start'}`;
            
            if (time) {
                const timeEl = document.createElement('p');
                timeEl.className = 'msg-time p-0 m-0';
                timeEl.textContent = time;
                footer.appendChild(timeEl);
            }

            if (isSender) {
                const statusEl = document.createElement('span');
                statusEl.className = 'msg-status';
                // Initially single tick (Sent)
                statusEl.innerHTML = '<i class="fa-solid fa-check"></i>';
                footer.appendChild(statusEl);
            }
            
            bubble.appendChild(footer);
        }

        wrap.appendChild(bubble);
        return wrap;
    }

    function appendMessage(data) {
        const container = document.getElementById('messages');
        // Remove "no messages" placeholder if present
        const placeholder = container.querySelector('.no-msg-placeholder');
        if (placeholder) placeholder.remove();

        container.appendChild(createBubble(data));
        scrollToBottom(true); // Smooth scroll for new messages
    }

    function closeMobileChat() {
        document.getElementById('messengerApp').classList.remove('mobile-chat-active');
    }

    function updateBadge(groupId, fromUserId = null) {
        const badge = document.querySelector(`.group-item[data-group-id="${groupId}"] .unread-badge`);
        if (!badge) return;
        
        if (fromUserId != null && fromUserId != AUTH_USER_ID) {
            unreadCounts[groupId] = (unreadCounts[groupId] || 0) + 1;
        } else if (fromUserId === null && groupId === currentGroupId) {
            unreadCounts[groupId] = 0;
        }

        const count = unreadCounts[groupId] || 0;
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    // ── Open / load a group chat ──────────────────────────────────────────
    function openGroupChat(groupId, groupName, groupImg) {
        // Switch to chat view on mobile immediately
        document.getElementById('messengerApp').classList.add('mobile-chat-active');

        if (currentGroupId == groupId) return; // Avoid duplicating/reloading if already open
        currentGroupId = groupId;

        // Update header
        document.getElementById('chatGroupName').textContent    = groupName;
        document.getElementById('chatGroupprofile').src         = groupImg;
        document.getElementById('groupIdd').textContent         = groupId;

        // Ensure clean separation: Clear any legacy header listeners and set only on InfoArea
        const header = document.getElementById("chatHeader");
        header.onclick = null; 
        
        const infoArea = document.getElementById("headerInfoArea");
        infoArea.onclick = (e) => {
            e.stopPropagation();
            loadGroupDetails(groupId);
        };
        
        // Enable inputs & reset
        const msgBtn = document.getElementById('sendMessageButton');
        const msgInp = document.getElementById('messageInput');
        const addBtn = document.getElementById('addUserIcon');
        const fileBtn = document.getElementById('fileSelectButton');
        const audioBtn = document.getElementById('startAudioCallBtn');
        const videoBtn = document.getElementById('startVideoCallBtn');
        const emojiBtn = document.getElementById('emojiButton');

        [msgBtn, msgInp, addBtn, fileBtn, audioBtn, videoBtn, emojiBtn].forEach(el => {
            if (el) el.disabled = false;
        });

        const msgContainer = document.getElementById('messageInputContainer');
        if (msgContainer) msgContainer.dataset.groupId = groupId;
        if (addBtn) addBtn.dataset.groupId = groupId;
        const uploadBtn = document.getElementById('sendFileButton');
        if (uploadBtn) uploadBtn.dataset.groupId = groupId;

        // Sidebar active state
        document.querySelectorAll('.group-item').forEach(el => {
            el.classList.toggle('active', el.dataset.groupId == groupId);
        });

        // Clear unread badge
        updateBadge(groupId, null);

        // Clear messages
        const container = document.getElementById('messages');
        container.innerHTML = '';

        fetch(`{{ route('fetch_messages', '') }}/${groupId}`)
            .then(r => r.json())
            .then(data => {
                // RACE CONDITION GUARD: Only update if we're still on this group
                if (currentGroupId != groupId) return;

                const msgs = data.messages || [];
                const grp  = data.group || {};

                // Update member list
                document.getElementById('chatGroupMembers').textContent = grp.members || '';

                // Handle pending status
                if (data.my_status === 'pending') {
                    document.getElementById('chatControls').classList.add('hidden');
                    document.getElementById('pendingJoinBar').classList.remove('hidden');
                    if(addBtn) addBtn.disabled = true;
                    
                    // Wire up buttons
                    document.getElementById('btnAcceptJoin').onclick = () => respondToInvite(groupId, 'accept');
                    document.getElementById('btnRejectJoin').onclick = () => respondToInvite(groupId, 'reject');
                } else {
                    document.getElementById('chatControls').classList.remove('hidden');
                    document.getElementById('pendingJoinBar').classList.add('hidden');
                    if(addBtn) addBtn.disabled = false;
                }

                if (msgs.length === 0) {
                    const ph = document.createElement('div');
                    ph.className = 'no-msg-placeholder text-center text-gray-500 text-sm py-8';
                    ph.innerHTML = '<i class="fa-regular fa-comment-dots text-2xl block mb-2 opacity-40"></i>No messages yet. Say hello!';
                    container.appendChild(ph);
                } else {
                    msgs.forEach(m => appendMessage(m));
                }
                scrollToBottom();
            })
            .catch(err => console.error('Error loading messages:', err));
    }

    // Attach click to all sidebar items
    document.querySelectorAll('.group-item').forEach(el => {
        el.addEventListener('click', function () {
            openGroupChat(this.dataset.groupId, this.dataset.groupName, this.dataset.groupImg);
        });
    });

    // ── Sidebar search filter ─────────────────────────────────────────────
    const searchInput = document.getElementById('groupSearch');
    const clearBtn    = document.getElementById('clearSearch');

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        clearBtn.classList.toggle('hidden', q.length === 0);
        
        document.querySelectorAll('.group-item').forEach(el => {
            const name = el.dataset.groupName?.toLowerCase() || '';
            el.style.display = name.includes(q) ? 'flex' : 'none';
        });
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });

    // Real-time search "no results" feedback
    searchInput.addEventListener('input', function() {
        const hasResults = Array.from(document.querySelectorAll('.group-item'))
                                .some(el => el.style.display !== 'none');
        const list = document.getElementById('groupList');
        let noResults = list.querySelector('.no-results-msg');
        
        if (!hasResults) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.className = 'no-results-msg text-center text-gray-500 text-xs py-10';
                noResults.innerHTML = '<i class="fa-solid fa-magnifying-glass text-2xl mb-2 opacity-20 block mx-auto"></i> No groups found';
                list.appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    });

    // ── Send text message ─────────────────────────────────────────────────
    function sendMessage() {
        const input   = document.getElementById('messageInput');
        const message = input.value.trim();
        const groupId = document.getElementById('messageInputContainer').dataset.groupId;
        if (!message || !groupId) return;

        fetch(`{{ route('store_messages', '') }}/${groupId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ content: message })
        })
        .then(r => r.json())
        .then(data => {
            input.value = '';
            if (data.success && data.message) {
                appendMessage(data.message);
                updateSidebarPreview(currentGroupId, data.message);
                // Play sent sound
                const sound = document.getElementById('soundSent');
                if (sound) { sound.currentTime = 0; sound.play().catch(() => {}); }
            }
        })
        .catch(err => console.error('Error sending message:', err));
    }

    document.getElementById('sendMessageButton').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
    });

    // ── Real-time Pusher subscriptions ────────────────────────────────────
    function subscribeToGroups() {
        groupIds.forEach(groupId => {
            const channel = pusher.subscribe('group-chat.' + groupId);
            channel.bind('group-chat', e => {
                if (currentGroupId == groupId) {
                    // Update ticks if it's our own message being broadcasted back (Delivered)
                    if (e.from_id == AUTH_USER_ID) {
                        const existing = document.querySelector(`.msg-bubble.sender[data-msg-id="${e.id}"] .msg-status`);
                        if (existing) existing.innerHTML = '<i class="fa-solid fa-check-double"></i>';
                    } else {
                        // Map event data to the format createBubble expects
                        const msgData = {
                            from_id:   e.from_id,
                            user_name: e.user_name,
                            type:      'body',
                            content:   e.message,
                            time:      null // No time provided in basic event yet
                        };
                        appendMessage(msgData);
                        // Mark as seen
                        fetch(`{{ route('seen_messages', '') }}/${groupId}`).catch(() => {});
                    }
                } else {
                    updateBadge(groupId, e.from_id);
                }

                updateSidebarPreview(groupId, { type: 'body', content: e.message });

                // Play received sound if it's from someone else
                if (e.from_id != AUTH_USER_ID) {
                    const sound = document.getElementById('soundReceived');
                    if (sound) { 
                        sound.currentTime = 0; 
                        sound.play().catch(err => console.warn('Sound play blocked:', err)); 
                    }
                }
            });

            // Seen channel
            channel.bind('was-seen', data => {
                if (data.user_id != AUTH_USER_ID && currentGroupId == groupId) {
                    document.querySelectorAll('.msg-bubble.sender .msg-status').forEach(status => {
                        status.classList.add('seen');
                        status.innerHTML = '<i class="fa-solid fa-check-double"></i>';
                    });
                }
            });

            // Incoming Group Call
            channel.bind('incoming-group-call', data => {
                if (data.caller_id != AUTH_USER_ID) {
                    incomingCallChannel = { name: data.channel_name, type: data.media_type, groupId: data.group_id, groupName: data.group_name, groupImg: data.group_image };
                    
                    document.getElementById('incomingCallGroupImg').src = data.group_image || '{{ asset('default-avatar.jpg') }}';
                    document.getElementById('incomingCallGroupName').textContent = data.group_name || 'Group Call';
                    document.getElementById('incomingCallDetails').innerHTML = `<strong class="text-white">${data.caller_name}</strong> is starting a ${data.media_type} call...`;
                    
                    const modal = document.getElementById('incomingGroupCallModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    
                    const sound = document.getElementById('soundRing');
                    if (sound) { sound.currentTime = 0; sound.play().catch(() => {}); }
                }
            });

            // File channel (existing event name preserved)
            const fileChannel = pusher.subscribe('group-files.' + groupId);
            fileChannel.bind('group-files', data => {
                if (currentGroupId == groupId) {
                    appendMessage({
                        from_id:   data.from_id,
                        user_name: '',
                        type:      'file_path',
                        content:   data.file,
                        time:      null,
                    });
                }
                updateSidebarPreview(groupId, { type: 'file_path', content: 'Image' });
            });
        });

        // ── Real-time Invitations ─────────────────────────────────────────
        const inviteChannel = pusher.subscribe('user-invitations.' + AUTH_USER_ID);
        inviteChannel.bind('user-invited', data => {
            // Play invite sound
            const sound = document.getElementById('soundInvite');
            if (sound) { sound.currentTime = 0; sound.play().catch(() => {}); }

            // Show invitation section if hidden
            document.getElementById('invitationSection').classList.remove('hidden');

            // Prepend new invitation HTML to both sidebar and modal
            const list      = document.getElementById('invitationList');
            const modalList = document.getElementById('modalInvitationList');
            
            const createInviteHTML = (data, isModal = false) => {
                const imgPath = data.group_image.startsWith('groups/') 
                    ? `/storage/${data.group_image}` 
                    : data.group_image;

                return `
                    <div class="bg-amber-900/20 border border-amber-600/30 rounded-xl p-3 invitation-item-row" data-invite-id="${data.group_id}">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="${imgPath}" class="w-8 h-8 rounded-full flex-shrink-0 object-cover ring-1 ring-amber-500/30">
                            <p class="text-xs font-semibold text-amber-200 truncate">${data.group_name}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="respondToInvite('${data.group_id}', 'accept')" 
                                    class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[0.65rem] font-bold rounded-lg transition shadow-sm">
                                Accept
                            </button>
                            <button onclick="respondToInvite('${data.group_id}', 'reject')" 
                                    class="flex-1 py-1.5 bg-dark-600 hover:bg-dark-500 text-gray-300 text-[0.65rem] font-bold rounded-lg transition border border-dark-500">
                                Reject
                            </button>
                        </div>
                    </div>
                `;
            };

            // Add to sidebar (limit to 2)
            const sidebarItem = document.createElement('div');
            sidebarItem.innerHTML = createInviteHTML(data);
            list.prepend(sidebarItem.firstElementChild);
            if (list.children.length > 2) {
                list.lastElementChild.remove();
            }

            // Add to modal
            if (modalList) {
                const modalItem = document.createElement('div');
                modalItem.innerHTML = createInviteHTML(data);
                modalList.prepend(modalItem.firstElementChild);
            }

            // Update count
            const countEl = document.getElementById('invitationCount');
            const match = countEl.textContent.match(/\d+/);
            const currentCount = match ? parseInt(match[0]) : 0;
            const newCount = currentCount + 1;
            countEl.textContent = `(${newCount})`;

            // Show "See All" button if count > 2 and not already showing
            if (newCount > 2) {
                if (!document.getElementById('btnSeeAllInvites')) {
                    const header = document.getElementById('invitationHeader');
                    const seeAllBtn = document.createElement('button');
                    seeAllBtn.id = 'btnSeeAllInvites';
                    seeAllBtn.onclick = () => showModal('allInvitationsModal');
                    seeAllBtn.className = "text-[0.6rem] font-bold text-brand-400 hover:text-brand-300 uppercase tracking-tight";
                    seeAllBtn.textContent = "See All";
                    header.appendChild(seeAllBtn);
                }
            }
        });
    }

    // ── Real-time Presence & Active Status ───────────────────────────────
    let onlineUsers = new Set();

    function updateActiveDots() {
        document.querySelectorAll('.active-dot').forEach(dot => {
            const groupItem = dot.closest('.group-item');
            if (!groupItem) return;

            const memberIdsStr = groupItem.dataset.memberIds || '';
            const memberIds    = memberIdsStr.split(',').filter(id => id.length > 0);
            
            // Show dot if any member (except current user) is online
            let isActive = false;
            for (const mId of memberIds) {
                if (mId != AUTH_USER_ID && onlineUsers.has(parseInt(mId))) {
                    isActive = true;
                    break;
                }
            }

            if (isActive) {
                dot.classList.remove('hidden');
            } else {
                dot.classList.add('hidden');
            }
        });
    }

    function subscribeToPresence() {
        if (!window.Echo || typeof window.Echo.join !== 'function') {
            console.warn('Laravel Echo presence channel not available.');
            return;
        }

        window.Echo.join('messenger.presence')
            .here(users => {
                users.forEach(u => onlineUsers.add(u.id));
                updateActiveDots();
            })
            .joining(user => {
                onlineUsers.add(user.id);
                updateActiveDots();
            })
            .leaving(user => {
                onlineUsers.delete(user.id);
                updateActiveDots();
            });
    }

    function respondToInvite(groupId, action) {
        const url = action === 'accept' ? `/messenger/accept-invitation/${groupId}` : `/messenger/reject-invitation/${groupId}`;
        
        fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove from sidebar and modal UI
                document.querySelectorAll(`.invitation-item-row[data-invite-id="${groupId}"]`).forEach(el => el.remove());
                
                // Update count
                const countEl = document.getElementById('invitationCount');
                let count = parseInt(countEl.textContent.match(/\d+/) || 0);
                count = Math.max(0, count - 1);
                countEl.textContent = `(${count})`;

                if (count === 0) {
                    document.getElementById('invitationSection').classList.add('hidden');
                }

                // If accepted, we could reload or fetch the new group dynamically.
                // For a smooth experience, reload is safest to reset all state,
                // but if we want strictly NO refresh:
                if (action === 'accept') {
                    location.reload(); // Still reloading on accept for robustness, but reject is silent.
                }
            } else {
                alert(data.message || 'Error responding to invitation');
            }
        })
        .catch(err => console.error('Error responding to invitation:', err));
    }

    function updateSidebarPreview(groupId, data) {
        const preview = document.querySelector(`.last-message-preview[data-group-id="${groupId}"]`);
        if (!preview) return;

        if (data.type === 'file_path') {
            preview.innerHTML = '<i class="fa-regular fa-image text-[0.6rem] mr-0.5 opacity-70"></i><span>Image</span>';
        } else {
            preview.textContent = data.content;
        }
    }

    // ── Theme System Logic ───────────────────────────────────────────────
    function setThemeMode(mode) {
        if (mode === 'light') document.body.classList.add('theme-light');
        else document.body.classList.remove('theme-light');
        localStorage.setItem('messenger_mode', mode);
    }
    
    function setAccentColor(color) {
        document.body.classList.remove('theme-blue', 'theme-green');
        if (color === 'blue') document.body.classList.add('theme-blue');
        if (color === 'green') document.body.classList.add('theme-green');
        localStorage.setItem('messenger_accent', color);
    }

    // Apply saved themes on load
    (function() {
        const mode = localStorage.getItem('messenger_mode') || 'dark';
        const accent = localStorage.getItem('messenger_accent') || 'default';
        setThemeMode(mode);
        setAccentColor(accent);
    })();

    document.addEventListener('DOMContentLoaded', () => {
        // Close theme menu on outside click
        document.addEventListener('click', () => {
             const menu = document.getElementById('themeMenu');
             if (menu) menu.classList.add('hidden');
        });

        subscribeToGroups();
        subscribeToPresence();
    });
    // ── Group Detail Modal Logic ──────────────────────────────────────────
    function loadGroupDetails(groupId) {
        const content = document.getElementById('groupDetailContent');
        content.innerHTML = `<div class="text-center py-10 text-gray-500"><i class="fa-solid fa-spinner fa-spin text-2xl"></i></div>`;
        showModal('groupDetailModal');

        fetch(`{{ route('group_details', '') }}/${groupId}`)
            .then(r => r.json())
            .then(data => {
                const g = data.group;
                const members = data.members;
                const isAdmin = data.is_admin;

                let html = `
                    <div class="p-6 bg-dark-700/30 text-center border-b border-dark-600">
                        <img src="${g.profile_image}" class="w-24 h-24 rounded-full mx-auto object-cover ring-4 ring-brand-500/20 mb-3">
                        <h3 class="text-xl font-bold text-white">${g.name}</h3>
                        <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Created on ${g.created_at}</p>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4 px-1">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">${members.length} Members</h4>
                        </div>
                        <div class="space-y-4">
                `;

                members.forEach(m => {
                    const isMe = (m.id == AUTH_USER_ID);
                    const isOnline = onlineUsers.has(m.id);
                    
                    html += `
                        <div class="flex items-center justify-between group/m">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full bg-dark-600 flex items-center justify-center text-brand-400 font-bold text-sm border border-dark-500 shadow-inner">
                                        ${m.name.charAt(0).toUpperCase()}
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-dark-800 ${isOnline ? 'bg-brand-400' : 'bg-gray-600'}"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-200 truncate flex items-center gap-2">
                                        ${m.name} 
                                        ${m.role === 'admin' ? '<span class="text-[9px] bg-brand-500/10 text-brand-400 px-1.5 py-0.5 rounded border border-brand-500/20 font-bold uppercase tracking-tighter">Admin</span>' : ''}
                                    </p>
                                    <p class="text-[10px] text-gray-500 truncate mt-0.5">${m.email}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1 opacity-100 md:opacity-0 group-hover/m:opacity-100 transition px-1">
                                ${isAdmin && !isMe ? `
                                    ${m.role !== 'admin' ? `
                                        <button onclick="promoteMember('${g.id}', '${m.id}')" title="Make Admin"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-brand-500/10 hover:text-brand-400 text-gray-500 transition">
                                            <i class="fa-solid fa-shield-halved text-xs"></i>
                                        </button>
                                    ` : ''}
                                    <button onclick="removeMember('${g.id}', '${m.id}')" title="Remove User"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-500/10 hover:text-red-400 text-gray-500 transition">
                                        <i class="fa-solid fa-user-minus text-xs"></i>
                                    </button>
                                ` : ''}
                                ${isMe && g.creator_id != AUTH_USER_ID ? `
                                    <button onclick="removeMember('${g.id}', '${m.id}')" title="Leave Group"
                                            class="text-xs text-red-500 hover:text-red-400 font-semibold px-2 transition">Leave</button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });

                html += `</div></div>`;
                content.innerHTML = html;
            })
            .catch(err => {
                content.innerHTML = `<div class="p-10 text-center text-red-400"><i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i><br>Failed to load details.</div>`;
            });
    }

    function promoteMember(groupId, userId) {
        if (!confirm('Make this user an admin?')) return;
        fetch(`{{ route('promote_admin', '') }}/${groupId}`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadGroupDetails(groupId);
            } else {
                alert(data.message || 'Error promoting user');
            }
        });
    }

    function removeMember(groupId, userId) {
        const isSelf = (userId == AUTH_USER_ID);
        if (!confirm(isSelf ? 'Leave this group?' : 'Remove this member?')) return;
        
        fetch(`{{ route('remove_user_from_group', '') }}/${groupId}`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (isSelf) {
                    location.reload();
                } else {
                    loadGroupDetails(groupId);
                }
            } else {
                alert(data.message || 'Error removing user');
            }
        });
    }

    document.querySelectorAll('.closeDetailModal').forEach(el => {
        el.addEventListener('click', () => hideModal('groupDetailModal'));
    });
</script>

{{-- ── Create Group Modal JS ───────────────────────────────────────────────── --}}
<script>
    // Open modal & load form
    document.getElementById('createGroupButton').addEventListener('click', function () {
        fetch(`{{ route('create_group') }}`)
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
                showModal('createGroupModal');

                // Cancel button (inside injected form)
                const cancelBtn = document.getElementById('cancelCreateGroup');
                if (cancelBtn) cancelBtn.addEventListener('click', () => hideModal('createGroupModal'));

                // Form submit
                document.getElementById('createGroupForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const fd = new FormData(this);
                    fetch(`{{ route('store_group') }}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: fd
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            hideModal('createGroupModal');
                            // Inject new group into sidebar dynamically
                            const g = data.group;
                            const sidebar = document.getElementById('joinedGroupList');
                            const item = document.createElement('div');
                            item.className = 'group-item flex items-center gap-3 px-3 py-2 rounded-xl cursor-pointer select-none';
                            item.dataset.groupId   = g.id;
                            item.dataset.groupName = g.name;
                            item.dataset.groupImg  = g.profile_image;
                            item.innerHTML = `
                                <div class="relative flex-shrink-0">
                                    <img src="${g.profile_image}" alt="avatar"
                                         class="w-11 h-11 rounded-full object-cover ring-2 ring-brand-600/40">
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-brand-400 rounded-full border-2 border-dark-800"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-white truncate">${g.name}</p>
                                    <p class="text-xs text-gray-500">Created by you</p>
                                </div>
                                <span class="unread-badge hidden">0</span>`;
                            item.addEventListener('click', function () {
                                openGroupChat(this.dataset.groupId, this.dataset.groupName, this.dataset.groupImg);
                            });
                            sidebar.prepend(item);

                            // Subscribe to new group's channel
                            groupIds.push(parseInt(g.id));
                            const ch = pusher.subscribe('group-chat.' + g.id);
                            ch.bind('group-chat', ev => {
                                if (currentGroupId == g.id) appendMessage({ from_id: ev.from_id, user_name: ev.user_name, type: 'body', content: ev.message });
                                else updateBadge(g.id, ev.from_id);
                            });
                        } else {
                            alert(data.message || 'Could not create group.');
                        }
                    })
                    .catch(err => console.error('Create group error:', err));
                });
            });
    });

    document.querySelector('.closeGroupModal').addEventListener('click', () => hideModal('createGroupModal'));
    document.getElementById('createGroupModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) hideModal('createGroupModal');
    });
</script>

{{-- ── Add User Modal JS ───────────────────────────────────────────────────── --}}
<script>
    document.getElementById('addUserIcon').addEventListener('click', function () {
        const groupId = this.getAttribute('data-group-id');
        if (!groupId) return;
        document.getElementById('groupIdInput').value = groupId;

        fetch(`{{ route('fetch_users', ':gid') }}`.replace(':gid', groupId))
            .then(r => r.json())
            .then(users => {
                const list = document.getElementById('userList');
                list.innerHTML = '';
                if (users.length === 0) {
                    list.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">All users are already in this group.</p>';
                } else {
                    users.forEach(user => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between bg-dark-700 rounded-xl px-3 py-2';
                        row.innerHTML = `
                            <div>
                                <p class="text-sm font-medium text-white">${user.name}</p>
                                <p class="text-xs text-gray-500">${user.email}</p>
                            </div>
                            <button class="add-user-btn px-3 py-1 text-xs rounded-lg bg-brand-600 hover:bg-brand-500
                                           text-white font-semibold transition"
                                    data-user-id="${user.id}" data-group-id="${groupId}">Add</button>`;
                        list.appendChild(row);
                    });

                    list.querySelectorAll('.add-user-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            addUserToGroup(this.dataset.userId, this.dataset.groupId, this);
                        });
                    });
                }
                showModal('addUserModal');
            })
            .catch(err => console.error('Fetch users error:', err));
    });

    function addUserToGroup(userId, groupId, btn) {
        fetch(`{{ route('add_user_to_group', '') }}/${groupId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.closest('div.flex').remove();
                const list = document.getElementById('userList');
                if (list.querySelectorAll('.add-user-btn').length === 0) {
                    list.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">All users are already in this group.</p>';
                }
            } else {
                alert(data.message || 'Error adding user.');
                hideModal('addUserModal');
            }
        })
        .catch(err => { console.error('Add user error:', err); alert('An error occurred.'); });
    }

    document.querySelector('.closeuserModal').addEventListener('click', () => hideModal('addUserModal'));
    document.getElementById('addUserModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) hideModal('addUserModal');
    });
</script>

{{-- ── File Upload Modal JS ────────────────────────────────────────────────── --}}
<script>
    let selectedFiles = [];
    const fileInput          = document.getElementById('fileInput');
    const filenamesContainer = document.getElementById('filenames');

    document.getElementById('sendFileIcon').addEventListener('click', function () {
        const groupId = document.getElementById('messageInputContainer').dataset.groupId;
        if (groupId) {
            document.getElementById('sendFileButton').dataset.groupId = groupId;
            showModal('uploadFileModal');
        }
    });

    document.querySelectorAll('.closeFileModal').forEach(el => {
        el.addEventListener('click', () => hideModal('uploadFileModal'));
    });
    document.getElementById('uploadFileModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) hideModal('uploadFileModal');
    });

    // ── Emoji Picker Logic ──────────────────────────────────────────────
    let picker = null;
    const emojiButton = document.getElementById('emojiButton');
    const pickerContainer = document.getElementById('emojiPickerContainer');

    function initEmojiPicker() {
        if (picker) return;
        
        picker = picmo.createPicker({
            rootElement: pickerContainer,
            theme: 'dark',
            showSearch: true,
            showPreview: false,
            style: {
                '--picker-background': '#1e293b',
                '--category-button-color-active': '#10b981',
                '--search-background': '#0f172a',
                '--search-placeholder-color': '#475569',
            }
        });

        picker.addEventListener('emoji:select', (selection) => {
            const input = document.getElementById('messageInput');
            const start = input.selectionStart;
            const end = input.selectionEnd;
            const text = input.value;
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);
            input.value = before + selection.emoji + after;
            input.selectionStart = input.selectionEnd = start + selection.emoji.length;
            input.focus();
        });
    }

    emojiButton.addEventListener('click', (e) => {
        e.stopPropagation();
        if (pickerContainer.classList.contains('hidden')) {
            initEmojiPicker();
            pickerContainer.classList.remove('hidden');
        } else {
            pickerContainer.classList.add('hidden');
        }
    });

    // Close picker when clicking outside
    document.addEventListener('click', (e) => {
        if (pickerContainer && !pickerContainer.contains(e.target) && e.target !== emojiButton) {
            pickerContainer.classList.add('hidden');
        }
    });

    document.getElementById('fileSelectButton').addEventListener('click', () => fileInput.click());
    document.getElementById('dropZone').addEventListener('click', e => {
        if (e.target.id !== 'fileSelectButton') fileInput.click();
    });

    fileInput.addEventListener('change', e => {
        selectedFiles = [...selectedFiles, ...Array.from(e.target.files)];
        renderFilenames();
    });

    function handleFileDrop(e) {
        e.preventDefault();
        selectedFiles = [...selectedFiles, ...Array.from(e.dataTransfer.files)];
        renderFilenames();
    }

    function renderFilenames() {
        filenamesContainer.innerHTML = '';
        selectedFiles.forEach(f => {
            const el = document.createElement('div');
            el.className = 'flex items-center gap-1';
            el.innerHTML = `<i class="fa-solid fa-file text-brand-400"></i> ${f.name}`;
            filenamesContainer.appendChild(el);
        });
        document.getElementById('sendFileButton').disabled = selectedFiles.length === 0;
    }

    document.getElementById('sendFileButton').addEventListener('click', () => {
        const groupId = document.getElementById('sendFileButton').dataset.groupId;
        if (!groupId || selectedFiles.length === 0) return;

        const formData = new FormData();
        selectedFiles.forEach(f => formData.append('files[]', f));

        fetch(`{{ route('send_file', '') }}/${groupId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            selectedFiles = [];
            renderFilenames();
            hideModal('uploadFileModal');
        })
        .catch(err => console.error('File upload error:', err));
    });

    // ── AGORA GROUP CALL LOGIC ───────────────────────────────────────────
    const AGORA_APP_ID = "{{ env('AGORA_APP_ID') }}";
    const agoraClient = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
    let localTracks = { video: null, audio: null };
    let remoteUsers = {};
    let isCallActive = false;
    let activeCallChannel = null;

    const overlay = document.getElementById('activeCallOverlay');
    const incomingModal = document.getElementById('incomingGroupCallModal');
    let incomingCallChannel = null;

    // Buttons
    const btnStartAudio = document.getElementById('startAudioCallBtn');
    const btnStartVideo = document.getElementById('startVideoCallBtn');
    const btnToggleMic = document.getElementById('toggleMicBtn');
    const btnToggleVideo = document.getElementById('toggleVideoBtn');
    const btnEndCall = document.getElementById('endCallBtn');
    const btnAcceptCall = document.getElementById('acceptGroupCallBtn');
    const btnRejectCall = document.getElementById('rejectGroupCallBtn');

    let isAudioMuted = false;
    let isVideoMuted = false;

    async function initGroupCall(channelName, mediaType, isInitiator = false, groupIdOverride = null, overrideGroupName = null, overrideGroupImg = null) {
        if (isCallActive) return;
        isCallActive = true;
        activeCallChannel = channelName;
        
        let headerTitle = overrideGroupName || document.getElementById('chatGroupName').textContent;
        let headerImg = overrideGroupImg || document.getElementById('chatGroupprofile').src;

        document.getElementById('activeCallGroupName').textContent = headerTitle;
        const imgEl = document.getElementById('activeCallGroupImg');
        imgEl.src = headerImg;
        imgEl.classList.remove('hidden');

        // Ensure UI displays
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        try {
            // Fetch Token using explicit Number for UID to avoid type mismatches
            const numericUid = Number("{{ auth()->id() }}");
            const tokenResponse = await fetch("{{ route('call.token') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ channel_name: channelName, uid: numericUid })
            });
            const tokenData = await tokenResponse.json();
            
            await agoraClient.join(AGORA_APP_ID, channelName, tokenData.token, numericUid);

            // Init Media Tracks
            if (mediaType === 'video' || mediaType === 'audio') {
                localTracks.audio = await AgoraRTC.createMicrophoneAudioTrack();
            }
            if (mediaType === 'video') {
                localTracks.video = await AgoraRTC.createCameraVideoTrack();
                localTracks.video.play("local-player");
            } else {
                // If audio only, put placeholder
                document.getElementById("local-player").innerHTML = '<div class="w-full h-full flex flex-col items-center justify-center bg-dark-800"><i class="fa-solid fa-phone text-4xl text-brand-500 mb-2"></i><span class="text-gray-400 font-medium">Audio Call</span><span class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md text-white text-xs font-medium px-2 py-1 rounded-md z-10">You</span></div>';
            }

            const tracksToPublish = [];
            if (localTracks.audio) tracksToPublish.push(localTracks.audio);
            if (localTracks.video) tracksToPublish.push(localTracks.video);
            
            if (tracksToPublish.length > 0) {
                await agoraClient.publish(tracksToPublish);
            }

            // Broadcast to other members if initiating
            if (isInitiator) {
                const targetGroupId = groupIdOverride || currentGroupId;
                fetch(`{{ url('messenger/call') }}/${targetGroupId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ channel_name: channelName, media_type: mediaType })
                }).catch(err => console.error(err));
            }

        } catch (error) {
            console.error("Agora Call Error:", error);
            leaveGroupCall();
        }
    }

    async function leaveGroupCall() {
        for (const trackName in localTracks) {
            var track = localTracks[trackName];
            if (track) {
                track.stop();
                track.close();
                localTracks[trackName] = null;
            }
        }
        await agoraClient.leave();
        
        // Reset local player HTML
        document.getElementById('videoGrid').innerHTML = '<div id="local-player" class="bg-black rounded-xl overflow-hidden shadow-lg relative border border-dark-600 w-[150px] h-[150px] sm:w-[180px] sm:h-[180px] md:w-[200px] md:h-[200px] flex-shrink-0"><span class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md text-white text-xs font-medium px-2 py-1 rounded-md z-10">You</span></div>';
        
        remoteUsers = {};
        isCallActive = false;
        activeCallChannel = null;
        
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
        // Stop answering ring if leaving
        incomingModal.classList.replace('flex', 'hidden');
        
        const ringback = document.getElementById('soundRingback');
        if (ringback) { ringback.pause(); ringback.currentTime = 0; }
        const ringtone = document.getElementById('soundRing');
        if (ringtone) { ringtone.pause(); ringtone.currentTime = 0; }
    }

    async function fetchRemoteUserName(uid) {
        try {
            const res = await fetch(`{{ url('messenger/user-info') }}/${uid}`);
            const data = await res.json();
            return data.name || `User ${uid}`;
        } catch {
            return `User ${uid}`;
        }
    }

    agoraClient.on("user-published", async (user, mediaType) => {
        await agoraClient.subscribe(user, mediaType);
        
        let userName = await fetchRemoteUserName(user.uid);

        // Stop ringback tone when a user finally joins
        const ringback = document.getElementById('soundRingback');
        if (ringback) { ringback.pause(); ringback.currentTime = 0; }

        if (mediaType === "video") {
            const playerContainer = document.createElement("div");
            playerContainer.id = `remote-player-${user.uid}`;
            playerContainer.className = "bg-black rounded-xl overflow-hidden shadow-lg relative border border-dark-600 flex items-center justify-center w-[150px] h-[150px] sm:w-[180px] sm:h-[180px] md:w-[200px] md:h-[200px] flex-shrink-0";
            playerContainer.innerHTML = `<span class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md text-white text-xs font-medium px-2 py-1 rounded-md z-10">${userName}</span>`;
            document.getElementById("videoGrid").appendChild(playerContainer);
            
            user.videoTrack.play(playerContainer.id);
        }
        if (mediaType === "audio") {
            user.audioTrack.play();
            // Create a placeholder slot if they are audio only and don't exist yet
            if (!document.getElementById(`remote-player-${user.uid}`)) {
                 const playerContainer = document.createElement("div");
                 playerContainer.id = `remote-player-${user.uid}`;
                 playerContainer.className = "bg-dark-800 rounded-xl overflow-hidden shadow-lg relative border border-dark-600 flex flex-col items-center justify-center w-[150px] h-[150px] sm:w-[180px] sm:h-[180px] md:w-[200px] md:h-[200px] flex-shrink-0";
                 playerContainer.innerHTML = `<i class="fa-solid fa-phone text-4xl text-brand-500 mb-2"></i><span class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md text-white text-xs font-medium px-2 py-1 rounded-md z-10">${userName}</span>`;
                 document.getElementById("videoGrid").appendChild(playerContainer);
            }
        }
        remoteUsers[user.uid] = user;
    });

    agoraClient.on("user-unpublished", (user) => {
        const playerContainer = document.getElementById(`remote-player-${user.uid}`);
        if (playerContainer) playerContainer.remove();
        delete remoteUsers[user.uid];
    });

    btnStartAudio.addEventListener('click', async () => {
        if (!currentGroupId) return;
        const channelName = `group_call_${currentGroupId}_${Date.now()}`;
        const res = await fetch(`{{ url('/messenger/call') }}/${currentGroupId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ media_type: 'audio', channel_name: channelName })
        });
        if (res.ok) {
            initGroupCall(channelName, 'audio', true);
            const ringback = document.getElementById('soundRingback');
            if (ringback) { ringback.currentTime = 0; ringback.play().catch(()=>{}); }
        }
    });

    btnStartVideo.addEventListener('click', async () => {
        if (!currentGroupId) return;
        const channelName = `group_call_${currentGroupId}_${Date.now()}`;
        const res = await fetch(`{{ url('/messenger/call') }}/${currentGroupId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ media_type: 'video', channel_name: channelName })
        });
        if (res.ok) {
            initGroupCall(channelName, 'video', true);
            const ringback = document.getElementById('soundRingback');
            if (ringback) { ringback.currentTime = 0; ringback.play().catch(()=>{}); }
        }
    });

    btnEndCall.addEventListener('click', leaveGroupCall);

    btnToggleMic.addEventListener('click', () => {
        if (!localTracks.audio) return;
        isAudioMuted = !isAudioMuted;
        localTracks.audio.setMuted(isAudioMuted);
        btnToggleMic.innerHTML = isAudioMuted ? '<i class="fa-solid fa-microphone-slash text-red-500"></i>' : '<i class="fa-solid fa-microphone"></i>';
    });

    btnToggleVideo.addEventListener('click', () => {
        if (!localTracks.video) return;
        isVideoMuted = !isVideoMuted;
        localTracks.video.setMuted(isVideoMuted);
        btnToggleVideo.innerHTML = isVideoMuted ? '<i class="fa-solid fa-video-slash text-red-500"></i>' : '<i class="fa-solid fa-video"></i>';
    });
    
    btnAcceptCall.addEventListener('click', () => {
        incomingModal.classList.replace('flex', 'hidden');
        const ringtone = document.getElementById('soundRing');
        if (ringtone) { ringtone.pause(); ringtone.currentTime = 0; }
        
        if (incomingCallChannel) {
            // Automatically switch the background chat to the group
            openGroupChat(
                incomingCallChannel.groupId, 
                incomingCallChannel.groupName, 
                incomingCallChannel.groupImg
            );

            initGroupCall(
                incomingCallChannel.name, 
                incomingCallChannel.type, 
                false, 
                incomingCallChannel.groupId, 
                incomingCallChannel.groupName, 
                incomingCallChannel.groupImg
            );
        }
    });
    
    btnRejectCall.addEventListener('click', () => {
        incomingModal.classList.replace('flex', 'hidden');
        const ringtone = document.getElementById('soundRing');
        if (ringtone) { ringtone.pause(); ringtone.currentTime = 0; }
        incomingCallChannel = null;
    });

</script>

</body>
</html>