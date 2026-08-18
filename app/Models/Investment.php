<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Investment extends Model {
    protected $fillable=['user_id','plan','principal','balance','annual_rate','status','started_at','last_accrued_at'];
    protected function casts(): array { return ['principal'=>'decimal:2','balance'=>'decimal:2','annual_rate'=>'decimal:4','started_at'=>'datetime','last_accrued_at'=>'datetime']; }
    public function user(){ return $this->belongsTo(User::class); }
}
