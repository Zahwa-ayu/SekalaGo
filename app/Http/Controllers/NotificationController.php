<?php

namespace App\Http\Controllers;

use App\Support\NotificationPresenter;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'items'         => $notifications->getCollection()->map(fn ($n) => NotificationPresenter::present($n)),
            'unread'        => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function count(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    public function latest(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
            'items' => $request->user()->notifications()->latest()->limit(8)->get()
                ->map(fn ($n) => NotificationPresenter::present($n))->values(),
        ]);
    }

    /** Tandai dibaca lalu buka tujuannya (hanya alamat milik aplikasi ini). */
    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = (string) ($notification->data['url'] ?? '');

        return str_starts_with($url, url('/')) ? redirect($url) : redirect()->route('notifications.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}