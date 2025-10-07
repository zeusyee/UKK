@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Buat Tugas Baru</h2>
    <form action="{{ route('leader.task.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Judul</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Proyek</label>
            <select name="project_id" class="form-control" required>
                @foreach($projects as $p)
                    <option value="{{ $p->project_id }}">{{ $p->project_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Penanggung Jawab</label>
            <select name="assigned_to" class="form-control" required>
                @foreach($members as $m)
                    <option value="{{ $m->user_id }}">{{ $m->full_name ?? $m->username }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Prioritas</label>
            <select name="priority" class="form-control" required>
                <option value="tinggi">Tinggi</option>
                <option value="sedang">Sedang</option>
                <option value="rendah">Rendah</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('leader.task.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
