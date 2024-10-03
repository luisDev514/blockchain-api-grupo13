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
        Schema::create('aplicacion', function (Blueprint $table) {
            $table->id('aplicacion_id');
            $table->string('codigo',10);
            $table->string('nombre',100);
            $table->string('titulo',50)->nullable();
            $table->string('icono',20)->nullable();
            $table->string('url',50)->nullable();
            $table->string('descripcion',100)->nullable();
            $table->string('area',50)->nullable();
            $table->string('base_datos',50)->nullable();
            $table->string('ip_servidor',20)->nullable();
            $table->string('version',20);
            $table->boolean('habilitado');
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
        Schema::table('aplicacion', function (Blueprint $table) {
            Schema::dropIfExists('aplicacion');
            
        });
    }
};
