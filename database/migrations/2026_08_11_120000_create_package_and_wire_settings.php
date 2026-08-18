<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::create('investment_packages',function(Blueprint $t){$t->id();$t->string('slug')->unique();$t->string('name');$t->decimal('minimum',16,2);$t->decimal('maximum',16,2);$t->decimal('annual_rate',7,4);$t->string('image');$t->string('image_light');$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('system_settings',function(Blueprint $t){$t->id();$t->string('key')->unique();$t->json('value');$t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::table('funding_requests',function(Blueprint $t){$t->string('method',20)->default('crypto')->after('type');$t->json('wire_details_snapshot')->nullable();$t->text('destination_details')->nullable();});
 }
 public function down():void{Schema::table('funding_requests',fn(Blueprint $t)=>$t->dropColumn(['method','wire_details_snapshot','destination_details']));Schema::dropIfExists('system_settings');Schema::dropIfExists('investment_packages');}
};
