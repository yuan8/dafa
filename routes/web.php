<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('tamu')->middleware('auth:web')->group(function(){
    Route::get('/daftar-tamu',[App\Http\Controllers\TamuCtrl::class, 'daftarTamuList'])->name('g.daftar_tamu');

    Route::get('/edit-tamu/{id}/{slug}',[App\Http\Controllers\TamuCtrl::class, 'edit'])->name('g.tamu.edit')->middleware('can:is_provos');

     Route::get('/tambah',[App\Http\Controllers\TamuCtrl::class, 'tambah'])->name('g.tamu.tambah')->middleware('can:is_gate');

     Route::post('/store',[App\Http\Controllers\TamuCtrl::class, 'store'])->name('g.tamu.store')->middleware('can:is_gate');

     Route::get('/view-tamu/{id}/{slug}',[App\Http\Controllers\TamuCtrl::class, 'view'])->name('g.tamu.view')->middleware('can:is_gate');

    Route::put('/edit-tamu/{id}',[App\Http\Controllers\TamuCtrl::class, 'simpan_data_tamu'])->name('g.tamu.update')->middleware('can:is_provos');

    Route::get('/identity-tamu-khusus/{id}/id-generate.pdf',[App\Http\Controllers\TamuCtrl::class, 'identity_tamu_khusus'])->name('g.tamu.id_khusus');

    Route::get('/daftar-tamu/gate-provos/{id}/{slug}',[App\Http\Controllers\TamuCtrl::class, 'toGateProvos'])->name('g.daftar_tamu.gate_provos');
});

Route::prefix('gate')->middleware(['auth:web'])->group(function(){
    Route::get('/', [App\Http\Controllers\HomeController::class, 'provos_index'])->name('p.index');

    Route::get('/input', [App\Http\Controllers\HomeController::class, 'provos_input'])->name('p.input');
    Route::post('/submit', [App\Http\Controllers\HomeController::class, 'provos_submit'])->name('p.submit');

    Route::get('/receiver/{fingerprint}', [App\Http\Controllers\HomeController::class, 'provos_receiver'])->name('p.receiver');
});

Route::prefix('admin')->middleware(['auth:web'])->group(function(){
    Route::prefix('tamu')->group(function(){
     Route::get('/', [App\Http\Controllers\TamuCtrl::class, 'index'])->name('a.t.index');
     Route::get('/detail/{id}/{date}/{slug}', [App\Http\Controllers\TamuCtrl::class, 'detail'])->name('a.t.detail');

    });

     Route::prefix('setting')->group(function(){
        Route::get('/', [App\Http\Controllers\BagianCtrl::class, 'index'])->name('a.s.index');
        Route::get('/tambah', [App\Http\Controllers\BagianCtrl::class, 'tambah'])->name('a.s.tambah');
        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'tambah'])->name('a.s.ubah');
        Route::put('/ubah/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'update'])->name('a.s.update');
        Route::delete('/delete/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'delete'])->name('a.u.delete');
    });

    Route::prefix('users')->group(function(){
        Route::get('/', [App\Http\Controllers\UserCtrl::class, 'index'])->name('a.u.index');
        Route::get('/tambah', [App\Http\Controllers\UserCtrl::class, 'tambah'])->name('a.u.tambah');

        Route::put('/ubah-passowrd/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'ubah_password'])->name('a.u.password');
        Route::post('/tambah', [App\Http\Controllers\UserCtrl::class, 'store'])->name('a.u.store');
        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'edit'])->name('a.u.ubah');
        Route::put('/ubah/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'update'])->name('a.u.update');
        Route::delete('/delete/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'delete'])->name('a.u.delete');
    });

     Route::prefix('bagian')->group(function(){
        Route::get('/', [App\Http\Controllers\BagianCtrl::class, 'index'])->name('a.b.index');

        Route::get('/tambah', [App\Http\Controllers\BagianCtrl::class, 'create'])->name('a.b.tambah');


        Route::post('/store', [App\Http\Controllers\BagianCtrl::class, 'store'])->name('a.b.store');

        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'edit'])->name('a.b.ubah');

        Route::put('/ubah/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'update'])->name('a.b.update');

        Route::delete('/delete/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'delete'])->name('a.b.delete');
    });

      Route::prefix('ketegori')->group(function(){
        Route::get('/', [App\Http\Controllers\KategoriTamuCtrl::class, 'index'])->name('a.k.index');
        Route::get('/tambah', [App\Http\Controllers\KategoriTamuCtrl::class, 'tambah'])->name('a.k.tambah');
        Route::post('/tambah', [App\Http\Controllers\KategoriTamuCtrl::class, 'store'])->name('a.k.store');

        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\KategoriTamuCtrl::class, 'tambah'])->name('a.k.ubah');
        Route::put('/ubah/{id}/{slug}', [App\Http\Controllers\KategoriTamuCtrl::class, 'update'])->name('a.k.update');
        Route::delete('/delete/{id}/{slug}', [App\Http\Controllers\KategoriTamuCtrl::class, 'delete'])->name('a.k.delete');
    });

});

Route::prefix('gate')->middleware(['auth:web'])->group(function(){
    Route::get('/', [App\Http\Controllers\HomeController::class, 'gate_index'])->name('g.index');
    Route::get('/report', [App\Http\Controllers\TamuCtrl::class, 'report'])->name('g.report');

    Route::get('/rekap', [App\Http\Controllers\HomeController::class, 'rekap'])->name('g.rekap');

     Route::delete('/batalkan-kunjungan/{id}', [App\Http\Controllers\HomeController::class, 'batalkan'])->name('k.batalkan');


    // Route::get('/input/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_input'])->middleware('can:is_gate')->name('g.input');
    
    // Route::post('/input/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_check_in'])->middleware('can:is_gate')->name('g.input.proccess');

    Route::get('/checkout/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_out'])->middleware('can:gate_check_out_provos')->name('g.checkout');

    Route::post('/checkout/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_check_out'])->middleware('can:gate_check_out_provos')->name('g.checkout.proccess');
    Route::get('/2-receiver/{fingerprint}', [App\Http\Controllers\HomeController::class, 'gate_receiver'])->name('g.receiver');

});

Route::get('batalkan-kunjungan/{id?}',[App\Http\Controllers\API\IdentityExtractCtrl::class, 'batalkan_kunjungan'])->name('batalkan.kunjungan');

Auth::routes();

Route::get('/send/{figerprint}', function ($fingerprint) {
    broadcast(new App\Events\ProvosInput([
        'a'=>'a',
        '2'=>'b'
    ],$fingerprint));
    return response('Sent');
});

Route::get('/pin', function () {
    broadcast(new App\Events\ProvosCheckin([
        'a'=>'a',
        '2'=>'b'
    ]));
    return response('Sent');
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
