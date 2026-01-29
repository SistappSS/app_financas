<?php

namespace App\Http\Controllers\Web;

use App\Models\Notification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(
            Auth::user()->notifications()->latest('sent_at')->get()
        );
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorize('update', $notification);
        $notification->update(['read' => true]);

        return response()->json($notification);
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        $notification->delete();

        return response()->json(null, 204);
    }
}
