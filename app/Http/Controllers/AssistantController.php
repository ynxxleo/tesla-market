<?php
namespace App\Http\Controllers;
use App\Models\Conversation;
use App\Services\AssistantService;
use Illuminate\Http\Request;
class AssistantController extends Controller {
 public function index(Request $r){$conversation=$r->user()->conversations()->with('messages.user')->latest()->first();return view('assistant.index',compact('conversation'));}
 public function store(Request $r,AssistantService $assistant){$data=$r->validate(['message'=>'required|string|max:3000','risk_budget'=>'nullable|numeric|min:0|max:1000000']);$conversation=$r->user()->conversations()->latest()->firstOrCreate([],['subject'=>'Daily risk conversation']);if(isset($data['risk_budget']))$conversation->update(['risk_budget'=>$data['risk_budget']]);$userMessage=$conversation->messages()->create(['user_id'=>$r->user()->id,'author_type'=>'user','body'=>$data['message']]);$reply=$assistant->reply($conversation,$data['message']);if($r->expectsJson())return response()->json(['messages'=>[$this->format($userMessage),$this->format($reply)]]);return back();}
 public function messages(Request $r){$conversation=$r->user()->conversations()->latest()->first();if(!$conversation)return response()->json(['messages'=>[]]);$messages=$conversation->messages()->with('user')->where('id','>',$r->integer('after'))->orderBy('id')->get()->map(fn($m)=>$this->format($m));return response()->json(['messages'=>$messages,'status'=>$conversation->status]);}
 private function format($message):array{return ['id'=>$message->id,'author_type'=>$message->author_type,'label'=>$message->author_type==='human_admin'?'CUSTOMER SUPPORT · '.($message->user?->name??'Administrator'):($message->author_type==='assistant'?'GROK ASSISTANT · AI':'YOU'),'body'=>$message->body,'created_at'=>$message->created_at?->format('H:i')];}
}
