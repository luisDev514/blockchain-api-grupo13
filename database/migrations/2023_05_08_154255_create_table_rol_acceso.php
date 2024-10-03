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
        Schema::create('rol_acceso', function (Blueprint $table) {
            $table->id('acceso_id');
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->foreign('rol_id')->references('rol_id')->on('rol');
            $table->unsignedBigInteger('modulo');
            $table->foreign('modulo')->references('modulo_id')->on('modulo');
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
        Schema::table('rol_acceso', function (Blueprint $table) {
            Schema::dropIfExists('rol_acceso');
            
        });
    }
};
