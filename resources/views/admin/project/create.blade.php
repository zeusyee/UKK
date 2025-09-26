@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Buat Proyek Baru</h2>
    <form action="{{ route('admin.project.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="project_name" class="form-label">Nama Proyek</label>
            <input type="text" class="form-control" id="project_name" name="project_name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="deadline" class="form-label">Deadline</label>
            <input type="date" class="form-control" id="deadline" name="deadline">
        </div>
        <button type="submit" class="btn btn-primary">Buat Proyek</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
