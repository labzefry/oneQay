<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Author by Lab | zefry. Immutable published revisions, separate editable drafts.
return new class extends Migration {
    public function up():void {
        Schema::create('oneqay_site_pages',function(Blueprint $t):void{$t->string('slug',64)->primary();$t->unsignedInteger('version');$t->longText('draft');$t->string('published_revision',32)->nullable();$t->unsignedBigInteger('updated_at');});
        Schema::create('oneqay_site_revisions',function(Blueprint $t):void{$t->string('id',32)->primary();$t->string('slug',64)->index();$t->longText('document');$t->string('actor',200);$t->unsignedBigInteger('created_at');});
        Schema::create('oneqay_site_media',function(Blueprint $t):void{$t->string('id',32)->primary();$t->string('mime',32);$t->unsignedInteger('bytes');$t->longText('payload');$t->string('actor',200);$t->unsignedBigInteger('created_at');});
        Schema::create('oneqay_site_registrations',function(Blueprint $t):void{$t->string('email_hash',64)->primary();$t->string('business_id',32)->unique();$t->string('phone',24);$t->string('business_type',32);$t->string('consent_version',32);$t->unsignedBigInteger('created_at');});
    }
    public function down():void {throw new RuntimeException('Content revisions and consent records require a reviewed retention migration.');}
};
