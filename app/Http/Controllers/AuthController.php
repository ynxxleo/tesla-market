<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $r, OtpService $otp)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::validate($data)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }$user = User::where('email', $data['email'])->firstOrFail();
        if ($user->isLocked()) {
            return back()->withErrors(['email' => 'This account is restricted. Contact compliance support.']);
        }$r->session()->regenerate();
        if ($user->is_admin) {
            Auth::login($user, $r->boolean('remember'));

            return redirect()->intended(route('admin.dashboard'));
        }
        $code = $otp->issue($r, 'login', $user->id, ['remember' => $r->boolean('remember')]);
        Mail::to($user)->send(new OtpMail($code, 'login'));

        return redirect()->route('auth.otp');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function store(Request $r, OtpService $otp)
    {
        $data = $r->validate(['name' => 'required|string|max:80', 'email' => 'required|email|unique:users', 'password' => 'required|min:8|confirmed']);
        $user = User::create($data);
        $r->session()->regenerate();
        $code = $otp->issue($r, 'signup', $user->id);
        Mail::to($user)->send(new OtpMail($code, 'signup'));

        return redirect()->route('auth.otp');
    }

    public function otp(Request $r, OtpService $otp)
    {
        $challenge = $r->session()->get('otp_challenge');
        if (! is_array($challenge) || ! in_array($challenge['purpose'] ?? null, ['login', 'signup'], true)) {
            return redirect()->route('login');
        }$user = User::find($challenge['user_id']);
        if (! $user) {
            return redirect()->route('login');
        }

return view('auth.otp', ['maskedEmail' => $this->maskEmail($user->email)]);
    }

    public function verifyOtp(Request $r, OtpService $otp)
    {
        $data = $r->validate(['code' => 'required|digits:6']);
        $pending = $r->session()->get('otp_challenge');
        $purpose = $pending['purpose'] ?? '';
        if (! in_array($purpose, ['login', 'signup'], true) || ! ($challenge = $otp->verify($r, $purpose, $data['code']))) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has exceeded the attempt limit.']);
        }$user = User::findOrFail($challenge['user_id']);
        if ($user->isLocked()) {
            return redirect()->route('login')->withErrors(['email' => 'This account is restricted. Contact compliance support.']);
        }if ($purpose === 'signup') {
            $user->forceFill(['email_verified_at' => now()])->save();
            Mail::to($user)->send(new WelcomeMail($user));
        }Auth::login($user, (bool) ($challenge['payload']['remember'] ?? false));
        $r->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function resendOtp(Request $r, OtpService $otp)
    {
        $challenge = $r->session()->get('otp_challenge');
        $purpose = $challenge['purpose'] ?? '';
        if (! in_array($purpose, ['login', 'signup'], true) || ! ($user = User::find($challenge['user_id'] ?? null))) {
            return redirect()->route('login');
        }$code = $otp->issue($r, $purpose, $user->id, $challenge['payload'] ?? []);
        Mail::to($user)->send(new OtpMail($code, $purpose));

        return back()->with('status', 'A new verification code was sent.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function maskEmail(string $email): string
    {
        [$name,$domain] = explode('@', $email, 2);

        return substr($name, 0, 1).str_repeat('•',max(2,strlen($name) - 1)).'@'.$domain;
    }
}
