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
        Schema::create('reconocimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reconocimiento_imagen_id')->nullable();
            $table->string('reconocimiento_a')->nullable();
            $table->string('lugar_obtenido')->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha')->nullable();


            $table->foreign('reconocimiento_imagen_id')->references('id')->on('reconocimiento_imagenes')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconocimientos');
    }
};
