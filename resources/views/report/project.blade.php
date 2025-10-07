@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Laporan Progres Proyek: {{ $project->project_name }}</h2>
    <p>Total Tugas: {{ $total }}, Selesai: {{ $done }}</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Judul Tugas</th>
                <th>Status</th>
                <th>Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->status }}</td>
                <td>{{ $task->user->full_name ?? $task->user->username ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
