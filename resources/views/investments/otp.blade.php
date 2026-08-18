@extends('layouts.app')
@section('title','Confirm investment — Tesla Markets')
@section('content')
<section class="auth-card glass"><span class="eyebrow">INVESTMENT SECURITY</span><h1>Confirm your investment.</h1><p>We sent a six-digit code to {{ $maskedEmail }}. Enter it within 10 minutes to authorize this portfolio change.</p><form method="post" action="{{ route('investments.otp.verify') }}">@csrf<label>Verification code<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus></label><button class="button wide">Confirm investment</button></form><p><a href="{{ route('investments.index') }}">Cancel</a></p></section>
@endsection
