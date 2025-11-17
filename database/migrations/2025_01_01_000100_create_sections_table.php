<?php

use App\Models\Section;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lang_id')->constrained('langs');
            $table->string('code')->unique();
            $table->string('hash')->unique();
            $table->string('name');
            $table->string('name2')->nullable();
            $table->unsignedTinyInteger('position')->default(Section::POSITION_SYSTEM);
            $table->boolean('requires_auth')->default(false);
            $table->boolean('status')->default(true);
            $table->string('default_controller')->nullable();
            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();
            $table->string('default_h1')->nullable();
            $table->json('meta_extra')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('sections'); }
};
