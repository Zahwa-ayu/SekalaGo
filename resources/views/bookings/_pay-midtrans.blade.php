<div id="midtrans-pay" class="space-y-4">
    <div id="pay-message" class="hidden px-4 py-3 rounded-xl text-sm"></div>

    <button type="button" id="pay-button"
            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition disabled:opacity-60 disabled:cursor-wait">
        <i class="fa-solid fa-lock mr-1"></i> {{ __('ui.pay_with_midtrans', ['amount' => format_price($booking->total_price, 'IDR')]) }}
    </button>

    <p class="text-[11px] text-slate-500">{{ __('ui.pay_methods_note') }}</p>
</div>

<script src="{{ config('services.midtrans.snap_js') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    (function () {
        const button  = document.getElementById('pay-button');
        const message = document.getElementById('pay-message');
        const show = (text, tone) => {
            message.textContent = text;
            message.className = 'px-4 py-3 rounded-xl text-sm ' + (tone === 'error'
                ? 'bg-red-50 border border-red-200 text-red-700'
                : 'bg-sky-50 border border-sky-100 text-sky-800');
        };

        // Setelah popup: tunggu webhook (atau pengecekan langsung) mengonfirmasi, lalu buka halaman pesanan
        async function waitForConfirmation() {
            show(@json(__('ui.pay_verifying')), 'info');

            for (let i = 0; i < 10; i++) {
                try {
                    const res = await fetch(@json(route('bookings.pay.status', $booking)), { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    if (data.status !== 'pending_payment') break;
                } catch (e) { /* coba lagi */ }
                await new Promise((r) => setTimeout(r, 2000));
            }

            window.location.href = @json(route('bookings.show', $booking));
        }

        button.addEventListener('click', async () => {
            button.disabled = true;
            message.className = 'hidden';

            try {
                const res = await fetch(@json(route('bookings.pay.snap', $booking)), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @json(csrf_token()), Accept: 'application/json' },
                });
                const data = await res.json();

                if (!res.ok) {
                    show(data.message || @json(__('ui.payment_gateway_error')), 'error');
                    button.disabled = false;
                    return;
                }

                window.snap.pay(data.token, {
                    onSuccess: waitForConfirmation,
                    onPending: waitForConfirmation,
                    onError: () => { show(@json(__('ui.pay_failed')), 'error'); button.disabled = false; },
                    onClose: () => { button.disabled = false; },
                });
            } catch (e) {
                show(@json(__('ui.payment_gateway_error')), 'error');
                button.disabled = false;
            }
        });
    })();
</script>