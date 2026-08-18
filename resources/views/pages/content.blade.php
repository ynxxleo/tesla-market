@extends('layouts.app')
@php
$pages=[
'about'=>['About Tesla Markets','A modern educational workspace for understanding markets before risking capital.',[
['What we built','Tesla Markets combines simulated stock and crypto execution, portfolio tracking, Tesla-inspired learning packages, identity-verification workflows, funding demonstrations, and guided support in one coherent environment.'],
['Our purpose','The platform is designed to make trading interfaces, operational controls, and risk decisions easier to understand. It is a software prototype and educational simulator—not a broker, exchange, custodian, bank, or investment adviser.'],
['How we work','Product experience, market engineering, compliance operations, and customer support are treated as one system. Every simulated transaction creates a visible status and review trail.'],
['Independent prototype','Tesla Markets is not affiliated with or endorsed by Tesla, Inc., xAI, Grok, or any financial institution. Product and company names may be used only to describe this demonstration experience.']]],
'contact'=>['Contact us','Get help with account access, verification, funding requests, or the simulator.',[
['Customer support','Email support@teslamarkets.test from your registered email address. Include a concise description, the affected page, approximate time, and any non-sensitive ticket reference.'],
['Compliance questions','For KYC, screening, account restrictions, or transaction reviews, write “Compliance review” in the subject line. Reviews may require additional information through an approved secure channel.'],
['Security reports','Report suspected unauthorized access immediately. Never email passwords, one-time codes, seed phrases, private keys, complete wallet credentials, or identity documents.'],
['Response expectations','This test environment does not guarantee response times. Production support hours, escalation targets, and complaint handling procedures will be published before any licensed launch.']]],
'help'=>['Help center','Practical guidance for navigating the Tesla Markets simulator.',[
['Getting started','Create an account, review the disclosures, complete the demonstration identity workflow, then open Overview to see balances, positions, packages, and milestones.'],
['Funding and withdrawals','Funding supports platform crypto and wire workflows. Deposits use the displayed company destination; withdrawals use your own receiving wallet or bank details and remain pending until administrator review.'],
['Trading','Search stocks or cryptocurrencies, select a market, change the chart timeframe, enter a quantity, and review the estimate before submitting. Open positions can be closed from the trading terminal.'],
['Grok Assistant','Grok Assistant can explain platform features and account statuses. If additional help is required, request customer support in the conversation. AI and human support messages are identified in the interface.']]],
'fees'=>['Fees','A transparent summary of costs represented in this sandbox.',[
['Platform access','No real subscription, custody, brokerage, deposit, or withdrawal fee is currently charged because this is an educational test environment.'],
['Market estimates','Displayed prices, spreads, conversions, network costs, profits, yields, and execution totals are simulated or reference values. They may be delayed and are not executable quotations.'],
['Future pricing','Any production service would publish a full fee schedule before charging users, including applicable trading, custody, withdrawal, conversion, wire, network, inactivity, and third-party costs.'],
['Taxes and external charges','Users would remain responsible for taxes, bank fees, blockchain fees, and other third-party charges where applicable. Consult a qualified adviser for your circumstances.']]],
'terms'=>['Terms of service','Rules governing access to this educational prototype.',[
['Eligibility and accounts','You must be at least 18, provide accurate information, keep credentials secure, and use only an account you are authorized to control. We may restrict access for suspected misuse or security concerns.'],
['Simulation status','Balances, deposits, withdrawals, trades, profits, yields, packages, eligibility counters, and reward milestones are simulated. They are not claims against real assets and cannot be redeemed for cash or a vehicle.'],
['Acceptable use','Do not probe security controls, automate abusive requests, impersonate others, launder funds, evade sanctions, manipulate records, upload malicious content, or use the platform unlawfully.'],
['Tesla milestone','The $1,000,000 milestone is a demonstration interface only and does not guarantee a Tesla or prize. A future promotion would require separate official rules, eligibility terms, jurisdictional review, and required approvals.'],
['Service changes','Features may change, reset, become unavailable, or contain errors during testing. Access is provided without a promise of uninterrupted availability or fitness for investment decisions.']]],
'privacy'=>['Privacy policy','How information is handled in this demonstration environment.',[
['Information collected','We may process account details, authentication records, profile images, KYC submissions, device and security data, support conversations, simulated orders, funding requests, and administrative review records.'],
['Purposes','Information is used to operate the sandbox, secure accounts, demonstrate compliance workflows, investigate misuse, provide support, test functionality, and maintain audit trails.'],
['Storage and access','KYC files use private storage, profile images use controlled public storage, and withdrawal destination details are encrypted at rest. Authorized administrators may access data required for review and support.'],
['Retention and rights','Test data may be retained for security, audit, and development needs. Production retention periods, deletion processes, lawful bases, international-transfer safeguards, and regional privacy rights will be finalized before launch.'],
['Sensitive information','Do not upload genuine identity or financial documents unless this environment has been formally approved for such use. Never submit passwords, private keys, recovery phrases, or one-time codes.']]],
'legal'=>['Legal information','Important limitations and platform status.',[
['No regulated service','Tesla Markets does not currently offer brokerage, exchange, custody, banking, payment, investment-management, or financial-advisory services. No live customer assets should be sent to displayed test destinations.'],
['No affiliation','This independent prototype is not affiliated with Tesla, Inc., xAI, Grok, any stock exchange, cryptocurrency network, broker, custodian, or financial institution.'],
['No advice','Information and assistant responses are general educational material, not investment, legal, accounting, or tax advice. Nothing is a recommendation, solicitation, or guarantee.'],
['Intellectual property','Names, logos, images, and trademarks remain the property of their respective owners. Their appearance in a prototype does not imply sponsorship or endorsement.'],
['Licensing','A production launch involving real assets would depend on legal review, appropriate registrations or licenses, approved vendors, banking and custody arrangements, and jurisdiction-specific restrictions.']]],
'risk'=>['Risk disclosure','Understand the risks associated with real markets and digital assets.',[
['Loss of capital','Stocks and cryptocurrencies can be highly volatile. Prices may move rapidly, liquidity can disappear, and investors can lose some or all capital.'],
['Simulation limitations','results exclude many real-world effects, including slippage, latency, outages, market impact, taxes, changing fees, rejected orders, custody failures, and emotional decision-making.'],
['Digital-asset risks','Blockchain transactions can be irreversible. Wrong networks or addresses, compromised keys, smart-contract failures, forks, regulatory changes, and platform insolvency may cause permanent loss.'],
['No performance promise','Historical, hypothetical, and modeled performance does not predict future outcomes. Automatic profit percentages in packages are demonstration calculations, not guaranteed returns.'],
['Personal responsibility','Never risk funds you cannot afford to lose. Consider financial circumstances, objectives, experience, and independent professional advice before using a real service.']]],
'aml'=>['AML & sanctions policy','A demonstration of compliance-first financial-crime controls.',[
['Customer due diligence','Funding access requires identity information and sanctions status. Higher-risk activity may require enhanced due diligence, source-of-funds information, or additional administrator review.'],
['Screening and monitoring','The prototype demonstrates sanctions screening, wallet and transaction checks, risk scoring, account restrictions, case notes, reconciliation, and audit records. Requests may be delayed, rejected, or escalated.'],
['Prohibited activity','The service must not be used for money laundering, terrorist financing, sanctions evasion, fraud, trafficking, ransomware, market abuse, stolen funds, or activity involving prohibited persons or jurisdictions.'],
['Records and reporting','A production program would define retention schedules, investigation procedures, trained compliance ownership, regulatory reporting, independent testing, and escalation to competent authorities where required.'],
['Production dependencies','Effective AML controls require approved identity, sanctions, blockchain-analysis, custody, banking, monitoring, and case-management providers plus jurisdiction-specific licensing and policies.']]],
];$content=$pages[$page];
@endphp
@section('title',$content[0].' — Tesla Markets')
@section('content')
<section class="legal-hero"><span class="eyebrow">TESLA MARKETS / INFORMATION</span><h1>{{ $content[0] }}</h1><p>{{ $content[1] }}</p></section>
<section class="legal-card legal-content glass">@foreach($content[2] as $index=>$section)<article><span>{{ str_pad($index+1,2,'0',STR_PAD_LEFT) }}</span><div><h2>{{ $section[0] }}</h2><p>{{ $section[1] }}</p></div></article>@endforeach<div class="legal-meta"><span>Last updated</span><b>August 13, 2026</b></div>@if($page==='contact')<a class="button" href="mailto:support@teslamarkets.test">Email support</a>@elseif($page==='help')<a class="button" href="{{ route('assistant.index') }}">Ask Grok Assistant</a>@endif</section>
@endsection
