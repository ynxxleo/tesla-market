<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InvestmentPackage extends Model{
 protected $fillable=['slug','name','minimum','maximum','annual_rate','image','image_light','sort_order','is_active'];
 protected function casts():array{return ['minimum'=>'decimal:2','maximum'=>'decimal:2','annual_rate'=>'decimal:4','is_active'=>'boolean'];}
 public static function defaults():array{return [
  ['slug'=>'model_3','name'=>'Model 3','minimum'=>500,'maximum'=>4999,'annual_rate'=>4.25,'image'=>'images/packages/model-3.png','image_light'=>'images/packages/model-3-light.png','sort_order'=>1],
  ['slug'=>'model_y','name'=>'Model Y','minimum'=>5000,'maximum'=>24999,'annual_rate'=>7,'image'=>'images/packages/model-y.png','image_light'=>'images/packages/model-y-light.png','sort_order'=>2],
  ['slug'=>'model_x','name'=>'Model X','minimum'=>25000,'maximum'=>199999,'annual_rate'=>10.5,'image'=>'images/packages/model-x.png','image_light'=>'images/packages/model-x-light.png','sort_order'=>3],
  ['slug'=>'cybertruck','name'=>'Cybertruck','minimum'=>200000,'maximum'=>1000000,'annual_rate'=>13.75,'image'=>'images/packages/cybertruck.png','image_light'=>'images/packages/cybertruck-light.png','sort_order'=>4],
 ];}
 public static function catalog(){foreach(self::defaults() as $row)self::firstOrCreate(['slug'=>$row['slug']],$row);return self::where('is_active',true)->orderBy('sort_order')->get();}
}
