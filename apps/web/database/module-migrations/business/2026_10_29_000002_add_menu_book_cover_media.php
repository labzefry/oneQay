<?php
// Author by Lab | zefry. Private, scoped media; no filesystem public upload directory.
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('oneqay_menu_book_media',static function(Blueprint $t):void {
   $t->string('tenant_id',64);$t->string('organization_id',64);$t->string('outlet_id',64);
   $t->char('id',32);$t->char('sha256',64);$t->string('mime',32);$t->unsignedInteger('bytes');$t->mediumText('payload');$t->unsignedBigInteger('created_at');
   $t->primary(['tenant_id','organization_id','outlet_id','id'],'pk_menu_book_media');
   $t->foreign(['tenant_id','organization_id'],'fk_menu_media_org')->references(['tenant_id','id'])->on('oneqay_organizations')->restrictOnDelete()->restrictOnUpdate();
   $t->foreign(['tenant_id','outlet_id'],'fk_menu_media_outlet')->references(['tenant_id','id'])->on('oneqay_outlets')->restrictOnDelete()->restrictOnUpdate();
  });
  Schema::table('oneqay_menu_book_publication',static function(Blueprint $t):void {$t->char('cover_id',32)->nullable();});
 }
 public function down():void {throw new RuntimeException('Preserve menu media and published snapshots; use a reviewed forward repair.');}
};
