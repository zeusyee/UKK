@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Laporan Tugas: {{ $task->title }}</h2>
    <div class="card mb-3">
        <div class="card-body">
            <p>Proyek: {{ $task->project->project_name ?? '-' }}</p>
            <p>Penanggung Jawab: {{ $task->user->full_name ?? $task->user->username ?? '-' }}</p>
            <p>Status: {{ $task->status }}</p>
            <p>Prioritas: {{ $task->priority }}</p>
            <p>Deskripsi: {{ $task->description ?? '-' }}</p>
        </div>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
