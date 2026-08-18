<?php
namespace App\Console\Commands;
use App\Models\FundingRequest;
use Illuminate\Console\Command;
class ReconcileFunding extends Command {
 protected $signature='funding:reconcile';protected $description='Verify finalized platform funding requests have matching ledger events';
 public function handle():int{$mismatches=0;FundingRequest::where('status','approved')->withCount('checks')->chunkById(100,function($items)use(&$mismatches){foreach($items as $f){$key=$f->type==='deposit'?"funding:{$f->id}:settled":"funding:{$f->id}:hold";$ledger=\App\Models\LedgerEntry::where('idempotency_key',$key)->exists();$compliance=$f->checks()->where('result','clear')->exists();if(!$ledger||!$compliance){$mismatches++;$this->error("Mismatch {$f->id}: ledger=".($ledger?'yes':'no').', compliance='.($compliance?'yes':'no'));}}});if($mismatches){$this->error("{$mismatches} reconciliation mismatch(es).");return self::FAILURE;}$this->info('Funding ledger and compliance records reconcile.');return self::SUCCESS;}
}
