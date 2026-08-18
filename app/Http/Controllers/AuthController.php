<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function login(){return view('auth.login');}
 public function authenticate(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required']);if(Auth::attempt($data,$r->boolean('remember'))){if($r->user()->isLocked()){Auth::logout();return back()->withErrors(['email'=>'This account is restricted. Contact compliance support.']);}$r->session()->regenerate();return redirect()->intended(route('dashboard'));}return back()->withErrors(['email'=>'Invalid credentials.'])->onlyInput('email');}
 public function register(){return view('auth.register');}
 public function store(Request $r){$data=$r->validate(['name'=>'required|string|max:80','email'=>'required|email|unique:users','password'=>'required|min:8|confirmed']);$user=User::create($data);Auth::login($user);$r->session()->regenerate();return redirect()->route('dashboard');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');}
}
