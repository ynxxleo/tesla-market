<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
class FinanceNewsService{
 private const FEED='https://www.cnbc.com/id/10000664/device/rss/rss.html';
 public function latest():array{return Cache::remember('finance-news-feed',now()->addMinutes(15),fn()=>$this->fetch());}
 private function fetch():array{try{$response=Http::timeout(8)->retry(2,250)->withHeaders(['User-Agent'=>'TeslaMarketsEducationalReader/1.0'])->get(self::FEED)->throw();$xml=simplexml_load_string($response->body(),'SimpleXMLElement',LIBXML_NONET|LIBXML_NOCDATA);if(!$xml)throw new \RuntimeException('Invalid news feed.');$items=[];foreach($xml->channel->item??[] as $item){$url=(string)$item->link;$host=strtolower(parse_url($url,PHP_URL_HOST)??'');if(!str_ends_with($host,'cnbc.com'))continue;$items[]=['title'=>trim((string)$item->title),'url'=>$url,'summary'=>Str::limit(trim(strip_tags((string)$item->description)),230),'published'=>($date=trim((string)$item->pubDate))?date('c',strtotime($date)):null,'source'=>'CNBC'];if(count($items)>=18)break;}if(!$items)throw new \RuntimeException('Empty news feed.');Cache::forever('finance-news-last-good',$items);return $items;}catch(\Throwable $e){report($e);return Cache::get('finance-news-last-good',$this->fallback());}}
 private function fallback():array{return [['title'=>'Financial news is temporarily unavailable','url'=>'https://www.cnbc.com/finance/','summary'=>'The external publisher could not be reached. Open the source directly or check again shortly.','published'=>null,'source'=>'CNBC']];}
}
