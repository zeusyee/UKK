@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Komentar untuk Tugas: {{ $task->title }}</h2>
    <form action="{{ route('comment.store', $task->card_id) }}" method="POST" class="mb-3">
        @csrf
        <div class="mb-3">
            <textarea name="content" class="form-control" rows="2" placeholder="Tulis komentar..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
    <div class="card">
        <div class="card-header">Daftar Komentar</div>
        <ul class="list-group list-group-flush">
            @foreach($comments as $c)
            <li class="list-group-item">
                <strong>{{ $c->user->full_name ?? $c->user->username }}:</strong> {{ $c->content }}
                <span class="text-muted float-end" style="font-size:12px">{{ $c->created_at->diffForHumans() }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
