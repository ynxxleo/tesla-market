<?php
namespace App\Services;
use App\Models\{ComplianceCheck,FundingRequest,User};
use Illuminate\Validation\ValidationException;
class ComplianceService {
 public function assertFundingEligible(User $user):void{
  if(config('funding.mode')!=='sandbox'||config('funding.live_crypto_transfers')) throw ValidationException::withMessages(['funding'=>'Funding is unavailable while the custody adapter is being reviewed.']);
  if($user->kyc_status!=='verified') throw ValidationException::withMessages(['funding'=>'Complete identity verification before making a funding request.']);
  if($user->sanctions_status!=='clear') throw ValidationException::withMessages(['funding'=>'Your compliance screening must be cleared before funding.']);
  if($user->isLocked()) throw ValidationException::withMessages(['funding'=>'This account is restricted. Contact support for a review.']);
 }
 public function screenTransaction(FundingRequest $funding):ComplianceCheck{$score=$this->riskScore($funding);$result=$score>=80?'blocked':($score>=50?'possible_match':'clear');return ComplianceCheck::create(['user_id'=>$funding->user_id,'funding_request_id'=>$funding->id,'type'=>'transaction_monitoring','provider'=>'sandbox_rules_v1','result'=>$result,'risk_score'=>$score,'details'=>['rules'=>['large_amount'=>$funding->usd_amount>=10000,'rapid_request'=>$funding->user->fundingRequests()->where('created_at','>=',now()->subHour())->count()>3]],'completed_at'=>now()]);}
 private function riskScore(FundingRequest $f):int{$score=0;if((float)$f->usd_amount>=10000)$score+=45;if((float)$f->usd_amount>=50000)$score+=35;if($f->user->fundingRequests()->where('created_at','>=',now()->subHour())->count()>3)$score+=30;return min($score,100);}
}
