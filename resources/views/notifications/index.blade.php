@extends('layouts.app')

@section('content')
    @php
        $authUser    = auth()->user();
        $withSidebar = in_array($authUser->user_type, ['admin', 'partner'], true) || $authUser->companyUser?->isFinance();
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 {{ $withSidebar ? 'py-8' : 'pt-8 pb-12' }} space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.notifications') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('ui.notifications_unread', ['count' => $unread]) }}</p>
            </div>

            <div class="flex items-center gap-4 text-sm">
                @if ($unread > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button class="font-bold text-sky-600 hover:underline">{{ __('ui.mark_all_read') }}</button>
                    </form>
                @endif
                <a href="{{ route('account.notifications') }}" class="font-bold text-slate-500 hover:underline"><i class="fa-solid fa-gear"></i> {{ __('ui.notification_settings') }}</a>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden divide-y divide-slate-100">
            @forelse ($items as $item)
                <a href="{{ $item['open'] }}" class="flex gap-4 px-5 py-4 hover:bg-slate-50 transition {{ $item['read'] ? '' : 'bg-sky-50/50' }}">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center {{ $item['read'] ? 'bg-slate-100 text-slate-500' : 'bg-sky-100 text-sky-600' }}">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="font-bold text-slate-900 text-sm {{ $item['read'] ? '' : 'text-sky-900' }}">{{ $item['title'] }}</div>
                            <div class="text-[11px] text-slate-400 whitespace-nowrap">{{ $item['time'] }}</div>
                        </div>
                        <p class="text-sm text-slate-600 mt-0.5">{{ $item['body'] }}</p>
                    </div>
                    @unless ($item['read'])<span class="w-2 h-2 rounded-full bg-sky-500 mt-2 shrink-0"></span>@endunless
                </a>
            @empty
                <div class="px-6 py-14 text-center text-slate-400">
                    <i class="fa-regular fa-bell text-3xl mb-2 block"></i>
                    {{ __('ui.no_notifications') }}
                </div>
            @endforelse
        </div>

        <div>{{ $notifications->links() }}</div>
    </section>
@endsection