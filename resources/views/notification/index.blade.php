@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Notifikasi</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <ul class="list-group">
        @forelse($notifications as $notif)
            <li class="list-group-item @if(!$notif->is_read) list-group-item-info @endif">
                @if($notif->type == 'help_request')
                    Permintaan bantuan dari user ID {{ json_decode($notif->data)->from }} untuk tugas ID {{ json_decode($notif->data)->card_id }}: <br>
                    <em>{{ json_decode($notif->data)->message }}</em>
                @else
                    {{ $notif->data }}
                @endif
                <span class="float-end" style="font-size:12px">{{ $notif->created_at->diffForHumans() }}</span>
            </li>
        @empty
            <li class="list-group-item">Tidak ada notifikasi.</li>
        @endforelse
    </ul>
</div>
@endsection
