<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LedgerEntry extends Model {protected $fillable=['user_id','funding_request_id','direction','amount','balance_after','event','idempotency_key'];protected function casts():array{return ['amount'=>'decimal:2','balance_after'=>'decimal:2'];}}
