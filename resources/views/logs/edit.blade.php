@extends('layouts.app')
@section('title', 'Edit Log')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Log</h1>
        <p class="page-sub">Modify your work log entry</p>
    </div>
    <a href="{{ route('logs.show', $log->id) }}" class="btn-ghost">← Cancel</a>
</div>

@include('logs.create')

@endsection
