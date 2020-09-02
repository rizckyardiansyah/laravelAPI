<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\kirimWAController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InboxController;

class PenjualanController extends Controller
{
    private $cc;
    private $kirimwa;
    private $orderController;
    private $inboxController;

    public function __construct() {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function singleOrder($nama, $hp, $alamat, $kec, $kota, $produk, $isiproduk, $inboxid, $dari, $kepada, $orderid){
        Log::info("singleOrder : ".$nama .",".$hp.",".$alamat.",".$kec.",".$kota.",".$produk.",".$isiproduk);
        $str = '';
        $date           = date('Y-m-d');
        $time           = date('H:i:s');
        $origin         = '115';
        $cc             = new CustomerController();
        $kirimwa        = new kirimWAController();
        $product        = new ProductController();
        $orderController= new OrderController();
        $inboxController= new InboxController();
        $test           = $cc->getCityId(trim($kota));
        $kecid          = $cc->getKecamatanId(trim($kec));
        $cekProduct     = $product->cekProduk(trim($produk));
        $destination    = '';
        $provinceId     = '';
        $kecamatanId    = '';
        foreach($test as $sp){
            $destination    = $sp->city_id;
            $provinceId     = $sp->province_id;
        }
        foreach($kecid as $kid){
            $kecamatanId    = $kid->kec_id;
        }
        $valHP          = $this->validate_mobile($hp);
        Log::info("valHP : ".$valHP);
        if($nama != '' || $nama != null){
            if($valHP){
                if($destination != '' && $provinceId != ''){
                    if($kecamatanId != ''){
                        if($cekProduct != 0){
                            $cekCustomer        = $cc->cekCustomer($nama, $hp, $alamat, $kec, $destination, $provinceId);
                            $idCustomer         = "";
                            foreach($cekCustomer as $ck){
                                $idCustomer     = $ck->record_id;
                            }
                            if($idCustomer == ""){
                                $idCustomer         = $cc->insertCustomer($nama, $hp, $alamat, $kec, $destination, $provinceId);
                            }
                            $multi  = explode(',', $isiproduk);
                            $cMulti = count($multi);
                            $pesan  = "";
                            $total  = "";
                            foreach($multi as $m){
                                $spl    = explode(' ', $m);
                                $count  = count($spl);
                                if($count < 2){
                                    $pesan .= "Silakan cek format order \nNAMA#NO_HP#ALAMAT#KECAMATAN#KOTA/KAB#NamaProduk Warna Ukuran Jumlah";
                                    Log::info($pesan);
                                }else{
                                    if($count == 2){
                                        $warna      = $spl[0];
                                        $size       = $spl[1];
                                        $jumlah     = '1';    
                                    }if($count == 3){
                                        if(is_numeric($spl[2])){
                                            $warna      = $spl[0];
                                            $size       = $spl[1];
                                            $jumlah     = $spl[2];
                                        }else{
                                            $warna      = $spl[0]." ".$spl[1];
                                            $size       = $spl[2];
                                            $jumlah     = '1';
                                        }
                                    }if($count == 4){
                                        $warna      = $spl[0]." ".$spl[1];
                                        $size       = $spl[2];
                                        $jumlah     = $spl[3];
                                    }
                                    Log::info("isian -> ".$warna."|".$size."|".$jumlah);
                                    $cekWarna   = $product->getWarna($warna);
                                    $getWarna   = $product->getWarnaAll();
                                    $cekSize    = $product->getSize($size);
                                    $getSize    = $product->getSizeAll();
                                    // $cekProduk  = $product->getProduk($namaproduk);
                                    $getProduk  = $product->getProdukAll();
                                    $colour     = "";$ukuran = "";
                                    foreach($getWarna as $gw){
                                        $colour .= $gw->warna.', ';
                                    }
                                    foreach($getSize as $gw){
                                        $ukuran .= $gw->size.', ';
                                    }
                                    // foreach($getProduk as $gw){
                                    //     $produk .= $gw->namaproduk.', ';
                                    // }
                                    $namaproduk = $produk;
                                    $beratTotal = "0";$idproduk = "";$sku = "";$idstock = "";$role = "";$jmlFinal = "0";
                                    if(count($cekWarna)==0 && count($cekSize)==0 && count($cekProduk)==0){
                                        // $pesan .= "Ukuran (".$size."), tidak tersedia di keyzaa.id. Berikut adalah ukuran yang bisa kamu pilih : [".$ukuran."]\n dan Warna (".$warna."), tidak tersedia di keyzaa.id. Berikut adalah warna yang bisa kamu pilih : [".$colour."]";
                                        $pesan .= "Hallo kak, Ukuran (".$size."), Warna (".$warna.") untuk Nama Produk (".$namaproduk."), tidak ada sis.";
                                        $total .= "0|".$namaproduk."|".$size."|".$warna."|".$jumlah."|".$beratTotal.
                                        "|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                    }elseif(count($cekWarna)==0){
                                        $pesan .= "Hallo kak, warna (".$warna."), tidak tersedia di keyzaa.id. Berikut adalah warna yang bisa kamu pilih : [".$colour."]";
                                        $total .= "0|".$namaproduk."|".$size."|".$warna."|".$jumlah."|".$beratTotal.
                                        "|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                        // Log::info($pesan);
                                    }elseif(count($cekSize)==0){
                                        $pesan .= "Hallo kak, size (".$size."), tidak tersedia di keyzaa.id. Berikut adalah ukuran yang bisa kamu pilih : [".$ukuran."]";
                                        $total .= "0|".$namaproduk."|".$size."|".$warna."|".$jumlah."|".$beratTotal.
                                        "|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                        // Log::info($pesan);
                                    }else{
                                        $stock = $product->getStock($produk, $warna, $size, $jumlah);
                                        Log::info("isi getStock : ".$stock);
                                        if($stock=='' || $stock==null){
                                            // Log::info("mohon maaf stock-nya full booked sis");
                                            $pesan .= " mohon maaf stock-nya untuk produk (".$namaproduk.") full booked sis";
                                            $total .= "0|".$namaproduk."|".$size."|".$warna."|".$jumlah."|".$beratTotal.
                                                "|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                        }else{
                                            $splStock = explode('|',$stock);
                                            $idproduk   = $splStock[2];
                                            $berat      = $splStock[1];
                                            $sisa       = $splStock[0];
                                            $sku        = $splStock[3];
                                            $harga      = $splStock[4];
                                            $idstock    = $splStock[5];
                                            $role       = $splStock[6];
                                            $jmlFinal   = $splStock[7];
                                            $beratTotal = $jmlFinal * $berat * 1000;
                                            // Log::info("stock hasilnya -> ".$stock."|".$beratTotal);
                                            $pesan .= "\nHarga yang harus dibayarkan ".$harga;
                                            $total .= ($jmlFinal*$harga)."|".$namaproduk."|".$size."|".$warna."|".$jmlFinal.
                                                "|".$beratTotal."|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                        }
                                    }
                                    Log::info("isi getTotal : ".$total);
                                }
                            }
                            $isfull     = 0;
                            $iskosong   = 0;
                            $ispartial  = 0;
                            $gafull     = 0;
                            $par        = count(explode("/",$total))-1;
                            $expTotal   = explode("/", $total);
                            $hargaTotal = "0";
                            $partProduk = "";
                            $beratKurir = "0";
                            for($i=0; $i<$par; $i++){
                                $expDalem   = explode("|",$expTotal[$i]);
                                $hargaTotal += $expDalem[0];
                            }
                            $hmm    = "";
                            for($i=0; $i<$par; $i++){
                                $expDalem   = explode("|",$expTotal[$i]);
                                if($expDalem[0]==0){
                                    $hmm    .= "0-0|";
                                    $beratKurir += 0;
                                }else{
                                    if($expDalem[9]=="p")
                                        $gafull = "1";
                                    if($expDalem[9]=="f")
                                        $gafull = "0";
                                    $partProduk     .= $expDalem[1]." ".$expDalem[3]." ".$expDalem[2]." ".$expDalem[4]." pcs";
                                    if($par > 1)
                                        $partProduk .= ","; 
                                    $beratKurir += $expDalem[5];
                                    $hmm    .= "1-".$gafull."|";
                                }
                            }
                            $jmlHmm = count(explode("|", $hmm))-1;
                            $expHmm = explode("|", $hmm);
                            $jmlsatu    = 0;
                            $jmlnol     = 0;
                            $jmlgafull  = 0;
                            for($i=0; $i<$jmlHmm; $i++){
                                if($expHmm[$i]=="0-0")
                                    $jmlnol++;
                                if($expHmm[$i]=="1-0"){
                                    $jmlsatu++;
                                }
                                if($expHmm[$i]=="1-1"){
                                    $jmlsatu++;
                                    $jmlgafull++;
                                }
                            }
                            if($jmlnol==$jmlHmm){
                                if($jmlgafull==$jmlHmm){
                                    $isfull     = 0;
                                    $ispartial  = 1;
                                }else{
                                    $isfull     = 1;
                                    $ispartial  = 0;
                                }
                            }
                            if($jmlsatu==$jmlHmm){
                                if($jmlgafull==$jmlHmm){
                                    $isfull     = 0;
                                    $ispartial  = 1;
                                }else{
                                    $isfull     = 1;
                                    $ispartial  = 0;
                                }
                            }
                            if($jmlnol!=$jmlHmm && $jmlsatu!=$jmlHmm){
                                $isfull     = 0;
                                $ispartial  = 1;
                            }
                            Log::info("jumlah Nol : ".$jmlnol." & Jumlah Satu : ".$jmlsatu." & Jumlah Ga Full : ".$jmlgafull);
                            $full       = "Terimakasih sudah order, berikut invoicenya:\n";
                            $partial    = "Maaf kak, hanya dapet ".strtoupper($partProduk)." saja, lainnya fullbooked\n";
                            $kosong     = "Hallo mohon maaf fullbooked. Nunggu cancel/hit&run yaa 🙏🏻😊";
                            $text       = "\nBatas transfer sampai jam 2 siang ini dan dikirim today 💞";
                            $norek      = "BCA  2821492647\nAn. Desi Lieswati\nTerimakasih 🙏🏻❤️";
                            setlocale(LC_MONETARY, 'id_ID');
                            $pesanAkhir = "";
                            if($isfull && ($partProduk=='' || $partProduk==null)){
                                $pesanAkhir = $kosong;
                            }else{
                                if(!$isfull && !$ispartial){
                                    $pesanAkhir = $kosong;
                                }else{
                                    $cost           = $kirimwa->getCost($origin, $destination, $beratKurir,'jne');
                                    $totalan        = $hargaTotal + $cost - $orderid;
                                    $formatHarga    = "Rp. ".number_format($hargaTotal, 2);
                                    $formatCost     = "Rp. ".number_format($cost, 2);
                                    $formatTotal    = "Rp. ".number_format($totalan, 2);
                                    $invoicenum     = $orderController->setInvoiceNum($orderid);
                                    $orderunicid    = $orderController->insertOrder($orderid, $invoicenum, $idCustomer, $hargaTotal, $cost, $orderid, $totalan);
                                    $date   = date('Y-m-d');
                                    $orderpayment   = $orderController->insertOrderPayment($orderunicid, $orderid, $invoicenum, $totalan, $date." 14:00:00");
                                    // $total .= ($jumlah*$harga)."|".$namaproduk."|".$size."|".$warna."|".$jumlah.
                                    //             "|".$beratTotal."|".$idproduk."|".$sku."|".$idstock."/";
                                    for($i=0; $i<$par; $i++){
                                        $expDalem   = explode("|",$expTotal[$i]);
                                        $produkid   = $expDalem[6];
                                        $stockid    = $expDalem[8];
                                        $sku        = $expDalem[7];
                                        $jml        = $expDalem[4];
                                        $hrg        = $expDalem[0]/$jml;
                                        if(($produkid != '' && $stockid != '' && $sku != '')){
                                            $orderController->insertOrderDetail($orderunicid, $produkid, $stockid, $sku, $jml, $hrg, $expDalem[0]);
                                            $orderController->insertOrderDetailExecuted($orderunicid, $produkid, $stockid, $sku, $jml, $hrg, $expDalem[0]);
                                        }
                                    }
                                    if($isfull && !$ispartial){
                                        $pesanAkhir = $full." ".$formatHarga." + ".$formatCost." - ".$orderid." = ".$formatTotal."\n".$text."\n".$norek;
                                    }
                                    if($isfull && $ispartial){
                                        
                                    }
                                    if(!$isfull && $ispartial){
                                        $pesanAkhir = $partial." ".$formatHarga." + ".$formatCost." - ".$orderid." = ".$formatTotal."\n".$text."\n".$norek;
                                    }
                                }
                            }
                            // $kirimwa->sendWA($dari, $pesanAkhir);
                            // $inboxController->updateInbox($inboxid, '1');
                            // $ongkir     = "0";
                            $str        = "OK|1|".$pesanAkhir;
                            Log::info($pesanAkhir);
                        }else{
                            // $inboxController->updateInbox($inboxid, '2');
                            // $ongkir     = "0";
                            $pesan      = "Mohon maaf, kak. Produk ".strtoupper($produk)." tidak ditemukan, mohon input nama produk dengan benar";
                            // $kirimwa->sendWA($dari, $pesan);
                            $str        = "ERR|2|".$pesan;
                            Log::info($pesan);    
                        }
                    }else{
                        // $inboxController->updateInbox($inboxid, '2');
                        // $ongkir     = "0";
                        $pesan      = "Mohon maaf, kak. Kecamatan ".strtoupper($kec)." tidak ditemukan, mohon input nama kota dengan benar";
                        // $kirimwa->sendWA($dari, $pesan);
                        $str        = "ERR|2|".$pesan;    
                        Log::info($pesan);
                    }
                }else{
                    // $inboxController->updateInbox($inboxid, '2');
                    // $ongkir     = "0";
                    $pesan      = "Mohon maaf, kak. Kota ".strtoupper($kota)." tidak ditemukan, mohon input nama kota dengan benar";
                    // $kirimwa->sendWA($dari, $pesan);
                    $str        = "ERR|2|".$pesan;
                    Log::info($pesan);
                }
            }else{
                // $inboxController->updateInbox($inboxid, '2');
                // $ongkir     = "0";
                // $kirimwa->sendWA($dari, $pesan);
                $pesan      = "Mohon maaf, kak. Nomor ".strtoupper($hp)." tidak valid, mohon input nomor HP dengan benar";
                $str        = "ERR|2|".$pesan;
                Log::info($pesan);
            }
        }else{
            $pesan      = "Mohon maaf, kak. Nama Penerima harus diisi";
            $str        = "ERR|2|".$pesan;
            // $inboxController->updateInbox($inboxid, '2');
            // $ongkir     = "0";
            
            // $kirimwa->sendWA($dari, $pesan);
            Log::info($pesan);
        }
        return $str;
    }

    public function validate_mobile($hp){
        return preg_match('/^[0-9]+$/', $hp);
    }
}
