<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Conversation extends Model {
    protected $fillable=['user_id','subject','risk_budget','status','assigned_admin_id'];
    protected function casts(): array { return ['risk_budget'=>'decimal:2']; }
    public function user(){ return $this->belongsTo(User::class); }
    public function messages(){ return $this->hasMany(Message::class); }
    public function assignedAdmin(){ return $this->belongsTo(User::class,'assigned_admin_id'); }
}
