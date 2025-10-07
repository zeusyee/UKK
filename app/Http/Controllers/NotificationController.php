<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\HelpRequest;

class NotificationController extends Controller
{
    // List notifikasi user
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())->latest()->get();
        return view('notification.index', compact('notifications'));
    }

    // Kirim permintaan bantuan ke team lead
    public function helpRequest(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'message' => 'required',
        ]);
        $help = HelpRequest::create([
            'card_id' => $request->card_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'status' => 'pending',
        ]);
        // Buat notifikasi ke team lead (asumsi ada relasi card->project->team_lead_id)
        $teamLeadId = $help->card->project->created_by ?? null;
        if ($teamLeadId) {
            Notification::create([
                'user_id' => $teamLeadId,
                'type' => 'help_request',
                'data' => json_encode([
                    'from' => Auth::id(),
                    'card_id' => $request->card_id,
                    'message' => $request->message,
                ]),
                'is_read' => false,
            ]);
        }
        return back()->with('success', 'Permintaan bantuan dikirim ke team lead!');
    }

    // (Optional) Tambahkan fitur notifikasi lain sesuai kebutuhan
}