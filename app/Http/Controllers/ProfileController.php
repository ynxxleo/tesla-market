<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class ProfileController extends Controller{
 public function show(Request $r){return view('profile.show',['user'=>$r->user()]);}
 public function update(Request $r){$data=$r->validate(['name'=>'required|string|max:80','email'=>['required','email',Rule::unique('users')->ignore($r->user()->id)],'avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096','current_password'=>'nullable|required_with:password|current_password','password'=>'nullable|min:8|confirmed']);$changes=['name'=>$data['name'],'email'=>$data['email']]+(filled($data['password']??null)?['password'=>Hash::make($data['password'])]:[]);if($r->hasFile('avatar')){$old=$r->user()->avatar_path;$changes['avatar_path']=$r->file('avatar')->store('avatars','public');if($old)Storage::disk('public')->delete($old);}$r->user()->update($changes);return back()->with('status','Profile updated.');}
}
