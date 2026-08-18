@extends('layouts.app')
@section('title', 'Funding — Tesla Markets')
@section('content')
<div class="page-head funding-head">
    <div><span class="eyebrow">SECURE ASSET TRANSFER</span><h1>Fund your portfolio.</h1><p>Choose an asset, scan its wallet, and track every stage from submission to administrator review.</p></div>
    <div class="compliance-badges"><span class="status {{ auth()->user()->kyc_status }}">KYC · {{ str_replace('_',' ',auth()->user()->kyc_status) }}</span><span class="status {{ auth()->user()->sanctions_status }}">SCREENING · {{ str_replace('_',' ',auth()->user()->sanctions_status) }}</span></div>
</div>

@if(auth()->user()->kyc_status !== 'verified' || auth()->user()->sanctions_status !== 'clear')
<a class="kyc-callout glass" href="{{ route('kyc.show') }}"><span>01</span><div><b>Complete verification to unlock funding</b><small>Your documents and screening must be approved before a request can be submitted.</small></div><strong>Continue →</strong></a>
@endif

<div class="funding-shell" data-funding>
    <section class="funding-main glass">
        <div class="funding-tabs" role="tablist"><button type="button" class="active" data-funding-tab="deposit">Deposit</button><button type="button" data-funding-tab="withdrawal">Withdraw</button></div>
        <div class="method-picker"><button type="button" class="active" data-payment-method="crypto"><i>₿</i><span><b>Crypto transfer</b><small>BTC, ETH and stablecoins</small></span></button><button type="button" data-payment-method="wire"><i>⌁</i><span><b>Wire transfer</b><small>Company bank account</small></span></button></div>
        <div data-crypto-panel><div class="funding-intro"><div><span class="step-dot">1</span><h2>Select an asset</h2></div><small>Each asset uses a specific network</small></div>
        <div class="asset-picker">
            @foreach($wallets as $asset => $wallet)
            <button type="button" class="asset-option {{ $loop->first ? 'active' : '' }}" data-asset="{{ $asset }}" data-network="{{ $wallet['network'] }}" data-address="{{ $wallet['address'] }}" data-rate="{{ $rates[$asset] }}">
                <i class="coin coin-{{ strtolower($asset) }}">{{ substr($asset,0,1) }}</i><span><b>{{ $asset }}</b><small>{{ ucfirst($wallet['network']) }} network</small></span><em>✓</em>
            </button>
            @endforeach
        </div></div>
        <div class="wire-panel" data-wire-panel hidden><div class="funding-intro"><div><span class="step-dot">1</span><h2>Company wire instructions</h2></div><span class="wire-timer" data-wire-timer data-expires="{{ $wireExpiresAt }}">20:00</span></div><div class="wire-details"><div><small>BANK</small><b>{{ $wire->value['bank_name'] }}</b></div><div><small>ACCOUNT NAME</small><b>{{ $wire->value['account_name'] }}</b></div><div><small>ACCOUNT / IBAN</small><code>{{ $wire->value['account_number'] }}</code></div><div><small>ROUTING</small><code>{{ $wire->value['routing_number']??'—' }}</code></div><div><small>SWIFT / BIC</small><code>{{ $wire->value['swift_code']??'—' }}</code></div><div><small>REFERENCE</small><code>{{ $wire->value['reference_prefix'] }}-{{ strtoupper(substr(auth()->user()->name,0,3)) }}{{ auth()->id() }}</code></div></div><p class="wire-warning">These platform instructions are valid for this 20-minute viewing session and may change. Refresh after expiry before initiating a transfer.</p></div>
        <div class="deposit-instructions" data-deposit-panel>
            <div class="funding-intro"><div><span class="step-dot">2</span><h2>Scan or copy wallet</h2></div><span class="network-pill" data-wallet-network>BITCOIN</span></div>
            <div class="wallet-display">
                <div class="qr-frame"><canvas data-wallet-qr width="152" height="152"></canvas><span class="qr-mark">T</span></div>
                <div class="wallet-copy"><small>YOUR platform <span data-wallet-asset>BTC</span> ADDRESS</small><code data-wallet-address>{{ $wallets['BTC']['address'] }}</code><button type="button" class="copy-wallet" data-copy-wallet><span>Copy address</span><b>⧉</b></button><p>Send only <strong data-wallet-asset>BTC</strong> using the <strong data-wallet-network>BITCOIN</strong> network.</p></div>
            </div>
        </div>
        <form method="post" action="{{ route('funding.store') }}" class="funding-form">
            @csrf
            <input type="hidden" name="type" value="deposit" data-funding-type>
            <input type="hidden" name="method" value="crypto" data-payment-input>
            <input type="hidden" name="asset" value="BTC" data-funding-asset>
            <input type="hidden" name="network" value="bitcoin" data-funding-network>
            <div class="funding-intro"><div><span class="step-dot">3</span><h2 data-final-step>Enter transfer details</h2></div></div>
            <div class="rate-line" data-rate-line><span><i></i><b data-rate-status>REFERENCE RATE</b></span><strong>1 <span data-rate-asset>BTC</span> = <span data-live-rate>${{ number_format($rates['BTC'],2) }}</span></strong></div>
            <div class="form-grid"><label>USD amount<input name="usd_amount" type="number" min="10" max="1000000" step="0.01" value="{{ old('usd_amount') }}" placeholder="$0.00" data-usd-amount required></label><label data-crypto-amount-field>You receive (estimated)<input name="crypto_amount" type="number" min="0.00000001" step="0.00000001" value="{{ old('crypto_amount') }}" placeholder="0.00000000" data-crypto-amount readonly required></label></div>
            <label class="destination-field" data-crypto-withdrawal hidden>Destination wallet address<input name="destination_address" value="{{ old('destination_address') }}" maxlength="180" placeholder="Paste your receiving wallet address"></label>
            <div class="wire-withdrawal-fields" data-wire-withdrawal hidden><div class="funding-intro"><div><span class="step-dot">2</span><h2>Your receiving bank details</h2></div><small>Used only to process this withdrawal</small></div><div class="form-grid"><label>Beneficiary name<input name="beneficiary_name" value="{{ old('beneficiary_name') }}" maxlength="120" placeholder="Account holder name"></label><label>Bank name<input name="bank_name" value="{{ old('bank_name') }}" maxlength="120" placeholder="Receiving bank"></label><label>Account number / IBAN<input name="account_number" value="{{ old('account_number') }}" maxlength="80" placeholder="Account number or IBAN"></label><label>Routing number<input name="routing_number" value="{{ old('routing_number') }}" maxlength="80" placeholder="Optional"></label><label>SWIFT / BIC<input name="swift_code" value="{{ old('swift_code') }}" maxlength="30" placeholder="Optional"></label><label>Bank address<input name="bank_address" value="{{ old('bank_address') }}" maxlength="255" placeholder="Optional"></label></div><p class="wire-warning">Your bank details are encrypted at rest and visible only to authorized withdrawal reviewers.</p></div>
            <div class="submit-row"><div><span class="shield">◇</span><small></small></div><button class="button">Create deposit ticket →</button></div>
        </form>
    </section>
    <aside class="funding-side glass">
        <div class="panel-title"><div><span class="eyebrow">ACTIVITY</span><h2>Transfer history</h2></div><span>{{ $requests->count() }} requests</span></div>
        <div class="timeline">
        @forelse($requests as $item)
            <article class="transfer-item"><i class="transfer-icon {{ $item->type }}">{{ $item->type==='deposit'?'↓':'↑' }}</i><div><b>{{ ucfirst($item->type) }} · {{ strtoupper($item->method) }} · {{ $item->asset }}</b><small>{{ $item->created_at->format('M j, Y · H:i') }}</small><span class="transfer-status">{{ str_replace('_',' ',$item->status) }}</span></div><strong>${{ number_format($item->usd_amount,2) }}</strong></article>
            @if($item->type==='deposit'&&$item->status==='awaiting_transfer')<form class="tx-form" method="post" action="{{ route('funding.transaction',$item) }}">@csrf<input name="tx_hash" minlength="16" placeholder="Paste transaction reference" required><button class="outline">Submit</button></form>@endif
            @if($item->review_note)<p class="review-note">{{ $item->review_note }}</p>@endif
        @empty
            <div class="empty-state"><span>↕</span><b>No transfers yet</b><p>Your deposit and withdrawal requests will appear here.</p></div>
        @endforelse
        </div>
        <div class="sandbox-note"><b>platform environment</b><p>These wallets and balances are for testing. Never send real cryptocurrency.</p></div>
    </aside>
</div>
@endsection
