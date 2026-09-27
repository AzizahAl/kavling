<header class="flex items-center justify-end bg-slate-50 px-8 py-4">
    <div class="flex items-center gap-3">
        <div class="text-right">
            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ auth()->user()->name ?? 'Azizah Alghani' }}</p>
            <p class="text-xs text-slate-500 leading-tight">{{ auth()->user()->role ?? 'Master Admin' }}</p>
        </div>
        <img src="{{ auth()->user()->avatar_url ?? 'https://i.pravatar.cc/80?img=47' }}"
             alt="avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200">
    </div>
</header>