<?php
namespace App\Services;
class QuoteService {
 private const QUOTES=['TSLA'=>445.91,'NVDA'=>182.12,'AAPL'=>226.94,'MSFT'=>527.75,'AMZN'=>222.69,'BTC'=>118420.35,'ETH'=>4218.67,'SOL'=>196.42,'XRP'=>3.21];
 public function symbols(): array { return self::QUOTES; }
 public function cryptoSymbols(): array { return array_intersect_key(self::QUOTES,array_flip(['BTC','ETH','SOL','XRP'])); }
 public function assetType(string $symbol):string{return in_array(strtoupper($symbol),['BTC','ETH','SOL','XRP'])?'crypto':'stock';}
 public function history(string $symbol,int $points=48):array{$price=$this->price($symbol);$seed=array_sum(array_map('ord',str_split(strtoupper($symbol))));$values=[];for($i=0;$i<$points;$i++){$wave=sin(($i+$seed)/4.3)*.018+cos(($i+$seed)/9.1)*.011;$trend=($i-$points)*.0007;$values[]=round($price*(1+$wave+$trend),2);}return $values;}
 public function price(string $symbol): float { $symbol=strtoupper($symbol); abort_unless(isset(self::QUOTES[$symbol]),422,'Unsupported symbol.'); return self::QUOTES[$symbol]; }
}
