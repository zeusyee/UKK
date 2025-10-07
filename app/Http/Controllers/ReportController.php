<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Card;

class ReportController extends Controller
{
    // Laporan progres proyek
    public function project($project_id)
    {
        $project = Project::with('members')->findOrFail($project_id);
        $tasks = Card::where('project_id', $project_id)->get();
        $done = $tasks->where('status', 'done')->count();
        $total = $tasks->count();
        return view('report.project', compact('project', 'tasks', 'done', 'total'));
    }

    // Laporan performa tim
    public function team($project_id)
    {
        $project = Project::with('members')->findOrFail($project_id);
        $members = $project->members;
        $teamReport = [];
        foreach ($members as $member) {
            $taskCount = Card::where('assigned_to', $member->user_id)->where('project_id', $project_id)->count();
            $doneCount = Card::where('assigned_to', $member->user_id)->where('project_id', $project_id)->where('status', 'done')->count();
            $teamReport[] = [
                'member' => $member,
                'total' => $taskCount,
                'done' => $doneCount,
            ];
        }
        return view('report.team', compact('project', 'teamReport'));
    }

    // Laporan status tugas
    public function task($card_id)
    {
        $task = Card::with('project', 'user')->findOrFail($card_id);
        return view('report.task', compact('task'));
    }
}
