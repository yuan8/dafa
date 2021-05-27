<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('extact-data-indentity',[App\Http\Controllers\API\IdentityExtractCtrl::class, 'extract'])->name('api.identity.extract');


Route::post('get-identity',[App\Http\Controllers\API\IdentityExtractCtrl::class, 'get_identity'])->name('api.get.identity');



Route::post('match-data-indentity',[App\Http\Controllers\API\IdentityExtractCtrl::class, 'extract'])->name('api.identity.match');

Route::post('generate-phone-call',[App\Http\Controllers\API\IdentityExtractCtrl::class, 'generate_qr_phone_cal'])->name('generate.qr.phone_call');
