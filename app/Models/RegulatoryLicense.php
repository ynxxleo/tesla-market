<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RegulatoryLicense extends Model {protected $fillable=['jurisdiction','license_type','license_number','status','effective_at','expires_at','live_operations_allowed'];protected function casts():array{return ['effective_at'=>'date','expires_at'=>'date','live_operations_allowed'=>'boolean'];}}
