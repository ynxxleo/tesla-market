@extends('layouts.app')
@section('title', 'Tesla Packages — Tesla Markets')
@section('content')
<div class="page-head"><div><span class="eyebrow">TESLA INVESTMENT GARAGE</span><h1>Choose your drive.</h1><p>Build a modeled portfolio through vehicle-inspired packages. Rates are simulation assumptions, not guaranteed returns.</p></div></div>

<section class="eligibility glass">
    <div class="eligibility-copy"><span class="eyebrow">TRADING ELIGIBILITY</span><h2>{{ $eligibility['current'] }} access</h2><p>Your active package balance determines which Tesla trading tier you can access.</p></div>
    <div class="eligibility-meter"><div class="eligibility-numbers"><b>${{ number_format($eligibility['total'],2) }} invested</b>@if($eligibility['next'])<span>${{ number_format($eligibility['next']['amount']-$eligibility['total'],2) }} to {{ $eligibility['next']['name'] }}</span>@else<span>All tiers unlocked</span>@endif</div><div class="meter-track"><i style="width:{{ $eligibility['progress'] }}%"></i></div><div class="meter-labels">@foreach($packages as $package)<span>{{ $package['name'] }} · ${{ $package['min']>=1000?number_format($package['min']/1000).'K':number_format($package['min']) }}</span>@endforeach</div></div>
</section>

<section class="package-grid">
@foreach($packages as $slug=>$package)
<article class="car-package glass" style="--delay:{{ $loop->index * 100 }}ms">
    <div class="package-image"><img class="car-dark" src="{{ asset($package['image']) }}" alt="{{ $package['name'] }} package in dark studio"><img class="car-light" src="{{ asset($package['image_light']) }}" alt="{{ $package['name'] }} package in light studio"><span>0{{ $loop->iteration }}</span><em>{{ number_format($package['rate'],2) }}% MODEL</em></div>
    <div class="package-content"><div><span class="eyebrow">{{ strtoupper($package['name']) }} PACKAGE</span><h2>{{ $package['name'] }}</h2></div><p>Portfolio range</p><b class="package-range">${{ number_format($package['min']) }} — ${{ number_format($package['max']) }}</b><div class="package-features"><span>Daily modeled accrual</span><span>Portfolio tracking</span><span>{{ $package['name'] }} eligibility</span></div><form method="post" action="{{ route('investments.store') }}">@csrf<input type="hidden" name="plan" value="{{ $slug }}"><label>Investment amount<input type="number" name="amount" min="{{ $package['min'] }}" max="{{ $package['max'] }}" step="100" placeholder="Min. ${{ number_format($package['min']) }}" required></label><button class="button wide">Add to portfolio →</button></form></div>
</article>
@endforeach
</section>

<section class="portfolio-section glass"><div class="panel-title"><div><span class="eyebrow">YOUR GARAGE</span><h2>Active portfolio</h2></div><strong>${{ number_format($investments->sum('balance'),2) }}</strong></div><div class="portfolio-list">@forelse($investments as $investment)@php($package=$packages[$investment->plan]??['name'=>str($investment->plan)->headline(),'image'=>'images/packages/model-3.png','image_light'=>'images/packages/model-3-light.png'])<article class="portfolio-item"><div class="portfolio-car"><img class="car-dark" src="{{ asset($package['image']) }}" alt=""><img class="car-light" src="{{ asset($package['image_light']) }}" alt=""></div><div><b>{{ $package['name'] }} package</b><small>Started {{ $investment->started_at->format('M j, Y') }} · {{ number_format($investment->annual_rate,2) }}% model</small></div><div><small>PRINCIPAL</small><b>${{ number_format($investment->principal,2) }}</b></div><div><small>CURRENT MODEL</small><strong>${{ number_format($investment->balance,2) }}</strong></div></article>@empty<div class="empty-state"><span>⌁</span><b>Your garage is empty</b><p>Select a package above to build your modeled portfolio.</p></div>@endforelse</div></section>
@endsection
