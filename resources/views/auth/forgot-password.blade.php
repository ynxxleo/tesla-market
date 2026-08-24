@extends('layouts.app')
@section('title','Forgot password — Tesla Markets')
@section('content')
<section class="auth-card glass"><span class="eyebrow">ACCOUNT RECOVERY</span><h1>Reset your password.</h1><p>Enter your account email and we’ll send you a secure reset link.</p><form method="post" action="{{ route('password.email') }}">@csrf<label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></label><button class="button wide">Send reset link</button></form><p><a href="{{ route('login') }}">← Back to login</a></p></section>
@endsection
