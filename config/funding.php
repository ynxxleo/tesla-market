<?php

return [
    'mode' => env('FUNDING_MODE', 'sandbox'),
    'live_crypto_transfers' => (bool) env('LIVE_CRYPTO_TRANSFERS', false),
    'wallets' => [
        'BTC' => ['network' => 'bitcoin', 'address' => 'tb1qtesla7k4m9p2x8c6v3n5r0w4y7z9s2d6f8g1h3'],
        'ETH' => ['network' => 'ethereum', 'address' => '0x7A3f4C91e2B6D8a05F1c9E47b3A6d2F8C5e1B904'],
        'USDC' => ['network' => 'polygon', 'address' => '0x91D6b4A8c3E75F02a9B1d8C64E3f7A05b2D9C816'],
        'USDT' => ['network' => 'trc20', 'address' => 'TX7kM9pQ4vN2cR8wF5dH3sL6aB1eG9yZtC'],
    ],
];
