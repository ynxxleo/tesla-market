<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SystemSetting extends Model{
 protected $fillable=['key','value','updated_by'];protected function casts():array{return ['value'=>'array'];}
 public static function wire():self{return self::firstOrCreate(['key'=>'wire_transfer'],['value'=>['bank_name'=>'Tesla Markets Test Bank','account_name'=>'Tesla Markets Operations','account_number'=>'0019283746','routing_number'=>'021000021','swift_code'=>'TESLUS33','bank_address'=>'1 Market Plaza, New York, NY','reference_prefix'=>'TM'],'updated_by'=>null]);}
}
