<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Author by Lab | zefry. Existing catalog and sale facts default to their original tracked behavior.
return new class extends Migration {
 public function up():void {
  Schema::table('oneqay_pos_sale_catalog_items',static function(Blueprint $t):void{$t->boolean('tracks_stock')->default(true);});
  Schema::table('oneqay_pos_sale_lines',static function(Blueprint $t):void{$t->boolean('stock_deducted')->default(true);});
 }
 public function down():void {throw new RuntimeException('Preserve stock mode and historical sale effects; rollback application code only.');}
};
