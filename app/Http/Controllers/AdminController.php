<?php
namespace App\Http\Controllers;
use App\Models\{Conversation,Investment,User};
use Illuminate\Http\Request;
class AdminController extends Controller {
 public function index(){return view('admin.dashboard',['users'=>User::where('is_admin',false)->latest()->limit(8)->get(),'conversations'=>Conversation::with('user')->latest()->limit(12)->get(),'invested'=>Investment::where('status','active')->sum('balance')]);}
 public function conversation(Conversation $conversation){return view('admin.conversation',['conversation'=>$conversation->load('messages.user','user')]);}
 public function reply(Request $r,Conversation $conversation){$data=$r->validate(['message'=>'required|string|max:3000']);$conversation->messages()->create(['user_id'=>$r->user()->id,'author_type'=>'human_admin','body'=>$data['message']]);$conversation->update(['status'=>'admin_joined','assigned_admin_id'=>$r->user()->id]);return back()->with('status','Human-admin reply sent and labeled.');}
}
