@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Time Log untuk Tugas: {{ $task->title }}</h2>
    <div class="mb-3">
        <form action="{{ route('timelog.start', $task->card_id) }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-success">Mulai Kerja</button>
        </form>
        <form action="{{ route('timelog.finish', $task->card_id) }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-primary">Selesai Kerja</button>
        </form>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timelogs as $log)
            <tr>
                <td>{{ $log->user->full_name ?? $log->user->username }}</td>
                <td>{{ $log->start_time }}</td>
                <td>{{ $log->end_time ?? '-' }}</td>
                <td>{{ $log->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
