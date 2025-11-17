<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lang_id')->constrained('langs');
            $table->string('hash')->unique();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('name2')->nullable();
            $table->unsignedTinyInteger('position')->default(\App\Models\Section::POSITION_HEADER);
            $table->tinyInteger('status')->default(1);
            $table->string('default_controller')->nullable();
            $table->boolean('requires_auth')->default(false);
            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();
            $table->string('default_h1')->nullable();
            $table->json('meta_extra')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('sections'); }
};
