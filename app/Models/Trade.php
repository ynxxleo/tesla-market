<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Trade extends Model {
    protected $fillable=['user_id','symbol','side','quantity','price','total','executed_at'];
    protected function casts(): array { return ['quantity'=>'decimal:4','price'=>'decimal:2','total'=>'decimal:2','executed_at'=>'datetime']; }
    public function user(){ return $this->belongsTo(User::class); }
}
