<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class KycProfile extends Model{protected $fillable=['user_id','legal_name','date_of_birth','country_code','document_type','document_path','document_sha256'];protected function casts():array{return ['date_of_birth'=>'date'];}public function user(){return $this->belongsTo(User::class);}}
