<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Identity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('identity_tamu', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tamu_id')->unsigned();
            $table->string('identity_number')->nullable();
            $table->string('jenis_identity');
            $table->date('berlaku_hingga')->nullable();            
            $table->string('path_identity')->nullable();
            $table->timestamps();
            $table->unique(['tamu_id','identity_number','jenis_identity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('identity_tamu');

    }
}
