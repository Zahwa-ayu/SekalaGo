<div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
    <h2 class="font-extrabold text-slate-900 mb-4">{{ __('ui.history') }}</h2>

    @forelse ($activities as $log)
        <div class="flex gap-3 py-3 border-b border-slate-100 last:border-0">
            <div class="w-8 h-8 shrink-0 rounded-full bg-sky-100 text-sagara-blue flex items-center justify-center text-xs">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="text-sm text-slate-800">
                    <strong>
                        {{ $showActor
                            ? ($log->causer_label ?? __('ui.log_role_system'))
                            : __('ui.log_role_' . ($log->causer_role ?: 'system')) }}
                    </strong>
                    {{ __('ui.log_event_' . $log->event) }}
                    <span class="text-slate-500">{{ $log->subject_label }}</span>
                </div>
                <div class="text-[11px] text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                @include('partials.activity-changes', ['log' => $log])
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-400">{{ __('ui.no_history') }}</p>
    @endforelse
</div>