<?php
namespace App\Console\Commands;
use App\Models\Investment;
use Illuminate\Console\Command;
class AccrueInvestmentReturns extends Command {
 protected $signature='investments:accrue'; protected $description='Accrue disclosed simulated daily investment returns';
 public function handle(): int {Investment::where('status','active')->chunkById(100,function($items){foreach($items as $i){$from=$i->last_accrued_at??$i->started_at;$days=$from->startOfDay()->diffInDays(now()->startOfDay());if($days<1)continue;$daily=pow(1+(float)$i->annual_rate/100,1/365)-1;$i->update(['balance'=>round((float)$i->balance*pow(1+$daily,$days),2),'last_accrued_at'=>now()]);}});$this->info('Simulated returns accrued.');return self::SUCCESS;}
}
