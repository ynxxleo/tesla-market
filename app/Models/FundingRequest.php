<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class FundingRequest extends Model {use HasUuids;protected $fillable=['user_id','type','method','asset','network','crypto_amount','usd_amount','status','wallet_address_id','tx_hash','confirmations','review_note','reviewed_by','reviewed_at','wire_details_snapshot','destination_details'];protected function casts():array{return ['crypto_amount'=>'decimal:12','usd_amount'=>'decimal:2','reviewed_at'=>'datetime','wire_details_snapshot'=>'array','destination_details'=>'encrypted'];}public function user(){return $this->belongsTo(User::class);}public function wallet(){return $this->belongsTo(WalletAddress::class,'wallet_address_id');}public function checks(){return $this->hasMany(ComplianceCheck::class);} }
