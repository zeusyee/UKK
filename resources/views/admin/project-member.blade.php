@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Kelola Anggota Proyek</h1>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Form Tambah Anggota --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah User ke Project</div>
        <div class="card-body">
            <form action="{{ route('admin.addMember') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select" required>
                        @foreach($users as $u)
                            <option value="{{ $u->user_id }}">{{ $u->full_name ?? $u->username }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" required>
                        @foreach($projects as $p)
                            <option value="{{ $p->project_id }}">{{ $p->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="team_lead">Team Lead</option>
                        <option value="developer">Developer</option>
                        <option value="user">User</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Tambah</button>
            </form>
        </div>
    </div>

    {{-- Daftar Member --}}
    <div class="card">
        <div class="card-header bg-info text-white">Daftar Member Project</div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>User</th>
                        <th>Role</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                        <tr>
                            <td>{{ $m->project->project_name }}</td>
                            <td>{{ $m->user->full_name ?? $m->user->username }}</td>
                            <td>{{ ucfirst($m->role) }}</td>
                            <td>
                                <form action="{{ route('admin.removeMember', $m->member_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada anggota</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
