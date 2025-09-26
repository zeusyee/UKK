@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Daftar Proyek</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            Proyek yang sudah dibuat
            <a href="{{ route('admin.project.create') }}" class="btn btn-light btn-sm float-end">+ Tambah Proyek</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Nama Proyek</th>
                        <th>Deskripsi</th>
                        <th>Deadline</th>
                        <th>Dibuat Oleh</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $p)
                        <tr>
                            <td>{{ $p->project_name }}</td>
                            <td>{{ $p->description ?? '-' }}</td>
                            <td>{{ $p->deadline ?? '-' }}</td>
                            <td>{{ $p->creator->full_name ?? $p->creator->username ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada proyek dibuat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
