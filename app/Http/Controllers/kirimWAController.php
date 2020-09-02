<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class kirimWAController extends Controller
{
    
    public function __construct() {}

    public function sendWA($phone, $pesan){
        date_default_timezone_set('Asia/Jakarta');
        $var['api_id']  = '2110';
        $var['api_key'] = 'SX1fRJiBWvHy7imWxkpyTz3XIJ7MGV5M';
        $var['phone']   = $phone;
        $var['text']    = $pesan;
        $curl = curl_init('wa.otomat.web.id');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $var);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        // Log::info($response);
        return $response;
    }

    public function getCost($origin, $destination, $weight, $courier){
        $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\n\t\"origin\": \"$origin\",\n\t\"destination\": \"$destination\",\n\t\"weight\": \"$weight\",\n\t\"courier\": \"$courier\"\n}",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "key: 679e40ac3f49ed8049cba59c8c5c2d45",
                    "postman-token: a381176a-5056-709e-b9ff-f8decdcdc559"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $hasil = json_decode($response, true);
            curl_close($curl);
            // Log::info($hasil['rajaongkir']['results'][0]['costs']);
            $cost = $hasil['rajaongkir']['results'][0]['costs'];
            $valid = $hasil['rajaongkir']['status']['code'];
            // Log::info("hasil valid : ".$valid);
            $isian;
            if($valid == "200"){
                foreach($cost as $c){
                    if($c['service'] == 'CTC' || $c['service'] == 'REG'){
                        $isian = $c['cost'][0]['value'];
                        Log::info("hasilnya -> ".$c['cost'][0]['value']);
                    }
                }
            }else{
                $isian = "0";
            }
            return $isian;
    }
}
