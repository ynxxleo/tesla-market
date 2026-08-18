<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureAccountActive {public function handle(Request $r,Closure $next){if($r->user()?->isLocked()){auth()->logout();$r->session()->invalidate();return redirect()->route('login')->withErrors(['email'=>'This account is restricted. Contact compliance support.']);}return $next($r);}}
