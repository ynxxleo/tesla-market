<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Investment;
use App\Models\InvestmentPackage;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InvestmentController extends Controller
{
    public function index(Request $r)
    {
        $investments = $r->user()->investments()->latest()->get();
        $models = InvestmentPackage::catalog();
        $packages = $models->mapWithKeys(fn ($p) => [$p->slug => ['name' => $p->name, 'min' => (float) $p->minimum, 'max' => (float) $p->maximum, 'rate' => (float) $p->annual_rate, 'image' => $p->image, 'image_light' => $p->image_light]]);
        $total = (float) $investments->where('status', 'active')->sum('balance');

        return view('investments.index', compact('investments', 'packages') + ['eligibility' => $this->eligibility($total, $models)]);
    }

    public function store(Request $r, OtpService $otp)
    {
        [$package,$data,$amount] = $this->validateInvestment($r);
        if ((float) $r->user()->cash_balance < $amount) {
            throw ValidationException::withMessages(['amount' => 'Your available BALANCE is $'.number_format($r->user()->cash_balance, 2).'. Deposit more platform funds or choose a lower amount.']);
        }$code = $otp->issue($r, 'investment', $r->user()->id, ['plan' => $data['plan'], 'amount' => $amount]);
        Mail::to($r->user())->send(new OtpMail($code, 'investment'));

        return redirect()->route('investments.otp');
    }

    public function otp(Request $r, OtpService $otp)
    {
        $challenge = $otp->challenge($r, 'investment');
        if (! $challenge || ($challenge['user_id'] ?? null) !== $r->user()->id) {
            return redirect()->route('investments.index');
        }

return view('investments.otp', ['maskedEmail' => $this->maskEmail($r->user()->email)]);
    }

    public function verifyOtp(Request $r, OtpService $otp)
    {
        $data = $r->validate(['code' => 'required|digits:6']);
        $challenge = $otp->verify($r, 'investment', $data['code']);
        if (! $challenge || ($challenge['user_id'] ?? null) !== $r->user()->id) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has exceeded the attempt limit.']);
        }$pending = $challenge['payload'];
        $package = InvestmentPackage::catalog()->firstWhere('slug', $pending['plan'] ?? null);
        if (! $package) {
            throw ValidationException::withMessages(['code' => 'This investment package is no longer available.']);
        }$amount = (float) ($pending['amount'] ?? 0);
        if ($amount < (float) $package->minimum || $amount > (float) $package->maximum) {
            throw ValidationException::withMessages(['code' => 'The package terms changed. Start the investment again.']);
        }DB::transaction(function () use ($r, $amount, $package, $pending) {
            $user = User::lockForUpdate()->findOrFail($r->user()->id);
            if ((float) $user->cash_balance < $amount) {
                throw ValidationException::withMessages(['code' => 'Your available balance changed. Start the investment again.']);
            }$user->decrement('cash_balance', $amount);
            Investment::create(['user_id' => $user->id, 'plan' => $pending['plan'], 'principal' => $amount, 'balance' => $amount, 'annual_rate' => $package->annual_rate, 'started_at' => now(), 'last_accrued_at' => now()]);
        });

        return redirect()->route('investments.index')->with('status', "{$package->name} package added to your portfolio.");
    }

    private function validateInvestment(Request $r): array
    {
        $package = InvestmentPackage::catalog()->firstWhere('slug', $r->input('plan'));
        if (! $package) {
            throw ValidationException::withMessages(['plan' => 'Select an available package.']);
        }$data = $r->validate(['plan' => 'required|string', 'amount' => 'required|numeric']);
        $amount = (float) $data['amount'];
        if ($amount < (float) $package->minimum || $amount > (float) $package->maximum) {
            throw ValidationException::withMessages(['amount' => "The {$package->name} package accepts $".number_format((float) $package->minimum).'–$'.number_format((float) $package->maximum).'.']);
        }

return [$package, $data, $amount];
    }

    private function maskEmail(string $email): string
    {
        [$name,$domain] = explode('@', $email, 2);

        return substr($name, 0, 1).str_repeat('•', max(2, strlen($name) - 1)).'@'.$domain;
    }

    private function eligibility(float $total, $packages): array
    {
        $levels = $packages->map(fn ($p) => ['name' => $p->name, 'amount' => (float) $p->minimum])->values()->all();
        $current = 'Explorer';
        $next = $levels[0] ?? null;
        foreach ($levels as $i => $level) {
            if ($total >= $level['amount']) {
                $current = $level['name'];
                $next = $levels[$i + 1] ?? null;
            }
        }$progress = $next ? min(100, ($total / $next['amount']) * 100) : 100;

        return compact('total','current','next','progress');
    }
}
