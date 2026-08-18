<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('kyc_profiles',function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->string('legal_name');$t->date('date_of_birth');$t->char('country_code',2)->index();$t->string('document_type');$t->string('document_path');$t->string('document_sha256',64);$t->timestamps();});}public function down():void{Schema::dropIfExists('kyc_profiles');}};
