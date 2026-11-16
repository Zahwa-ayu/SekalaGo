@auth
    @php
        $dark   = ($tone ?? 'light') === 'dark';
        $button = $dark ? 'text-slate-200 hover:text-white hover:bg-white/10' : 'text-slate-600 hover:text-sky-600 hover:bg-sky-50';
    @endphp

    <div class="relative" id="notif-root" data-count-url="{{ route('notifications.count') }}" data-latest-url="{{ route('notifications.latest') }}">
        <button type="button" id="notif-toggle" class="relative p-2 rounded-lg transition {{ $button }}" aria-label="{{ __('ui.notifications') }}">
            <i class="fa-regular fa-bell text-xl"></i>
            <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-black items-center justify-center"></span>
        </button>

        <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] bg-white text-slate-800 rounded-2xl border border-slate-200 shadow-2xl z-50 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <span class="font-extrabold text-sm text-slate-900">{{ __('ui.notifications') }}</span>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="text-[11px] font-bold text-sky-600 hover:underline">{{ __('ui.mark_all_read') }}</button>
                </form>
            </div>
            <div id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-100 text-sm"></div>
            <a href="{{ route('notifications.index') }}" class="block text-center px-4 py-2.5 text-xs font-bold text-sky-600 hover:bg-slate-50 border-t border-slate-100">{{ __('ui.view_all') }}</a>
        </div>
    </div>

    @once
        <script>
            (function () {
                const root  = document.getElementById('notif-root');
                if (!root) return;
                const badge = document.getElementById('notif-badge');
                const panel = document.getElementById('notif-panel');
                const list  = document.getElementById('notif-list');
                const empty = @js(__('ui.no_notifications'));

                function setBadge(count) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.toggle('hidden', count === 0);
                    badge.classList.toggle('flex', count > 0);
                }

                function render(items) {
                    list.replaceChildren();
                    if (!items.length) {
                        const p = document.createElement('div');
                        p.className = 'px-4 py-8 text-center text-slate-400 text-xs';
                        p.textContent = empty;
                        list.appendChild(p);
                        return;
                    }
                    items.forEach((item) => {
                        const a = document.createElement('a');
                        a.href = item.open;
                        a.className = 'block px-4 py-3 hover:bg-slate-50 ' + (item.read ? '' : 'bg-sky-50/60');
                        const title = document.createElement('div');
                        title.className = 'font-bold text-slate-900 text-[13px]';
                        title.textContent = item.title;
                        const body = document.createElement('div');
                        body.className = 'text-xs text-slate-600 mt-0.5 line-clamp-2';
                        body.textContent = item.body;
                        const time = document.createElement('div');
                        time.className = 'text-[10px] text-slate-400 mt-1';
                        time.textContent = item.time;
                        a.append(title, body, time);
                        list.appendChild(a);
                    });
                }

                // Hitungan belum dibaca diperbarui tiap menit
                function refreshCount() {
                    fetch(root.dataset.countUrl, { headers: { Accept: 'application/json' } })
                        .then((r) => r.json()).then((d) => setBadge(d.count)).catch(() => {});
                }

                document.getElementById('notif-toggle').addEventListener('click', () => {
                    panel.classList.toggle('hidden');
                    if (!panel.classList.contains('hidden')) {
                        fetch(root.dataset.latestUrl, { headers: { Accept: 'application/json' } })
                            .then((r) => r.json()).then((d) => { setBadge(d.count); render(d.items); }).catch(() => {});
                    }
                });

                document.addEventListener('click', (e) => { if (!root.contains(e.target)) panel.classList.add('hidden'); });

                refreshCount();
                setInterval(refreshCount, 60000);
            })();
        </script>
    @endonce
@endauth