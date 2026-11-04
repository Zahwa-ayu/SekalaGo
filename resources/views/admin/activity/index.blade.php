@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.activity_log') }}</h1>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        {{-- FILTER --}}
        @php $field = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue'; @endphp
        <form method="GET" action="{{ route('admin.activity.index') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <select name="type" class="{{ $field }}">
                <option value="">{{ __('ui.all') }} ({{ __('ui.log_object') }})</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" @selected(($filters['type'] ?? null) === $type)>{{ __('ui.log_type_' . $type) }}</option>
                @endforeach
            </select>
            <input name="user" value="{{ $filters['user'] ?? '' }}" placeholder="{{ __('ui.log_actor') }}" class="{{ $field }}">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('ui.log_search') }}" class="{{ $field }}">
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="{{ $field }}">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="{{ $field }}">
            <button class="px-4 py-2 rounded-xl bg-sagara-navy text-white text-sm font-bold hover:bg-sagara-blue transition">
                <i class="fa-solid fa-filter"></i> {{ __('ui.filter') }}
            </button>
        </form>

        {{-- TABEL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.date') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.log_actor') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.log_activity') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-sky-50/50 transition align-top">
                                <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-slate-800">{{ $log->causer_label ?? __('ui.log_role_system') }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ __('ui.log_role_' . ($log->causer_role ?: 'system')) }}
                                        @if ($log->ip) &middot; {{ $log->ip }} @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="text-slate-800">
                                        <strong>{{ __('ui.log_event_' . $log->event) }}</strong>
                                        <span class="px-2 py-0.5 rounded-full bg-sky-100 text-sagara-blue text-[11px] font-bold">{{ __($log->typeLabelKey()) }}</span>
                                        {{ $log->subject_label }}
                                    </div>
                                    @include('partials.activity-changes', ['log' => $log])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 block"></i>
                                    {{ __('ui.no_history') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $logs->links() }}</div>
    </section>
@endsection