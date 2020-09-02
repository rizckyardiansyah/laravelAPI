<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class InboxController extends Controller
{
    public function __construct() {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function getInbox(){
        $data = DB::table('app_inbox')
        ->select('*')
        ->get();

        return response()->json([
            'status'=> '1',
            'msg'	=> 'Success',
            'data'	=> $data
        ], 200);
    }

    public function cekInbox($dari, $kepada, $pesan, $date){
        $cekInbox = DB::table('app_inbox')
            ->select('*')
            ->where('dari', $dari)
            ->where('kepada', $kepada)
            ->where('pesan', $pesan)
            ->where('create_date', '>=', $date.' 00:00:00')
            ->where('create_date', '<=', $date.' 23:59:59')
            ->where('status','=','1')
            ->count();
        return $cekInbox;
    }

    public function cekInboxDouble($dari, $kepada, $pesan, $date){
        $cekInbox = DB::table('app_inbox')
            ->select('*')
            ->where('dari', $dari)
            ->where('kepada', $kepada)
            ->where('pesan', 'like', '%'.$pesan.'%')
            ->where('create_date', '>=', $date.' 00:00:00')
            ->where('create_date', '<=', $date.' 23:59:59')
            ->count();
        return $cekInbox;
    }

    public function getInboxVal($dari, $kepada, $pesan, $date){
        $cekInbox = DB::table('app_inbox')
            ->select('*')
            ->where('dari', $dari)
            ->where('kepada', $kepada)
            ->where('pesan', 'like', '%'.$pesan.'%')
            ->where('create_date', '>=', $date.' 00:00:00')
            ->where('create_date', '<=', $date.' 23:59:59')
            ->get();
        return $cekInbox;
    }

    public function insertInbox($dari, $kepada, $pesan){
        $inbox  = DB::table('app_inbox')
                ->insertGetId([
                    'dari'  => $dari,
                    'kepada'=> $kepada,
                    'pesan' => $pesan
                ]);
        return $inbox;
    }

    public function updateInbox($id, $status){
        $inbox  = DB::table('app_inbox')
        ->where('id', $id)
        ->update(
            [
                'status'   => $status
            ]
        );
        return $inbox;
    }
}
