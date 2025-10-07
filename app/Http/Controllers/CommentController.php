<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Card;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // List komentar pada tugas tertentu
    public function index($card_id)
    {
        $task = Card::findOrFail($card_id);
        $comments = Comment::where('card_id', $card_id)->with('user')->latest()->get();
        return view('comment.index', compact('task', 'comments'));
    }

    // Simpan komentar baru
    public function store(Request $request, $card_id)
    {
        $request->validate([
            'content' => 'required',
        ]);
        Comment::create([
            'card_id' => $card_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);
        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }
}
