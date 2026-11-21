@if ($summary['cancel_fee_count'] > 0)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="font-extrabold">
                <i class="fa-solid fa-ban"></i> {{ __('ui.cancel_fee_amount') }}:
                {{ format_price($summary['cancel_fees'], 'IDR') }}
                <span class="font-semibold text-amber-700">&middot; {{ __('ui.cancel_fee_report_count', ['count' => $summary['cancel_fee_count']]) }}</span>
            </div>
            <div class="font-bold">{{ __('ui.total_with_fees') }}: {{ format_price($summary['total_with_fees'], 'IDR') }}</div>
        </div>

        <p class="text-xs text-amber-800">{{ __('ui.cancel_fee_report_note') }}</p>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-[11px] uppercase tracking-wider text-amber-700">
                    <tr>
                        <th class="text-left py-1.5 pr-3">{{ __('ui.booking_code') }}</th>
                        <th class="text-left py-1.5 pr-3">{{ __('ui.employee') }}</th>
                        <th class="text-left py-1.5 pr-3">{{ __('ui.item_name') }}</th>
                        <th class="text-left py-1.5 pr-3">{{ __('ui.cancel_fee_report_cancelled_on') }}</th>
                        <th class="text-right py-1.5">{{ __('ui.cancel_fee_amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-200/70">
                    @foreach ($penalized as $booking)
                        <tr>
                            <td class="py-1.5 pr-3 font-mono">{{ $booking->booking_code }}</td>
                            <td class="py-1.5 pr-3">{{ $booking->user?->companyUser?->displayName() ?? $booking->user?->email }}</td>
                            <td class="py-1.5 pr-3">{{ $booking->hotel_name }}</td>
                            <td class="py-1.5 pr-3 whitespace-nowrap">{{ $booking->cancelled_at?->format('d/m/Y') }}</td>
                            <td class="py-1.5 text-right font-bold">{{ format_price($booking->cancel_fee, 'IDR') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif