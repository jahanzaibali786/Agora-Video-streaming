<div class="bg-amber-900/20 border border-amber-600/30 rounded-xl p-3 invitation-item-row" data-invite-id="{{ $invite->id }}">
    <div class="flex items-center gap-3 mb-2">
        <img src="{{ Str::startsWith($invite->profile_image, 'groups/') ? asset('storage/'.$invite->profile_image) : asset($invite->profile_image) }}" 
             class="w-8 h-8 rounded-full flex-shrink-0 object-cover ring-1 ring-amber-500/30">
        <p class="text-xs font-semibold text-amber-200 truncate">{{ $invite->name }}</p>
    </div>
    <div class="flex gap-2">
        <button onclick="respondToInvite('{{ $invite->id }}', 'accept')" 
                class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[0.65rem] font-bold rounded-lg transition shadow-sm">
            Accept
        </button>
        <button onclick="respondToInvite('{{ $invite->id }}', 'reject')" 
                class="flex-1 py-1.5 bg-dark-600 hover:bg-dark-500 text-gray-300 text-[0.65rem] font-bold rounded-lg transition border border-dark-500">
            Reject
        </button>
    </div>
</div>
