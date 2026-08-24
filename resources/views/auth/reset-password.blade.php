@extends('layouts.app')
@section('title','Choose a new password — Tesla Markets')
@section('content')
<section class="auth-card glass"><span class="eyebrow">ACCOUNT RECOVERY</span><h1>Choose a new password.</h1><form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Email<input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"></label><label>New password<input type="password" name="password" required autocomplete="new-password"></label><label>Confirm new password<input type="password" name="password_confirmation" required autocomplete="new-password"></label><button class="button wide">Reset password</button></form></section>
@endsection
