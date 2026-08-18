<?php
namespace App\Http\Controllers;
use App\Services\QuoteService;
use Illuminate\Http\Request;
class DashboardController extends Controller {
 public function __invoke(Request $r,QuoteService $quotes){$user=$r->user()->load('investments');$positions=$user->trades()->get()->groupBy('symbol')->map(fn($rows)=>$rows->sum(fn($t)=>($t->side==='buy'?1:-1)*(float)$t->quantity));$approvedDeposits=(float)$user->fundingRequests()->where('type','deposit')->where('status','approved')->sum('usd_amount');return view('dashboard',compact('user','positions','approvedDeposits')+['quotes'=>$quotes->symbols()]);}
}
