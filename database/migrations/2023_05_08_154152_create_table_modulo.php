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
        Schema::create('modulo', function (Blueprint $table) {
            $table->id('modulo_id');
            $table->unsignedBigInteger('aplicacion_id')->nullable();
            $table->foreign('aplicacion_id')->references('aplicacion_id')->on('aplicacion')->nullable();
            $table->unsignedBigInteger('modulo_padre')->nullable();
            $table->foreign('modulo_padre')->references('modulo_id')->on('modulo')->nullable();
            $table->string('url', 50);
            $table->string('titulo', 100);
            $table->string('nombre', 50);
            $table->string('icono', 20);
            $table->boolean('menu')->default(0);
            $table->unsignedBigInteger('habilitado')->nullable()->default(0);
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
        Schema::table('modulo', function (Blueprint $table) {
            Schema::dropIfExists('modulo');
        });
    }
};
