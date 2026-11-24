<?php

return [
    'greeting' => 'Hello,',
    'open' => 'Open in SekalaGo',
    'footer' => 'You can manage email notifications in Account Settings.',
    'by_customer' => 'the customer', 'by_admin' => 'an admin', 'by_partner' => 'the partner',
    'refund_line' => ':amount was refunded.',
    'type_hotel' => 'Hotel', 'type_vehicle' => 'Vehicle',
    'reason_limit' => 'it exceeds the position limit', 'reason_budget' => 'it exceeds the monthly budget', 'reason_limit_budget' => 'it exceeds the position limit and the monthly budget',

    'booking_confirmed' => ['title' => 'Booking confirmed: :code', 'body' => ':name (:period) is confirmed. Total :amount.'],
    'booking_expired' => ['title' => 'Booking expired: :code', 'body' => 'Your booking for :name was not processed because the deadline passed. Please book again if you still want it.'],
    'approval_approved' => ['title' => 'Booking approved: :code', 'body' => 'Finance approved your booking for :name. It is now confirmed.'],
    'approval_rejected' => ['title' => 'Booking rejected: :code', 'body' => 'Finance rejected your booking for :name. Note: :note'],
    'late_fee_recorded' => ['title' => 'Late fee recorded: :code', 'body' => 'The car :name was returned late. A late fee of :amount is paid directly to the vendor.'],
    'no_show_recorded' => ['title' => 'Arrival not recorded: :code', 'body' => 'The hotel recorded that you did not show up for :name. Think this is a mistake? Contact the hotel or SekalaGo admin.'],

    'new_booking' => ['title' => 'New booking: :code', 'body' => ':guest booked :name (:period). Partner share :amount.'],
    'extension_done' => ['title' => 'Rental extended: :code', 'body' => 'The rental of :name was extended until :date (additional :amount).'],
    'payout_created' => ['title' => 'Payout created: :code', 'body' => 'A payout of :amount to :bank is being processed by the admin.'],
    'payout_paid' => ['title' => 'Funds transferred: :code', 'body' => 'The payout of :amount has been transferred. The proof of transfer is on the Payouts page.'],
    'product_approved' => ['title' => ':type approved', 'body' => ':name has been approved and is now listed on SekalaGo.'],
    'product_rejected' => ['title' => ':type rejected', 'body' => ':name was rejected. Reason: :reason'],
    'partner_verified' => ['title' => 'Partner registration approved', 'body' => 'Your partner account is verified. Review and sign the MOU to start selling.'],
    'partner_rejected' => ['title' => 'Partner registration rejected', 'body' => 'Your registration was rejected. Reason: :note'],

    'approval_requested' => ['title' => 'Trip approval: :code', 'body' => ':employee requested :name (:period), :amount. Reason: :reason.'],
    'company_verified' => ['title' => 'Company approved', 'body' => ':company has been approved. Log in to add employees and top up the balance.'],
    'company_rejected' => ['title' => 'Company registration rejected', 'body' => 'The registration of :company was rejected. Reason: :note'],

    'partner_registered' => ['title' => 'New partner registered', 'body' => ':company is awaiting verification.'],
    'mou_signed' => ['title' => 'MOU signed', 'body' => ':company signed the MOU with a :commission% commission.'],
    'company_registered' => ['title' => 'New company registered', 'body' => ':company is awaiting verification.'],

    'refund_manual' => ['title' => 'Manual refund needed: :code', 'body' => 'The :amount refund (method :method) cannot be processed automatically. Transfer it manually, then record it on the Payments page.'],
    'payment_orphan' => ['title' => 'Payment received for an inactive booking', 'body' => 'Order :order for :amount was paid, but its booking expired or was cancelled. Refund the customer.'],
    'payment_mismatch' => ['title' => 'Payment amount mismatch', 'body' => 'Order :order was paid with an amount that differs from the bill. Check the Midtrans dashboard.'],

    'topup_requested' => ['title' => 'Top-up request: :company', 'body' => ':company requested a top-up of :amount. Check the proof of transfer and the bank statement, then approve or reject.'],
    'topup_approved' => ['title' => 'Top-up approved: :code', 'body' => 'The company balance increased by :amount.'],
    'topup_rejected' => ['title' => 'Top-up rejected: :code', 'body' => 'The top-up request of :amount was rejected. Reason: :reason'],

    'fee_line' => 'A cancellation fee of :amount was withheld.',
    'partner_share_line' => 'Your share of the fee: :amount (included in your payouts).',
    'booking_cancelled' => ['title' => 'Booking cancelled: :code', 'body' => 'Your booking for :name was cancelled by :by. :fee :refund'],
    'booking_cancelled_partner' => ['title' => 'Booking cancelled: :code', 'body' => 'The booking for :name by :guest was cancelled (:by). The stock has been released. :share'],

    'payment_expiring' => ['title' => 'Pay soon: :code', 'body' => 'The payment time for :name has about :minutes minute(s) left. Complete it now so the booking is not lost.'],
    'arrival_tomorrow_hotel' => ['title' => 'Check-in tomorrow: :code', 'body' => 'You stay at :name starting tomorrow. Estimated arrival: :time. Bring your original ID and the e-ticket.'],
    'arrival_tomorrow_car' => ['title' => 'Car pick-up tomorrow: :code', 'body' => 'Pick up :name tomorrow at :time at :place. Bring your original ID (and driving licence for self-drive) and the e-ticket.'],
    'return_soon' => ['title' => 'Return the car soon: :code', 'body' => ':name is due back :time. Need longer? Extend the rental from the booking page before the deadline.'],
    'return_overdue_customer' => ['title' => 'Car returned late: :code', 'body' => ':name has passed its return deadline (:time). A late fee applies under the terms and is paid directly to the vendor. Please return the car now.'],
    'return_overdue_partner' => ['title' => 'Car not returned: :code', 'body' => ':name for :guest has passed its return deadline (:time). Contact the renter and check the next booking under Active Rentals.'],
    'review_prompt' => ['title' => 'How was your experience?', 'body' => 'Tell us about your experience with :name. Your review helps other customers.'],
    'review_received' => ['title' => 'New review :rating/5', 'body' => 'A customer reviewed :name. Open the Reviews page to read and reply.'],
    'review_replied' => ['title' => 'The partner replied to your review', 'body' => 'The partner of :name replied to your review.'],
    'issue_reported' => ['title' => 'New issue report: :code', 'body' => 'A report for booking :booking (:name) awaits review.'],
    'issue_for_partner' => ['title' => 'A customer reported a problem: :code', 'body' => 'There is a report for booking :booking (:name). Open Issue Reports to respond.'],
    'issue_updated' => ['title' => 'Report :code updated', 'body' => 'Your report status is now: :status. Open My reports for the answer.'],
];