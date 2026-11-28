@php
    // kunci session => [gaya, ikon]
    $types = [
        'success' => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'fa-circle-check text-emerald-500'],
        'status'  => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'fa-circle-check text-emerald-500'],
        'info'    => ['bg-sky-50 border-sky-200 text-sky-800',             'fa-circle-info text-sky-500'],
        'warning' => ['bg-amber-50 border-amber-200 text-amber-800',       'fa-triangle-exclamation text-amber-500'],
        'error'   => ['bg-red-50 border-red-200 text-red-800',             'fa-circle-xmark text-red-500'],
    ];

    $messages = [];
    foreach ($types as $key => [$style, $icon]) {
        if (session()->has($key) && is_string(session($key))) {
            $messages[] = [$style, $icon, session($key)];
        }
    }
@endphp

@if (count($messages))
    <div id="flash-stack"
         class="fixed z-[60] top-20 inset-x-4 sm:inset-x-auto sm:right-6 sm:w-96 space-y-2 pointer-events-none">
        @foreach ($messages as [$style, $icon, $text])
            <div data-flash role="alert"
                 class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-lg shadow-black/10 text-sm font-medium transition duration-300 {{ $style }}">
                <i class="fa-solid {{ $icon }} mt-0.5"></i>
                <p class="flex-1 leading-snug">{{ $text }}</p>
                <button type="button" data-flash-close class="opacity-50 hover:opacity-100 transition" aria-label="Tutup">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            const hide = (el) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px)';
                setTimeout(() => el.remove(), 300);
            };

            document.querySelectorAll('[data-flash]').forEach((el) => {
                el.querySelector('[data-flash-close]').addEventListener('click', () => hide(el));
                setTimeout(() => hide(el), 6000); // hilang sendiri setelah 6 detik
            });
        })();
    </script>
@endif