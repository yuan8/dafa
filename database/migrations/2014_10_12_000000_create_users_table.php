<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path_pp')->nullable();
            $table->string('nrp')->unique();
            $table->string('username')->unique();
            $table->string('pangkat')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('api_token')->nullable();
            $table->integer('role')->default(2);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
        });

        DB::table('users')->insert([
            'name'=>'admin',
            'username'=>'admin',
            'email'=>'admin@domain.com',
            'password'=>Hash::make('12345678'),
            'api_token'=>Hash::make('admin@domain.com'),
            'role'=>1,
            'jabatan'=>'Admin',
            'pangkat'=>'-',
            'nrp'=>'100000000',
            'is_active'=>1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
