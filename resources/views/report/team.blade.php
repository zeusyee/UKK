@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Laporan Performa Tim Proyek: {{ $project->project_name }}</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Anggota</th>
                <th>Total Tugas</th>
                <th>Tugas Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teamReport as $row)
            <tr>
                <td>{{ $row['member']->full_name ?? $row['member']->username }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['done'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
