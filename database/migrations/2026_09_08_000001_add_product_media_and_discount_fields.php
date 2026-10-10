<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table("products", function(Blueprint $table){ $table->unsignedTinyInteger("discount_percent")->default(0); $table->string("video_path")->nullable(); $table->enum("video_status", ["none","pending","approved","rejected"])->default("none"); }); }
 public function down(): void { Schema::table("products", function(Blueprint $table){ $table->dropColumn(["discount_percent","video_path","video_status"]); }); }
};
