@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Selamat datang, {{ Auth::user()->full_name ?? Auth::user()->username }}</h2>
    <div class="mt-3">
        <p>Ini adalah dashboard user. Silakan akses fitur sesuai hak akses Anda.</p>
    </div>
</div>
@endsection
