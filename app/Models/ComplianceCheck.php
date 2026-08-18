<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ComplianceCheck extends Model {protected $fillable=['user_id','funding_request_id','type','provider','result','risk_score','details','reviewed_by','completed_at'];protected function casts():array{return ['details'=>'array','completed_at'=>'datetime'];}}
