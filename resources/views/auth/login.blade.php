@extends('layouts.auth')
@section('title', 'Login')
@section('content')
<div class="auth-card">
    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-sub">Sign in to your WorkLog account</p>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required placeholder="••••••••">
        </div>
        <div class="form-check">
            <label class="check-label">
                <input type="checkbox" name="remember"> Remember me
            </label>
        </div>
        <button type="submit" class="btn-primary btn-full">Sign In →</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
</div>
@endsection
