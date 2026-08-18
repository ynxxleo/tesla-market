<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users',fn(Blueprint $t)=>$t->boolean('is_admin')->default(false)->index());
  Schema::table('users',fn(Blueprint $t)=>$t->decimal('cash_balance',16,2)->default(100000));
  Schema::create('trades',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('symbol',10)->index();$t->enum('side',['buy','sell']);$t->decimal('quantity',16,4);$t->decimal('price',16,2);$t->decimal('total',16,2);$t->timestamp('executed_at');$t->timestamps();});
  Schema::create('investments',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('plan');$t->decimal('principal',16,2);$t->decimal('balance',16,2);$t->decimal('annual_rate',7,4);$t->enum('status',['active','closed'])->default('active');$t->timestamp('started_at');$t->timestamp('last_accrued_at')->nullable();$t->timestamps();});
  Schema::create('conversations',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('subject')->default('Risk discussion');$t->decimal('risk_budget',16,2)->nullable();$t->enum('status',['automated','escalated','admin_joined','closed'])->default('automated');$t->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('messages',function(Blueprint $t){$t->id();$t->foreignId('conversation_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->enum('author_type',['user','assistant','human_admin']);$t->text('body');$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('messages');Schema::dropIfExists('conversations');Schema::dropIfExists('investments');Schema::dropIfExists('trades');Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['is_admin','cash_balance']));}
};
