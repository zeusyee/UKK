@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Daftar Tugas Proyek Anda</h2>
    <a href="{{ route('leader.task.create') }}" class="btn btn-success mb-3">Tambah Tugas</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Proyek</th>
                <th>Penanggung Jawab</th>
                <th>Status</th>
                <th>Prioritas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->project->project_name ?? '-' }}</td>
                <td>{{ $task->assigned_to ? $task->user->full_name ?? $task->user->username : '-' }}</td>
                <td>{{ $task->status }}</td>
                <td>{{ $task->priority }}</td>
                <td>
                    <a href="{{ route('leader.task.edit', $task->card_id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <form action="{{ route('leader.task.destroy', $task->card_id) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus tugas?')">Hapus</button>
                    </form>
                    <a href="{{ route('leader.task.review', $task->card_id) }}" class="btn btn-info btn-sm">Review</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
