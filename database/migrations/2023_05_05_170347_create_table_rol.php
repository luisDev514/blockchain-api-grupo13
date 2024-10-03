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
        Schema::create('rol', function (Blueprint $table) {
            $table->id('rol_id');
            $table->string('nombre', 50);
            $table->boolean('habilitado');
            $table->unsignedBigInteger('aplicacion_id')->nullable();
            $table->foreign('aplicacion_id')->references('aplicacion_id')->on('aplicacion');
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
        Schema::table('rol', function (Blueprint $table) {
            Schema::dropIfExists('rol');
        });
    }
};
