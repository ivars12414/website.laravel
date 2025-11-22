<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
//        Schema::create('langs', function (Blueprint $table) {
//            $table->id();
//            $table->string('code', 5)->unique();
//            $table->string('name');
//            $table->tinyInteger('status')->default(1);
//            $table->boolean('main')->default(false);
//        });
    }

    public function down(): void
    {
        Schema::dropIfExists('langs');
    }
};
