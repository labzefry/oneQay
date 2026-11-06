<?php
declare(strict_types=1);
// Author by Lab | zefry. Additive discriminator: existing browser and paired native sessions retain their TTL.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('oneqay_identity_first_party_sessions',function(Blueprint $t):void {
   $t->string('authority_kind',24)->default('browser');
  });
 }
 public function down():void {throw new LogicException('Forward-only migration; revoke persistent grants before application rollback.');}
};
