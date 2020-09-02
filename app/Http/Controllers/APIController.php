<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class APIController extends Controller
{
    public function __construct(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->request = $request;
    }

    public function getAcv() {
        $y = date("Y");

        $start = $y."-01-01";
        $end = date("Y-m-t", strtotime($start));

        $array = null;

        $inc = 1;

        while($inc < 13) {
            $acv = $this->getRealAcv($start, $end);
            $month = date("F", strtotime($start));

            $array[] = array(
                'month'=>"$month", 
                'achievement'=>"$acv"
            );

            $start = date('Y-m-d', strtotime("+1 days", strtotime($end)));
            $end = date("Y-m-t", strtotime($start));

            $inc++; 
        }

        return json_encode($array);
    }

    public function getRealAcv($start, $end){
        $start  = $start . " 00:00:00";
        $end    = $end . " 23:59:59";
        $str    = DB::table('app_order')
            ->select(DB::raw('COALESCE(sum(total_price)-sum(code),0) as sum'))
            ->where('is_approve','=','1')
            ->where('atch_date','>',$start)
            ->where('atch_date','<',$end)
            ->get();
        $str1   = null;
        foreach($str as $sr){
            $str1   = $sr->sum;
        }
        return $str1;
    }

    public function getMntAcv($start="", $end="") {
        if($start=="") {
            $start = date("Y-m-01");
        }

        if($end=="") {
            $end = date("Y-m-t");
        }

        $startloop = $start;
        $endloop = $end;

        $array=null;

        while($startloop <= $endloop) {
            $acv = $this->getDailyAcv($startloop);

            $dt = date("d", strtotime($startloop));

            $array[] = array(
                'date' => "$dt",
                'acv' => "$acv"
            );

            $startloop = date('Y-m-d', strtotime("+1 days", strtotime($startloop)));
        }

        return json_encode($array);
    }

    public function getDailyAcv($startloop) {
        $start  = $startloop . " 00:00:00";
        $end    = $startloop . " 23:59:59";
        $q      = DB::table('app_order')
                ->select(DB::raw('COALESCE(sum(total_price)-sum(code), 0) as sum'))
                ->where('is_approve','=','1')
                ->where('atch_date', '>=', $start)
                ->where('atch_date', '<=', $end)
                ->get();
        foreach($q as $qr){
            $q  = $qr->sum;
        }
        return $q;
    }

    public function getPie() {
        $startloop = date("Y-m-01");
        $astart = $startloop . " 00:00:00";
        $aend   = date("Y-m-t", strtotime($startloop)) . " 23:59:59";
        $q      = DB::table('app_order')
                ->select('*')
                ->where('is_approve','=','0')
                ->where('atch_date','>=',$astart)
                ->where('atch_date','<=',$aend)
                ->count();
        
        $arr[] = array('kode'=>'Order', 'jumlah'=>$q);

        $q1     = DB::table('app_order')
        ->select('*')
        ->where('is_approve','=','1')
        ->where('atch_date','>=',$astart)
        ->where('atch_date','<=',$aend)
        ->count();

        $arr[] = array('kode'=>'Payment', 'jumlah'=>$q1);

        return json_encode($arr);
    }

    public function getSumTrx($start, $end){
        if($start!='' && $end!=''){
            $month = date("F", strtotime($start));
            $str = DB::table('app_order')
            ->select('*')
            ->where('atch_date', '>', $start)
            ->where('atch_date', '<', $end)
            ->count();

            $str1 = DB::table('app_order_detail_executed')
            ->select(DB::raw('sum(app_order_detail_executed.nilai)-sum(app_order.code) as total'))
            ->leftJoin('app_order', 'app_order_detail_executed.order_unic_id', '=', 'app_order.record_id')
            ->where('app_order.atch_date', '>', $start)
            ->where('app_order.atch_date', '<', $end)
            ->get();
            foreach($str1 as $sr){
                $str1   = $sr->total;
            }

            $str2 = DB::table('app_produk_stock')
            ->select(DB::raw('sum(stock) as total'))
            ->get();
            foreach($str2 as $sr){
                $str2   = $sr->total;
            }

            $str3 = $this->getAcv();
            $str4 = $this->getMntAcv();
            $str5 = $this->getPie();

            $myArray = array('SumTrx'=>$str, 'SumTrxDuit'=>$str1, 'SumInventory'=>$str2, 'SumAcv'=>$str3, 'SumMonthAcv'=>$str4, 'SumPie'=>$str5);
            return response()->json([
                'status'    => '1',
                'msg'       => 'okay',
                'result'    => $myArray
            ]);
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'Body is null'
            ]);
        }
    }

    public function getCustomer($id = "") {
        if($id == "") {
            $q = DB::table('app_customer')
                ->select('*',DB::raw('(select name from app_area_kecamatan where name = kecamatan limit 1) as nama_kecamatan, 
                (select name from app_area_city where city_id = kota limit 1) as nama_kota, 
                (select area_name from app_area_province where area_id = provinsi limit 1) as nama_provinsi'))
                ->get();
        }
        else {
            $q = DB::table('app_customer')
            ->select('*',DB::raw('(select name from app_area_kecamatan where name = kecamatan limit 1) as nama_kecamatan, 
            (select name from app_area_city where city_id = kota limit 1) as nama_kota, 
            (select area_name from app_area_province where area_id = provinsi limit 1) as nama_provinsi'))
            ->where('record_id','=',$id)
            ->get();
        }
        return response()->json([
            'status'    => '1',
            'msg'       => 'okay',
            'result'    => $q
        ]);
    }

    public function getLaporanPenjualan($start, $end) {
        $q  = DB::table('app_order')
            ->select(DB::raw('app_order.record_id, app_order.unic_code, app_order.atch_date, app_order.atch_by,
            app_order.doctor_id, app_order.is_approve, app_order.is_process, app_order.discount, app_order.total_price, app_order.invoice_number,
            app_customer.name, app_order.code, app_order.add_address as resi, app_shipping.nama as shipping,
            COALESCE(app_order_payment.trx_payment,0) as jumlahyangsudahdibayar, app_order_payment.payment_file as buktibayar,
            app_order.discount+app_order.total_price-app_order.code as total_nilai,
            (select sum(jumlah) from app_order_detail_executed where order_unic_id=app_order.record_id) as qty'))
            ->leftJoin('app_customer','app_order.doctor_id','=','app_customer.record_id')
            ->leftJoin('app_order_payment','app_order.record_id','=','app_order_payment.order_id')
            ->leftJoin('app_shipping','app_order.is_add_address','=','app_shipping.id')
            ->where('app_order.atch_date','>',$start)
            ->where('app_order.atch_date','<',$end)
            ->orderby('app_order.atch_date', 'desc')
            ->get();
        if($q != ''){
            return response()->json([
                'status'    => '1',
                'msg'       => 'okay',
                'result'    => $q
            ]);
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'result null'
            ]);
        }
    }

    public function getLaporanPenjualanDetail($id) {
        $q  = DB::table('app_order_detail')
        ->leftJoin('app_order', 'app_order_detail.order_unic_id','=','app_order.record_id')
        ->leftJoin('app_produk', 'app_order_detail.produk_id','=','app_produk.id')
        ->leftJoin('app_produk_stock', 'app_order_detail.stock_id','=','app_produk_stock.id')
        ->leftJoin(DB::raw('(select id,warna from app_produk_colour) as e'), 'app_produk_stock.id_warna','=','e.id')
        ->leftJoin(DB::raw('(select id,size from app_produk_size) as f'), 'app_produk_stock.id_size','=','f.id')
        ->where('order_unic_id','=',$id)
        ->orderby('order_unic_id')
        ->get();
        if($q != ''){
            return response()->json([
                'status'    => '1',
                'msg'       => 'okay',
                'result'    => $q
            ]);
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'result null'
            ]);
        }
    }
}
