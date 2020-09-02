<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use DB;

class RajaOngkir extends Controller
{
    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getAllProvince(){
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

    public function getProvinsi(){
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
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success',
            'resp'  => $hasil['rajaongkir']['results']
        ], 200);
    }

    public function getAllCity(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
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
            $inbox  = DB::table('app_area_city')
                ->insert([
                    'city_id'  => $hsl['city_id'],
                    'province_id'=> $hsl['province_id'],
                    'name' => $hsl['city_name'],
                    'type' => $hsl['type'],
                    'postal_code' => $hsl['postal_code']
                ]);
            // Log::info("hasilnya -> ".$hsl['province_id']);
        }
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success'
        ], 200);
    }

    public function getCity(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
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
        Log::info($hasil['rajaongkir']['results']);
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success',
            'resp'  => $hasil['rajaongkir']['results']
        ], 200);
    }

    public function getAllKec(){
        $in     = DB::table("app_area_city")
            ->select("*")
            ->where('city_id','>=','400')
            // ->where('city_id','<','400')
            ->get();
        Log::info("getAllKec : ".$in);
        foreach($in as $ms){
            Log::info("hasil : ".$ms->city_id)."\n";
            $this->getAllKecamatan($ms->city_id);
        }
            return response()->json([
                'status'=> '1',
                'msg'	=> 'Success'
            ], 200);
        
    }

    public function waybill($resi, $kurir){
        Log::info("waybill : ".$resi.", kurir: ".$kurir);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/waybill",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"waybill\": \"$resi\",\n\t\"courier\": \"$kurir\"}",
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
        Log::info($hasil['rajaongkir']);
        $cost = $hasil['rajaongkir'];
        return $cost;
    }

    public function getAllKecamatan($cityid){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=$cityid",
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
            $inbox  = DB::table('app_area_kecamatan')
                ->insert([
                    'city_id'   => $hsl['city_id'],
                    'name'      => $hsl['subdistrict_name'],
                    'kec_id'    => $hsl['subdistrict_id']
                ]);
            Log::info("hasilnya -> ".$hsl['city_id']);
        }
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success'
        ], 200);
    }

    public function getKecamatan($cityid){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=$cityid",
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
        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success',
            'resp'  => $hasil['rajaongkir']['results']
        ], 200);
    }

    public function getCost(Request $request){
        if($request->has('origin') && $request->has('destination') && $request->has('weight') && $request->has('courier')){
            $origin   = $request->input('origin');
            $destination = $request->input('destination');
            $weight  = $request->input('weight');
            $courier  = $request->input('courier');
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
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
            // $cost = '';
            Log::info($hasil['rajaongkir']['results'][0]['costs']);
            $cost = $hasil['rajaongkir']['results'][0]['costs'];
            // foreach($hasil['rajaongkir']['results'][0]['costs'] as $hsl){
            //     Log::info("hasilnya -> ".$hsl['service']);
            //     if($hsl['service'] == 'REG'){
            //         $cost = $hasil['rajaongkir']['results'][0]['costs'];
            //     }
            //     // foreach($hsl['cost'] as $jd){
            //     //     // $cost = $jd['value'];
            //     //     Log::info("hasil akhir -> ".$jd['value']);
            //     // }
            // }
            return response()->json([
                'status'=> '1',
                'msg'	=> 'Success',
                'data'  => $cost
            ], 200);
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'Body is null'
            ]);
        }
    }
}
