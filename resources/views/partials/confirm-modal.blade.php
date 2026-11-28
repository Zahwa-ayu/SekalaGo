{{-- Popup konfirmasi untuk form yang punya atribut data-confirm-form --}}
@once
    <div id="confirm-modal" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-500 flex items-center justify-center text-xl mb-4">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="confirm-modal-title" class="text-lg font-extrabold text-slate-900 break-words"></h3>
            <p id="confirm-modal-message" class="mt-1 text-sm font-normal text-slate-500 break-words"></p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="confirm-modal-cancel"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.back') }}</button>
                <button type="button" id="confirm-modal-confirm"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition disabled:opacity-60">{{ __('ui.confirm_yes') }}</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal     = document.getElementById('confirm-modal');
            const title     = document.getElementById('confirm-modal-title');
            const message   = document.getElementById('confirm-modal-message');
            const yesButton = document.getElementById('confirm-modal-confirm');
            let pending     = null;

            const close = () => {
                modal.classList.add('hidden');
                yesButton.disabled = false;
                pending = null;
            };

            document.addEventListener('submit', (event) => {
                const form = event.target.closest('[data-confirm-form]');
                if (!form) return;

                event.preventDefault();
                pending = form;
                title.textContent   = form.dataset.title;
                message.textContent = form.dataset.message;
                modal.classList.remove('hidden');
            });

            document.getElementById('confirm-modal-cancel').addEventListener('click', close);
            modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
            window.addEventListener('pageshow', close);

            yesButton.addEventListener('click', () => {
                if (!pending) return;
                yesButton.disabled = true; // cegah klik dua kali
                pending.submit();
            });
        })();
    </script>
@endonce 