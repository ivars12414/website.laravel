<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('default_controller')->nullable();
            $table->boolean('requires_auth')->default(false);
            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();
            $table->string('default_h1')->nullable();
            $table->json('meta_extra')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sections'); }
};
