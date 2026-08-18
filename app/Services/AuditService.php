<?php
namespace App\Services;
use App\Models\AuditLog;
use Illuminate\Http\Request;
class AuditService {public function record(string $event,object $subject,array $before=[],array $after=[],?Request $request=null):void{AuditLog::create(['actor_id'=>auth()->id(),'event'=>$event,'auditable_type'=>$subject::class,'auditable_id'=>(string)$subject->getKey(),'before'=>$before?:null,'after'=>$after?:null,'ip_address'=>$request?->ip(),'user_agent'=>substr((string)$request?->userAgent(),0,500)]);}}
