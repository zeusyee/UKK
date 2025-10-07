<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Card;
use App\Models\User;
use App\Models\TimeLog;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        // Ringkasan proyek
        $projects = Project::all();
        // Status tugas (Card)
        $tasks = Card::with('project')->get();
        // Progres tim (jumlah tugas selesai/total)
        $teamProgress = [
            'total' => Card::count(),
            'done' => Card::where('status', 'done')->count(),
        ];
        // Laporan aktivitas (TimeLog, Notification)
        $activityLogs = TimeLog::with('user', 'card')->latest()->limit(10)->get();
        $notifications = Notification::where('user_id', $user->user_id)->latest()->limit(10)->get();

        // Filter sesuai role
        if ($role === 'admin') {
            // Admin melihat semua
            return view('dashboard.admin', compact('projects', 'tasks', 'teamProgress', 'activityLogs', 'notifications'));
        } elseif ($role === 'team_lead') {
            // Team lead melihat proyek & tugas yang dipimpin
            $leadProjects = $projects->where('created_by', $user->user_id);
            $leadTasks = $tasks->where('assigned_by', $user->user_id);
            return view('dashboard.team_lead', compact('leadProjects', 'leadTasks', 'teamProgress', 'activityLogs', 'notifications'));
        } else {
            // User/developer/designer melihat tugas sendiri
            $myTasks = $tasks->where('assigned_to', $user->user_id);
            return view('dashboard.user', compact('myTasks', 'teamProgress', 'activityLogs', 'notifications'));
        }
    }
}
