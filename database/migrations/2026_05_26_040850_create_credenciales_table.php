<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credenciales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('matricula');
            $table->string('curp');
            $table->string('nivel');
            $table->string('grado')->nullable();
            $table->string('grupo')->nullable();
            $table->string('licenciatura')->nullable();
            $table->string('ciclo_escolar')->nullable();
            $table->string('vigencia')->nullable();
            $table->string('telefono')->nullable();
            $table->string('domicilio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credenciales');
    }
};
