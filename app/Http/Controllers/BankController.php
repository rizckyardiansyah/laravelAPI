<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\kirimWAController;
use DB;
use Log;

class BankController extends Controller
{
    private $request;
    private $kirimwa;

    public function __construct(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->request = $request;
    }

    public function cekMaxTransfer(){
        date_default_timezone_set('Asia/Jakarta');
        $date       = date('Y-m-d');
        $time       = date('H:i:s');
        $cc         = new CustomerController();
        $kirimwa    = new kirimWAController();
        $hasil      = $cc->getOrder($date);
        Log::info($date." -> ".$hasil);
        Log::info("waktu : ".$time);
        if($time >= '14:00:00'){
            if($hasil != ''){
                foreach($hasil as $hsl){
                    $id     = $hsl->record_id;
                    $idc    = $hsl->doctor_id;
                    $total  = $hsl->total_harga;
                    $detail = $cc->getOrderDetail($id);
                    $customer=$cc->getCustomer($idc);
                    $cc->updateOrder($id, '2', '3');
                    Log::info("detailnya : ".$detail);
                    foreach($detail as $dt){
                        $stock_id   = $dt->stock_id;
                        $stock      = $dt->jumlah;
                        $cc->updateOrderStock($stock_id, $stock);
                    }
                    $phone          = '';
                    $name           = '';
                    foreach($customer as $cust){
                        $phone      = $cust->phone1;
                        $name       = $cust->name;
                    }
                    $pesanAkhir     = "Hai, Kak ".strtoupper($name).". Mohon maaf pemesanannya kami batalkan, karena sampai jam 14:00 WIB tidak menyelesaikan pembayaran sebesar Rp. ".number_format($total, 2).".";
                    $kirimwa->sendWA($phone, $pesanAkhir);
                    LOG::info($phone." - ".$pesanAkhir);
                }
            }
            return response()->json([
                'status'    => '1',
                'msg'       => 'okay, lebih dari jam 14:00'
            ]);
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'kurang dari jam 14:00'
            ]);
        }
    }

    public function CekMutasi($tipe, $tgl_start, $tgl_end)
    {
        if($tipe != '' && $tgl_start != '' && $tgl_end != ''){
            $api_server     = 'ibuah.otomat.web.id';
            $api_id         = '2126';
            $api_key        = 'NxoaAmxilRlfryVhBfPqMasvTLG3d5bi';
            $bank           = 'bca';
            $tipe_mutasi    = $tipe;
            $tanggal_mulai  = $tgl_start;
            $tanggal_akhir  = $tgl_end;

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "http://ibuah.otomat.web.id/ibank/bca?api_server=$api_server&api_id=$api_id&api_key=$api_key&ibank_password=651788&bank=$bank&tipe_mutasi=$tipe_mutasi&tanggal_mulai=$tanggal_mulai&tanggal_akhir=$tanggal_akhir",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"api_server\"\r\n\r\nibuah.otomat.web.id\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"api_id\"\r\n\r\n2126\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"api_key\"\r\n\r\nNxoaAmxilRlfryVhBfPqMasvTLG3d5bi\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"ibank_password\"\r\n\r\n651788\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"bank\"\r\n\r\nbca\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"tipe_mutasi\"\r\n\r\nsemua\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"tanggal_mulai\"\r\n\r\n2020-07-01\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"tanggal_akhir\"\r\n\r\n2020-07-06\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 1ff6b5f1-2540-9769-934c-860f5e40915f"
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
                Log::info("error : ".$err);
            } else {
                echo $response;
                Log::info("response is : ".$response);
            }
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'Body is null'
            ]);
        }
    }

    public function getMutasiBank(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "key: 679e40ac3f49ed8049cba59c8c5c2d45",
                "postman-token: a381176a-5056-709e-b9ff-f8decdcdc559"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $hasil = json_decode($response, true);
        curl_close($curl);
        // Log::info($hasil['rajaongkir']['results'][0]);
        foreach($hasil['rajaongkir']['results'] as $hsl){
            $inbox  = DB::table('app_area_province')
                ->insert([
                    'area_id'  => $hsl['province_id'],
                    'area_name'=> $hsl['province']
                ]);
            Log::info("hasilnya -> ".$hsl['province_id']);
        }
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success'
        ], 200);
    }
}
