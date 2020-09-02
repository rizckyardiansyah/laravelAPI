<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use DB;

class CustomerController extends Controller
{
    private $request;

    public function __construct() {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Customer::all(); 
    } 

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }

    public function getOrder($tanggal){
        $data = DB::table('app_order')
        ->select('*')
        // ->leftJoin('app_order_detail', 'app_order_detail.order_unic_id', '=', 'app_order.record_id')
        ->where('atch_date','>=',$tanggal." 00:00:00")
        ->where('atch_date','<=',$tanggal." 23:59:59")
        ->where('is_approve','0')
        ->where('is_process','0')
        ->get();
        return $data;
    }

    public function getOrderDetail($id){
        $data = DB::table('app_order_detail')
        ->select('*')
        ->where('order_unic_id',$id)
        ->get();
        return $data;
    }

    public function updateOrder($id, $approve, $process){
        $inbox  = DB::table('app_order')
        ->where('record_id', $id)
        ->update(
            [
                'is_approve'    => $approve,
                'is_process'    => $process
            ]
        );
        return $inbox;
    }

    public function updateOrderStock($id, $stock){
        DB::beginTransaction();
        $inbox  = DB::table('app_produk_stock')
        ->select('*')
        ->where('id', $id)
        ->get();
        $st = 0;
        foreach($inbox as $ib){
            $st     = $ib->stock;
        }
        $sum    = $st + $stock;
        $kurangi = DB::update('update app_produk_stock set stock=? where id=?', [$sum,$id]);
        DB::commit();
        return $kurangi;
    }

    public function getCityId($nama){
        $data = DB::table('app_area_city')
        ->select('*')
        ->where('name', $nama)
        ->get();
        return $data;
    }

    public function getKecamatanId($nama){
        $data = DB::table('app_area_kecamatan')
        ->select('*')
        ->where('name', $nama)
        ->get();
        return $data;
    }

    public function getKecId($nama, $kecamatan){
        $data = DB::table('app_area_kecamatan')
        ->select('*')
        ->leftJoin('app_area_city', 'app_area_kecamatan.city_id', '=', 'app_area_city.city_id')
        ->where('app_area_kecamatan.name', $nama)
        ->where('app_area_city.name', $nama)
        ->get();
        return $data;
    }

    public function getProvinceId($nama){
        $data = DB::table('app_area_province')
        ->select('*')
        ->where('area_id', $nama)
        ->get();
        return $data;
    }

    public function getCustomer($id){
        $customer   = DB::table('app_customer')
        ->select('*')
        ->where('record_id', $id)
        ->get();
        return $customer;
    }

    public function cekCustomer($name, $phone, $alamat, $kec, $kota, $prov){
        $cekInbox = DB::table('app_customer')
            ->select('*')
            ->where('name', $name)
            ->where('phone1', $phone)
            ->where('full_add', $alamat)
            ->where('kecamatan', $kec)
            ->where('kota', $kota)
            ->where('provinsi', $prov)
            ->get();
        return $cekInbox;
    }

    public function insertCustomer($name, $phone, $alamat, $kec, $kota, $prov){
        $inbox  = DB::table('app_customer')
                ->insertGetId([
                    'name'      => $name,
                    'phone1'    => $phone,
                    'full_add'  => $alamat,
                    'kecamatan' => $kec,
                    'kota'      => $kota,
                    'provinsi'  => $prov
                ]);
        return $inbox;
    }
}
