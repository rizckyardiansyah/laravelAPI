<?php

namespace App\Listeners;

use App\Events\Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Inbox;
use Log; 
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\kirimWAController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PenjualanController;

class EventSubscriber implements ShouldQueue
{
    use InteractsWithQueue;
    public  $connection = 'database';
    public  $queue = 'listeners';
    private $cc;
    private $kirimwa;
    private $orderController;
    private $inboxController;
    private $pController;
    // public $delay = 2;
    /**
     * Handle inbox events
     */
    public function sendWAReply($event){
        date_default_timezone_set('Asia/Jakarta');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $origin     = '115';
        $cc         = new CustomerController();
        $kirimwa    = new kirimWAController();
        $product    = new ProductController();
        $orderController    = new OrderController();
        $inboxController    = new InboxController();
        $pController= new PenjualanController();
        $msg        = explode('#', $event->data[2]);
        if(count($msg)>0){
            $dari       = $event->data[0];
            $kepada     = $event->data[1];
            if(count($msg) < 6 )
                $pesan  = "";
            else
                $pesan      = $msg[0]."#".$msg[1]."#".$msg[2]."#".$msg[3]."#".$msg[4];
            $cekInbox   = $inboxController->cekInboxDouble($dari, $kepada, $pesan, $date);
            $getInboxAll= $inboxController->getInboxVal($dari, $kepada, $pesan, $date);
            $inboxid    = "";
            foreach($getInboxAll as $ib){
                $inboxid    = $ib->id;
            }
            Log::info("hasil cekInbox -> ".$cekInbox);
            if($cekInbox == 1){
                $orderid        = $orderController->getOrderId();
                Log::info("hasil getInboxId : ".$inboxid);
                Log::info("count # ".count($msg));
                if(count($msg) == 6){
                    $test = $cc->getCityId($msg[4]);
                    $destination    = '';
                    $provinceId     = '';
                    foreach($test as $sp){
                        $destination    = $sp->city_id;
                        $provinceId     = $sp->province_id;
                    }
                    if($destination != '' && $provinceId != ''){
                        $cekCustomer        = $cc->cekCustomer($msg[0], $msg[1], $msg[2], $msg[3], $destination, $provinceId);
                        $idCustomer         = "";
                        foreach($cekCustomer as $ck){
                            $idCustomer     = $ck->record_id;
                        }
                        if($idCustomer == ""){
                            $idCustomer         = $cc->insertCustomer($msg[0], $msg[1], $msg[2], $msg[3], $destination, $provinceId);
                        }
                        $multi  = explode(',', $msg[5]);
                        $cMulti = count($multi);
                        $pesan  = "";
                        $total  = "";
                        foreach($multi as $m){
                            $spl    = explode(' ', $m);
                            $count  = count($spl);
                            if($count < 3){
                                $pesan .= "Silakan cek format order \nNAMA#NO_HP#ALAMAT#KECAMATAN#KOTA/KAB#NamaProduk Warna Ukuran Jumlah";
                                Log::info($pesan);
                            }else{
                                if($count == 3){
                                    $namaproduk = $spl[0];
                                    $warna      = $spl[1];
                                    $size       = $spl[2];
                                    $jumlah     = '1';    
                                }if($count == 4){
                                    if(is_numeric($spl[3])){
                                        $namaproduk = $spl[0];
                                        $warna      = $spl[1];
                                        $size       = $spl[2];
                                        $jumlah     = $spl[3];
                                    }else{
                                        $namaproduk = $spl[0];
                                        $warna      = $spl[1]." ".$spl[2];
                                        $size       = $spl[3];
                                        $jumlah     = '1';
                                    }
                                }if($count == 5){
                                    $namaproduk = $spl[0];
                                    $warna      = $spl[1]." ".$spl[2];
                                    $size       = $spl[3];
                                    $jumlah     = $spl[4];
                                }
                                Log::info("isian -> ".$namaproduk."|".$warna."|".$size."|".$jumlah);
                                $cekWarna   = $product->getWarna($warna);
                                $getWarna   = $product->getWarnaAll();
                                $cekSize    = $product->getSize($size);
                                $getSize    = $product->getSizeAll();
                                $cekProduk  = $product->getProduk($namaproduk);
                                $getProduk  = $product->getProdukAll();
                                $colour     = "";$ukuran = "";$produk = "";
                                foreach($getWarna as $gw){
                                    $colour .= $gw->warna.', ';
                                }
                                foreach($getSize as $gw){
                                    $ukuran .= $gw->size.', ';
                                }
                                foreach($getProduk as $gw){
                                    $produk .= $gw->namaproduk.', ';
                                }
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
                                }elseif(count($cekProduk)==0){
                                    $pesan .= "Hallo kak, Produk (".$namaproduk."), tidak tersedia di keyzaa.id. Berikut adalah nama produk yang bisa kamu pilih : [".$produk."]";
                                    $total .= "0|".$namaproduk."|".$size."|".$warna."|".$jumlah."|".$beratTotal.
                                    "|".$idproduk."|".$sku."|".$idstock."|".$role."/";
                                    // Log::info($pesan);
                                }else{
                                    $stock = $product->getStock($namaproduk, $warna, $size, $jumlah);
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
                        $norek      = "BCA  2821492647\nMandiri 1320012633260\nAn. Desi Lieswati\nTerimakasih 🙏🏻❤️";
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
                        $kirimwa->sendWA($dari, $pesanAkhir);
                        $inboxController->updateInbox($inboxid, '1');
                        $ongkir     = "0";
                    }else{
                        $inboxController->updateInbox($inboxid, '2');
                        $ongkir     = "0";
                        $pesan = "Mohon maaf, kak. Kota ".strtoupper($msg[4])." tidak ditemukan, mohon input nama kota dengan benar";
                        $kirimwa->sendWA($dari, $pesan);
                        Log::info($pesan);
                    }
                    
                    // Log::info($pesanAkhir);
                }elseif(count($msg) == 7){
                    $penjualan  = $pController->singleOrder($msg[0], $msg[1], $msg[2], $msg[3], $msg[4], $msg[5], $msg[6], $inboxid, $dari, $kepada, $orderid);
                    $spl        = explode('|', $penjualan);
                    $code       = $spl[0];
                    $errcode    = $spl[1];
                    $msg        = $spl[2];
                    $inboxController->updateInbox($inboxid, $errcode);
                    $kirimwa->sendWA($dari, $msg);
                    Log::info($msg);
                }elseif(count($msg) == 2){
                    if(strtolower($msg[0]) == 'warna'){
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
                    }
                }else{
                    $inboxController->updateInbox($inboxid, '2');
                    $pesanAkhir = "Silakan cek format order \nNAMA#NO_HP#ALAMAT#KECAMATAN#KOTA/KAB#NamaProduk Warna Ukuran Jumlah,NamaProduk Warna Ukuran Jumlah\n";
                    $pesanAkhir .= "Contoh Order Single Product\n";
                    $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1\n\n";
                    $pesanAkhir .= "Contoh Order Multiple Product\n";
                    $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1,byan dark maroon L 5";
                    $kirimwa->sendWA($dari, $pesanAkhir);
                    Log::info($pesanAkhir);
                }
            }
            else{
                $inboxController->updateInbox($inboxid, '2');
                $pesanAkhir = "Silakan cek format order \nNAMA#NO_HP#ALAMAT#KECAMATAN#KOTA/KAB#NamaProduk Warna Ukuran Jumlah,NamaProduk Warna Ukuran Jumlah\n";
                $pesanAkhir .= "Contoh Order Single Product\n";
                $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1\n\n";
                $pesanAkhir .= "Contoh Order Multiple Product\n";
                $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1,byan dark maroon L 5";
                // $this->callUrl($event->data[0], $pesan);
                Log::info($pesanAkhir);
            }
        }
        else{
            $inboxController->updateInbox($inboxid, '2');
            $pesanAkhir = "Silakan cek format order \nNAMA#NO_HP#ALAMAT#KECAMATAN#KOTA/KAB#NamaProduk Warna Ukuran Jumlah,NamaProduk Warna Ukuran Jumlah\n";
            $pesanAkhir .= "Contoh Order Single Product\n";
            $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1\n\n";
            $pesanAkhir .= "Contoh Order Multiple Product\n";
            $pesanAkhir .= "KEYZAA#08123456789#Pacific Century Place tower, Jl. Jend Sudirman no 53, RT01/01, Senayan#Kebayoran Lama#Jakarta Selatan#byan white S 1,byan dark maroon L 5";
            Log::info($pesanAkhir);
        }
    }

    private function singleOrder(){

    }
    
    /**
     * Register the listeners for the subscriber
     */
    public function subscribe($event){
        $event->listen(
            'App\Events\Event',
            'App\Listeners\EventSubscriber@sendWAReply'
        );
    }
}
