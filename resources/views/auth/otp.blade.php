@extends('layouts.app')
@section('title','Verify code — Tesla Markets')
@section('content')
<section class="auth-card glass"><span class="eyebrow">EMAIL SECURITY</span><h1>Enter your verification code.</h1><p>We sent a six-digit code to {{ $maskedEmail }}. It expires in 10 minutes.</p><form method="post" action="{{ route('auth.otp.verify') }}">@csrf<label>Verification code<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus></label><button class="button wide">Verify and continue</button></form><form method="post" action="{{ route('auth.otp.resend') }}">@csrf<button class="button wide" type="submit">Send a new code</button></form><p><a href="{{ route('login') }}">Cancel and return to login</a></p></section>
@endsection
