<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;

class ProductController extends Controller
{
    public function __construct() {}

    public function getStock($namaproduk, $warna, $size, $jumlah){
        $str = "";
        DB::beginTransaction();
        $cekInbox = DB::table('app_produk_stock')
        ->select('app_produk_stock.id', 'app_produk_stock.stock', 'app_produk_stock.weight',
        'app_produk_stock.produk_id', 'app_produk_stock.sku', 'app_produk_stock.price')
        ->leftJoin('app_produk', 'app_produk_stock.produk_id', '=', 'app_produk.id')
        ->leftJoin('app_produk_colour', 'app_produk_stock.id_warna', '=', 'app_produk_colour.id')
        ->leftJoin('app_produk_size', 'app_produk_stock.id_size', '=', 'app_produk_size.id')
        ->where('app_produk.namaproduk', $namaproduk)
        ->where('app_produk_colour.warna', $warna)
        ->where('app_produk_size.size', $size)
        // ->where('app_produk_stock.stock', '>=', $jumlah)
        ->where('app_produk_stock.stock', '>', '0')
        ->where('app_produk_stock.status', '1')
        ->get();
        // Log::info("hasil id --> ".$cekInbox);
        $hsl = "";$idp = "";$we="";$ids = "";$sku = "";$price = "";
        if(count($cekInbox)!='0'){
            foreach($cekInbox as $ci){
                $hsl = $ci->stock;
                $ids = $ci->id;
                $we  = $ci->weight;
                $idp = $ci->produk_id;
                $sku = $ci->sku;
                $price = $ci->price;
            }
            $diff = "0";$role   = "";$kurang = "0";
            if($hsl > $jumlah || $hsl == $jumlah){
                $diff   = $hsl - $jumlah;
                $role   = "f";
                $kurang = $jumlah;
            }
            if($hsl < $jumlah){
                $diff   = $hsl - $hsl;
                $role   = "p";
                $kurang = $hsl;
            }
            $kurangi = DB::update('update app_produk_stock set stock=? where id=?', [$diff,$ids]);
            $str = $diff."|".$we."|".$idp."|".$sku."|".$price."|".$ids."|".$role."|".$kurang;
            Log::info("hasil diff -> ".$diff.' - '.$str);
        }else{
            $str = "";
        }
        DB::commit();
        return $str;
    }

    public function getWarna($warna){
        $str = DB::table('app_produk_colour')
        ->select('*')
        ->where('warna', $warna)
        ->get();
        return $str;
    }

    public function getWarnaAll(){
        $str = DB::table('app_produk_colour')
        ->select('warna')
        ->get();
        return $str;
    }

    public function getSize($size){
        $str = DB::table('app_produk_size')
        ->select('*')
        ->where('size', $size)
        ->get();
        return $str;
    }

    public function getSizeAll(){
        $str = DB::table('app_produk_size')
        ->select('size')
        ->get();
        return $str;
    }

    public function getProduk($size){
        $str = DB::table('app_produk')
        ->select('*')
        ->where('namaproduk', $size)
        ->where('status', '1')
        ->get();
        return $str;
    }

    public function cekProduk($size){
        $str = DB::table('app_produk')
        ->select('*')
        ->where('namaproduk', $size)
        ->where('status', '1')
        ->count();
        return $str;
    }

    public function getProdukAll(){
        $str = DB::table('app_produk')
        ->select('namaproduk')
        ->get();
        return $str;
    }

    public function getProdukbyName($nama){
        $str = DB::table('app_produk')
        ->select('*')
        ->where('namaproduk',$nama)
        ->get();
        return $str;
    }

    public function getWarnabyName($warna){
        $str = DB::table('app_produk_stock')
        ->select('*')
        ->leftJoin('app_produk', 'app_produk_stock.produk_id', '=', 'app_produk.id')
        ->leftJoin('app_produk_colour', 'app_produk_stock.id_warna', '=', 'app_produk_colour.id')
        ->where('app_produk.namaproduk', $warna)
        ->get();
        return $str;
    }

    public function getSizebyName($warna){
        $str = DB::table('app_produk_stock')
        ->select('*')
        ->leftJoin('app_produk', 'app_produk_stock.produk_id', '=', 'app_produk.id')
        ->leftJoin('app_produk_size', 'app_produk_stock.id_size', '=', 'app_produk_size.id')
        ->where('app_produk.namaproduk', $warna)
        ->get();
        return $str;
    }
}
