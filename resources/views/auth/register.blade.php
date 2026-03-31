@extends('layouts.auth')
@section('title', 'Register')
@section('content')
<div class="auth-card">
    <h2 class="auth-title">Create account</h2>
    <p class="auth-sub">Start logging your daily work</p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="John Doe">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required placeholder="Min 8 characters">
        </div>
        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-input" required placeholder="Repeat password">
        </div>
        <button type="submit" class="btn-primary btn-full">Create Account →</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</div>
@endsection
