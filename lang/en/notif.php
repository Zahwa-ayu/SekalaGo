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
    'booking_cancelled' => ['title' => 'Booking cancelled: :code', 'body' => 'Your booking for :name was cancelled by :by. :refund'],
    'booking_expired' => ['title' => 'Booking expired: :code', 'body' => 'Your booking for :name was not processed because the deadline passed. Please book again if you still want it.'],
    'approval_approved' => ['title' => 'Booking approved: :code', 'body' => 'Finance approved your booking for :name. It is now confirmed.'],
    'approval_rejected' => ['title' => 'Booking rejected: :code', 'body' => 'Finance rejected your booking for :name. Note: :note'],
    'late_fee_recorded' => ['title' => 'Late fee recorded: :code', 'body' => 'The car :name was returned late. A late fee of :amount is paid directly to the vendor.'],
    'no_show_recorded' => ['title' => 'Arrival not recorded: :code', 'body' => 'The hotel recorded that you did not show up for :name. Think this is a mistake? Contact the hotel or SekalaGo admin.'],

    'new_booking' => ['title' => 'New booking: :code', 'body' => ':guest booked :name (:period). Partner share :amount.'],
    'booking_cancelled_partner' => ['title' => 'Booking cancelled: :code', 'body' => 'The booking for :name by :guest was cancelled (:by). The stock has been released.'],
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
];