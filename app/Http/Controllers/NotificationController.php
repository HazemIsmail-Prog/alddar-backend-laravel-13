<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\SendNotificationToUserAction;

class NotificationController extends Controller
{
    public function index()
    {
        $technicians = User::query()
            ->where('is_technician', true)
            ->where('is_active', true)
            ->get()
            ;
        return response()->json($technicians);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'title' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        try {

            (new SendNotificationToUserAction())->handle(
                $validated['technician_id'],
                    $validated['title'],
                    $validated['message'],
                    config('services.beams.tech_frontend_url')
                    );
            return response()->json(['message' => 'Notification sent successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send notification'], 500);
        }

    }
}
