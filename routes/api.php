<?php

use Illuminate\Http\Request;

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

Route::get('customer','CustomerController@getCustomer');
Route::get('getmax','CustomerController@getOrder');
Route::post('doc', 'SalesController@createInbox');
Route::post('resi', 'SalesController@sendResi');
Route::get('cekresi/{resi}/{kurir}', 'RajaOngkir@waybill');
Route::get('province','RajaOngkir@getAllProvince');
Route::get('city','RajaOngkir@getAllCity');
Route::get('kecamatan','RajaOngkir@getAllKecamatan/{cityid}');
Route::post('cost','RajaOngkir@getCost');
Route::post('cancel', 'OrderContoller@cancelOrder/{orderid}');
Route::get('kec','RajaOngkir@getAllKec');
Route::get('getmaxtransfer', 'BankController@cekMaxTransfer');
Route::get('mutasi/{tipe}/{tglstart}/{tglend}', 'BankController@CekMutasi');
Route::get('sendwa/{phone}/{pesan}', ' kirimWAController@sendWA');
Route::get('getSumTrx/{start}/{end}', 'APIController@getSumTrx');
Route::get('getCustomer', 'APIController@getCustomer');
Route::get('getLaporanPenjualan/{start}/{end}', 'APIController@getLaporanPenjualan');
Route::get('getLaporanPenjualanDetail/{id}', 'APIController@getLaporanPenjualanDetail');
Route::get('getCity','RajaOngkir@getCity');
Route::get('getKecamatan/{cityid}', 'RajaOngkir@getKecamatan');
Route::get('getProvinsi', 'RajaOngkir@getProvinsi');