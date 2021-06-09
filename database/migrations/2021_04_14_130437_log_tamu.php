<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LogTamu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('log_tamu', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tamu_id')->unsigned();
            $table->string('jenis_id')->nullable();
            
            // $table->dateTime('provos_checkin');
            // $table->bigInteger('provos_handle');

            $table->string('foto_checkin')->nullable();
            $table->mediumText('tujuan')->nullable();
            $table->mediumText('keperluan')->nullable();
            $table->string('kategori_tamu')->nullable();
            $table->text('instansi')->nullable();
            $table->dateTime('gate_checkin')->nullable();
            $table->bigInteger('gate_handle')->nullable();
            $table->dateTime('gate_checkout')->nullable();
            $table->bigInteger('gate_out_handle')->nullable();
            $table->boolean('checkout_from_gate')->nullable();
            $table->bigInteger('gate_checkout_handle')->nullable();
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
        Schema::dropIfExists('log_tamu');

    }
}
