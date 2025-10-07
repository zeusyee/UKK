<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeLog;
use App\Models\Card;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    // List time log untuk tugas tertentu
    public function index($card_id)
    {
        $task = Card::findOrFail($card_id);
        $timelogs = TimeLog::where('card_id', $card_id)->with('user')->latest()->get();
        return view('timelog.index', compact('task', 'timelogs'));
    }

    // Mulai pengerjaan tugas
    public function start($card_id)
    {
        $task = Card::findOrFail($card_id);
        TimeLog::create([
            'card_id' => $card_id,
            'user_id' => Auth::id(),
            'start_time' => now(),
            'status' => 'in_progress',
        ]);
        $task->status = 'in_progress';
        $task->save();
        return back()->with('success', 'Pengerjaan tugas dimulai!');
    }

    // Selesai pengerjaan tugas
    public function finish($card_id)
    {
        $task = Card::findOrFail($card_id);
        $timelog = TimeLog::where('card_id', $card_id)
            ->where('user_id', Auth::id())
            ->whereNull('end_time')
            ->latest()->first();
        if ($timelog) {
            $timelog->end_time = now();
            $timelog->status = 'done';
            $timelog->save();
        }
        $task->status = 'done';
        $task->save();
        return back()->with('success', 'Tugas selesai dikerjakan!');
    }
}
