{{-- Tombol hapus (teks merah polos) + popup konfirmasi. Popup dan skripnya hanya dimuat sekali per halaman.
     Parameter: action (wajib), title, message, label, class --}}
@php
    $title   = $title   ?? 'Hapus data ini?';
    $message = $message ?? 'Data yang dihapus tidak bisa dikembalikan.';
    $label   = $label   ?? 'Hapus';
    $class   = $class   ?? 'font-bold text-red-600 hover:underline';
@endphp

<form method="POST" action="{{ $action }}" class="inline"
      data-delete-form data-title="{{ $title }}" data-message="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $class }}">{{ $label }}</button>
</form>

@once
    <div id="delete-modal" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        {{-- whitespace-normal, font-normal, tracking-normal: reset warisan dari <td> tabel (nowrap, bold, text-xs) --}}
        <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl text-center whitespace-normal font-normal tracking-normal normal-case">
            <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-500 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="delete-modal-title" class="text-lg font-extrabold text-slate-900 break-words"></h3>
            <p id="delete-modal-message" class="mt-1 text-sm font-normal text-slate-500 break-words"></p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="delete-modal-cancel"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">Batal</button>
                <button type="button" id="delete-modal-confirm"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition">Ya, hapus</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal   = document.getElementById('delete-modal');
            const title   = document.getElementById('delete-modal-title');
            const message = document.getElementById('delete-modal-message');
            let pending   = null;

            const close = () => { modal.classList.add('hidden'); pending = null; };

            document.addEventListener('submit', (event) => {
                const form = event.target.closest('[data-delete-form]');
                if (!form) return;

                event.preventDefault();
                pending = form;
                title.textContent   = form.dataset.title;
                message.textContent = form.dataset.message;
                modal.classList.remove('hidden');
            });

            document.getElementById('delete-modal-cancel').addEventListener('click', close);
            modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });

            document.getElementById('delete-modal-confirm').addEventListener('click', () => {
                if (pending) pending.submit(); // submit() tidak memicu event submit lagi
            });
        })();
    </script>
@endonce