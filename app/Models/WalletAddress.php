<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WalletAddress extends Model {protected $fillable=['user_id','asset','network','address_encrypted','address_hash','purpose','is_active','verified_at'];protected function casts():array{return ['address_encrypted'=>'encrypted','is_active'=>'boolean','verified_at'=>'datetime'];}public function getMaskedAddressAttribute():string{$v=$this->address_encrypted;return strlen($v)>14?substr($v,0,6).'…'.substr($v,-6):$v;} }
