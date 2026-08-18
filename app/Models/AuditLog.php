<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {protected $fillable=['actor_id','event','auditable_type','auditable_id','before','after','ip_address','user_agent'];protected function casts():array{return ['before'=>'array','after'=>'array'];}}
