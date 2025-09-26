@extends('layouts.app')

@section('content')
<div class="container mt-4">
	<h1 class="mb-4">Dashboard Admin - Project Admin</h1>
	<div class="row">
		<div class="col-md-6">
			<div class="card mb-3">
				<div class="card-header bg-primary text-white">Manajemen Proyek</div>
				<div class="card-body">
					<ul>
						<li><strong>Buat/Hapus Proyek:</strong> Tambahkan atau hapus proyek sesuai kebutuhan organisasi.</li>
						<li><strong>Setup Awal Proyek:</strong> Atur detail, deadline, dan tujuan proyek baru.</li>
					</ul>
					<a href="{{ route('admin.project.create') }}" class="btn btn-success btn-sm">Buat Proyek Baru</a>
				</div>
			</div>
			<div class="card mb-3">
				<div class="card-header bg-info text-white">Manajemen Tim & Anggota</div>
				<div class="card-body">
					<ul>
						<li><strong>Kelola Anggota:</strong> Tambah, edit, atau hapus anggota tim proyek.</li>
						<li><strong>Manajemen Tim:</strong> Atur struktur dan peran tim dalam proyek.</li>
					</ul>
					<a href="{{route('admin.project-member')}}" class="btn btn-primary btn-sm">Kelola Anggota</a>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card mb-3">
				<div class="card-header bg-warning text-dark">Monitoring & Kontrol</div>
				<div class="card-body">
					<ul>
						<li><strong>Akses Semua Data:</strong> Lihat seluruh data proyek, anggota, dan tugas.</li>
						<li><strong>Monitoring Keseluruhan:</strong> Pantau progres dan status semua proyek.</li>
						<li><strong>Edit Semua Tugas:</strong> Ubah atau kelola tugas pada semua proyek.</li>
					</ul>
					<a href="{{ route('admin.project.index') }}" class="btn btn-info btn-sm">Lihat Semua Proyek</a>
				</div>
			</div>
			<div class="card mb-3">
				<div class="card-header bg-success text-white">Laporan</div>
				<div class="card-body">
					<ul>
						<li><strong>Generate Laporan:</strong> Buat laporan proyek, anggota, dan progres secara otomatis.</li>
					</ul>
					<a href="#" class="btn btn-success btn-sm">Generate Laporan</a>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
