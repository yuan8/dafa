<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tamu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tamu', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nama');
            $table->string('string_id')->unique();
            $table->boolean('jenis_kelamin')->default(false);
            $table->text('golongan_darah')->nullable();
            $table->string('nomer_telpon')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->text('alamat')->nullable();
            $table->text('provinsi')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->boolean('tamu_khusus')->default(false);
            $table->string('jenis_tamu_khusus')->nullable();
            $table->boolean('izin_akses_masuk')->default(true);
            $table->text('keterangan_tolak_izin_akses')->nullable();
            $table->string('def_jenis_identity')->nullable();
            $table->string('def_kategori_tamu')->nullable();
            $table->string('def_instansi')->nullable();
            $table->mediumText('def_tujuan')->nullable();
            $table->mediumText('def_keperluan')->nullable();
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
        //
        Schema::dropIfExists('tamu');

    }
}
