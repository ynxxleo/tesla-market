<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class OtpService
{
    private const SESSION_KEY = 'otp_challenge';

    public function send(Request $request, string $purpose, User $user, array $payload = []): void
    {
        $code = (string) random_int(100000, 999999);

        $challenge = [
            'purpose' => $purpose,
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'payload' => $payload,
        ];

        try {
            Mail::to($user)->send(new OtpMail($code, $purpose));
        } catch (TransportExceptionInterface|InvalidArgumentException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'We could not send your verification code. Please try again shortly.',
            ]);
        }

        // A failed resend must leave the previously delivered code usable.
        $request->session()->put(self::SESSION_KEY, $challenge);
    }

    public function challenge(Request $request, string $purpose): ?array
    {
        $challenge = $request->session()->get(self::SESSION_KEY);

        if (! is_array($challenge) || ($challenge['purpose'] ?? null) !== $purpose) {
            return null;
        }

        return $challenge;
    }

    public function verify(Request $request, string $purpose, string $code): ?array
    {
        $challenge = $this->challenge($request, $purpose);

        if (! $challenge || ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $this->clear($request);

            return null;
        }

        $challenge['attempts'] = ($challenge['attempts'] ?? 0) + 1;

        if ($challenge['attempts'] > 5) {
            $this->clear($request);

            return null;
        }

        if (! Hash::check($code, $challenge['code_hash'])) {
            $request->session()->put(self::SESSION_KEY, $challenge);

            return null;
        }

        $this->clear($request);

        return $challenge;
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
