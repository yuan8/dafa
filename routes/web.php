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

Route::prefix('tamu')->group(function(){
    Route::get('/daftar-tamu',[App\Http\Controllers\HomeController::class, 'daftar_tamu'])->name('g.daftar_tamu');
});

Route::prefix('provos')->middleware(['auth:web'])->group(function(){
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
        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'tambah'])->name('a.u.ubah');
        Route::put('/ubah/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'update'])->name('a.u.update');
        Route::delete('/delete/{id}/{slug}', [App\Http\Controllers\UserCtrl::class, 'delete'])->name('a.u.delete');
    });

     Route::prefix('bagian')->group(function(){
        Route::get('/', [App\Http\Controllers\BagianCtrl::class, 'index'])->name('a.b.index');
        Route::get('/tambah', [App\Http\Controllers\BagianCtrl::class, 'tambah'])->name('a.b.tambah');
        Route::get('/ubah/{id}/{slug}', [App\Http\Controllers\BagianCtrl::class, 'tambah'])->name('a.b.ubah');
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

     Route::delete('/batalkan-kunjungan/{id}', [App\Http\Controllers\HomeController::class, 'batalkan'])->name('k.batalkan');


    Route::get('/input/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_input'])->middleware('can:is_gate')->name('g.input');
    
    Route::post('/input/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_check_in'])->middleware('can:is_gate')->name('g.input.proccess');

    Route::get('/checkout/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_out'])->middleware('can:gate_check_out_provos')->name('g.checkout');

    Route::post('/checkout/{id}/{slug}', [App\Http\Controllers\HomeController::class, 'gate_check_out'])->middleware('can:gate_check_out_provos')->name('g.checkout.proccess');
    Route::get('/receiver/{fingerprint}', [App\Http\Controllers\HomeController::class, 'gate_receiver'])->name('g.receiver');

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