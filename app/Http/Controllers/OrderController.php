<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;

class OrderController extends Controller
{
    private $gen = "";

    public function __construct() {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getOrderId(){
        $lastorderid    = DB::table('app_order')
            ->select('unic_code')
            ->get();
        $id="";$genid="";
        foreach($lastorderid as $lid){
            $id     = $lid->unic_code;
        }
        $bulan  = date('m');
        if($id=='' || $id==null){
            $genid  = str_pad('1',6,0, STR_PAD_LEFT);
        }else{
            $lastmonth  = substr($id, 4, 2);
            $lastinc = "";
            if($lastmonth != $bulan){
                $lastinc    = 0;
            }else{
                $lastinc    = substr($id,-6);
            }
            $genid      = str_pad($lastinc+1,6,0, STR_PAD_LEFT);
        }
        Log::info("generate order id ".$genid);
        return $genid;
    }

    public function setInvoiceNum($code){
        $bulan  = date('m');
        $tahun  = date('Y');
        return "INV/".$tahun."/".$bulan."/".$code;
    }

    public function insertOrder($unic_code, $invoice, $customerID, $total, $cost, $code, $totalharga){
        $date   = date('Y-m-d H:i:s');
        $unic   = date('Ymd');
        $inbox  = DB::table('app_order')
                ->insertGetId([
                    'unic_code' => $unic."".$unic_code,
                    'invoice_number'   => $invoice,
                    'atch_date' => $date,
                    'doctor_id' => $customerID,
                    'total_price'=> $total,
                    'discount'  => $cost,
                    'code'      => $code,
                    'total_harga'=> $totalharga
                ]);
        return $inbox;
    }

    public function insertOrderDetail($orderid, $produkid, $stockid, $sku, $jumlah, $harga, $nilai){
        $inbox  = DB::table('app_order_detail')
                ->insert([
                    'order_unic_id' => $orderid,
                    'produk_id'     => $produkid,
                    'stock_id'      => $stockid,
                    'sku'           => $sku,
                    'jumlah'        => $jumlah,
                    'harga'         => $harga,
                    'nilai'         => $nilai
                ]);
        return $inbox;
    }

    public function insertOrderDetailExecuted($orderid, $produkid, $stockid, $sku, $jumlah, $harga, $nilai){
        $inbox  = DB::table('app_order_detail_executed')
                ->insert([
                    'order_unic_id' => $orderid,
                    'produk_id'     => $produkid,
                    'stock_id'      => $stockid,
                    'sku'           => $sku,
                    'jumlah'        => $jumlah,
                    'harga'         => $harga,
                    'nilai'         => $nilai,
                    'total'         => $nilai
                ]);
        return $inbox;
    }

    public function insertOrderPayment($orderid, $unic_code, $invoice, $price, $duedate){
        $date   = date('Y-m-d H:i:s');
        $unic   = date('Ymd');
        $inbox  = DB::table('app_order_payment')
                ->insert([
                    'order_id'      => $orderid,
                    'unic_code'     => $unic."".$unic_code,
                    'invoice_number'=> $invoice,
                    'trx_price'     => $price,
                    'duedate'       => $duedate
                ]);
        return $inbox;
    }

    public function updateOrderPayment($payment_id, $trx_payment, $paymentdate){
        $inbox  = DB::table('app_order_payment')
        ->where('payment_id', $payment_id)
        ->update(
            [
                'trx_payment'   => $trx_payment,
                'payment_status'=> '1',
                'payment_date'  => $paymentdate
            ]
        );
        return $inbox;
    }
    
    public function insertOrderRetur($detailorder, $unic_code, $orderid, $harga, $jumlah, $sku)
    {
        $date   = date('Y-m-d H:i:s');
        $inbox  = DB::table('app_order_retur_history')
                ->insert([
                    'create_date'       => $date,
                    'detail_order_id'   => $detailorder,
                    'unic_code'         => $unic_code,
                    'order_id'          => $orderid,
                    'harga'             => $harga,
                    'jml'               => $jumlah,
                    'sku'               => $sku
                ]);
        return $inbox;
    }

    public function getOrder($unic_code)
    {
        $lastorderid    = DB::table('app_order')
            ->select('*')
            ->leftJoin('app_order_detail', 'app_order_detail.order_unic_id', '=', 'app_order.record_id')
            ->where('app_order.unic_code', $unic_code)
            ->get();
        return $lastorderid;
    }

    public function cancelOrder($record_id){
        $inbox  = DB::table('app_order')
        ->where('record_id', $record_id)
        ->leftJoin('app_order_payment', 'app_order_payment.order_id', '=', 'app_order.record_id')
        ->leftJoin('app_order_detail', 'app_order_detail.order_unic_id', '=', 'app_order.record_id')
        ->leftJoin('app_produk_stock','app_produk_stock.id', '=', 'app_order_detail.stock_id')
        ->update(
            [
                'payment_status'    => '2',
                'is_approve'        => '2',
                'is_process'        => '2',
                'stock'             => 'stock'+'app_order_detail.jumlah'

            ]
        );
        return $inbox;
    }
}
