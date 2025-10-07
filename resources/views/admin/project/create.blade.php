@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Buat Proyek Baru</h2>

    {{-- Tampilkan pesan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form tambah proyek --}}
    <form action="{{ route('admin.project.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="project_name" class="form-label">Nama Proyek <span class="text-danger">*</span></label>
            <input
                type="text"
                class="form-control @error('project_name') is-invalid @enderror"
                id="project_name"
                name="project_name"
                value="{{ old('project_name') }}"
                required
            >
            @error('project_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea
                class="form-control @error('description') is-invalid @enderror"
                id="description"
                name="description"
                rows="3"
            >{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="deadline" class="form-label">Deadline</label>
            <input
                type="date"
                class="form-control @error('deadline') is-invalid @enderror"
                id="deadline"
                name="deadline"
                value="{{ old('deadline') }}"
            >
            @error('deadline')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.project.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Proyek
            </button>
        </div>
    </form>
</div>
@endsection
