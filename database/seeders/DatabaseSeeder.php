<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@teslamarkets.test')], [
            'name' => env('ADMIN_NAME', 'Platform Administrator'),
            'password' => env('ADMIN_PASSWORD', 'ChangeMe!2026'),
            'is_admin' => true,
            'cash_balance' => 0,
        ]);
        \App\Models\RegulatoryLicense::firstOrCreate([
            'jurisdiction' => 'UNCONFIGURED',
            'license_type' => 'Virtual asset / securities authorization',
        ], ['status' => 'planning', 'live_operations_allowed' => false]);
        \App\Models\InvestmentPackage::catalog();
        \App\Models\SystemSetting::wire();
    }
}
