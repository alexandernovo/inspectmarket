<?php

namespace App\Http\Controllers;

use App\Models\MarketNotification;
use Illuminate\Http\Request;

class MarketNotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('market.portal.notifications', [
            'pageTitle' => 'Notifications',
            'notifications' => $request->user()->marketNotifications()->latest()->paginate(20),
        ]);
    }

    public function read(Request $request, MarketNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return $notification->action_url
            ? redirect($notification->action_url)
            : back();
    }

    public function readAll(Request $request)
    {
        $request->user()->marketNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
