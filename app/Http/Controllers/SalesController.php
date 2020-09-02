<?php

namespace App\Http\Controllers;

use App\Sales;
use App\Inbox;
use Illuminate\Http\Request;
use DB;
use App\Events\Event;
use Log;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\kirimWAController;
use App\Http\Controllers\ProductController;

class SalesController extends Controller
{
    private $request;
    private $inboxController;

    public function __construct(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->request = $request;
    }

    public function sendResi(Request $request)
    {
        $kirimWa        = new KirimWAController();
        if($request->has('dari') && $request->has('pesan')){
            date_default_timezone_set('Asia/Jakarta');
            $dari   = $request->input('dari');
            $pesan  = $request->input('pesan');
            $kirim  = $kirimWa->sendWA($dari, $pesan);
            Log::info($kirim);
            Log::info("dari : ".$dari.", pesannya: ".$pesan);
            return response()->json([
                'status'    => '1',
                'msg'       => 'Successfully send'
            ]);
        }
    }

    public function validate_mobile($hp){
        return preg_match('/^[0-9]+$/', $hp);
    }

    public function createInbox(Request $request)
    {
        $inboxController    = new InboxController();
        $kirimwa            = new kirimWAController();
        $product            = new ProductController();
        if($request->has('from') && $request->has('to') && $request->has('body')){
            date_default_timezone_set('Asia/Jakarta');
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $dari   = $request->input('from');
            $kepada = $request->input('to');
            $pesan  = $request->input('body');
            if($this->validate_mobile($dari) && $this->validate_mobile($kepada) && $pesan != ''){
                $msg        = explode('#', $pesan);
                if(strtolower($msg[0]) == 'warna' && count($msg) == 2){
                    $cariproduk     = $product->getProdukbyName($msg[1]);
                    $hsl            = "";
                    $pesan          = "";
                    if(count($cariproduk) != 0){
                            // foreach($cariproduk as $cp){
                            //     $hsl    = $cp->namaproduk;
                            // }
                        $cariwarna  = $product->getWarnabyName($msg[1]);
                        $warna      = "";
                        if(count($cariwarna) != 0){
                            foreach($cariwarna as $cw){
                                $warna  .= $cw->warna;
                                if(count($cariwarna) > 1)
                                    $warna .= ", ";
                            }
                            $pesan  = "Hai Kak, berikut adalah warna yang tersedia untuk Produk ".strtoupper($msg[1])." --> ".strtoupper($warna);
                        }
                    }else{
                        $pesan      = "Hai Kak, Mohon maaf. Produk ".strtoupper($msg[1])." tidak tersedia saat ini.";
                    }
                    $kirimwa->sendWA($dari, $pesan);
                    Log::info($pesan);
                    return response()->json([
                        'status'    => '1',
                        'msg'       => 'OK'
                    ]);
                }else{
                    $cekInbox   = $inboxController->cekInbox($dari, $kepada, $pesan, $date);
                    Log::info("This is value $cekInbox");
                    if($cekInbox == 0){
                        $inbox  = $inboxController->insertInbox($dari, $kepada, $pesan);
                        if($inbox){
                            $arr = [$dari, $kepada, $pesan, $inbox];
                            event(new Event($arr));
                            return response()->json([
                                'status'    => '1',
                                'msg'       => 'Successfully input to inbox'
                            ]);
                        }
                        else{
                            return response()->json([
                                'status'    => '0',
                                'msg'       => 'Unsuccessfully input to inbox'
                            ]);
                        }
                    }else{
                        $pesan  = "Mohon maaf, Anda sudah melakukan order sebelumnya. Silakan tunggu informasi selanjutnya. \nTerimakasih 🙏🏻❤️";
                        $kirimwa->sendWA($dari, $pesan);
                        return response()->json([
                            'status'    => '0',
                            'msg'       => 'message already submitted, please wait for a moment'
                        ]);
                    }
                }
            }else{
                return response()->json([
                    'status'    => '0',
                    'msg'       => 'message is null'
                ]);
            }
        }else{
            return response()->json([
                'status'    => '0',
                'msg'       => 'Body is null'
            ]);
        }
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
     * @param  \App\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function show(Sales $sales)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function edit(Sales $sales)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sales $sales)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sales $sales)
    {
        //
    }
}
