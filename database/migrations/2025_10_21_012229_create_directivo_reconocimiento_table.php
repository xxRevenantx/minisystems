<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('directivo_reconocimiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reconocimiento_id')->nullable();
            $table->unsignedBigInteger('directivo_id')->nullable();


            $table->foreign('reconocimiento_id')->references('id')->on('reconocimientos')->cascadeOnDelete();
            $table->foreign('directivo_id')->references('id')->on('directivos')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directivo_reconocimiento');
    }
};
