<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id('persona_id');
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50)->nullable();
            $table->string('nombre_completo', 150)->nullable();
            $table->string('ci', 12)->nullable();
            $table->string('ci_origen', 3)->nullable();
            $table->string('ci_extension', 3)->nullable();
            $table->unsignedBigInteger('codigo')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('correo', 30)->nullable();
            $table->string('telefono', 12)->nullable();
            $table->boolean('habilitado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('persona', function (Blueprint $table) {
            Schema::dropIfExists('persona');
        });
    }
};
