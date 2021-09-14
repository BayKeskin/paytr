<?php
Class PayTR
{
    public $merchant_id 	= '104521';
	public $merchant_key 	= 'uH1pZmfmRcCHENk9';
	public $merchant_salt	= '4dNALiEG3T7PAxMS';
    public $basariliadres ="";
    public $basarisizadres ="";
    public $local ="";
    
    function formGetir($mail,$siparisid,$adsoyad,$adres,$telefon,$siparisler=array(),$fiyat,$fiyattur)
    {
            global $siteURL;
        	$merchant_id 	= $this->merchant_id;
        	$merchant_key 	= $this->merchant_key;
        	$merchant_salt	= $this->merchant_salt;
        	$email =  $mail;
        	#

            $payment_amount	= $fiyat * 100;

        	$merchant_oid = $siparisid;

        	$user_name = $adsoyad;

        	$user_address = $adres;

        	$user_phone = $telefon;

        	$merchant_ok_url = $this->basariliadres;

        	$merchant_fail_url = $this->basarisizadres;

        	$user_basket = $siparisler;
        	#
        	
        	$user_basket = base64_encode(json_encode($siparisler));
        	
        	############################################################################################
        
        	## Kullan�c�n�n IP adresi
        	if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
        		$ip = $_SERVER["HTTP_CLIENT_IP"];
        	} elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
        		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        	} else {
        		$ip = $_SERVER["REMOTE_ADDR"];
        	}
        

        	if($this->local=="local")
            {
                $user_ip="151.250.98.186";
            }else
            {
                $user_ip=$ip;
            }
            
        	##
        

        	$timeout_limit = "30";

        	if($this->local=="local")
            {
                $debug_on = 1;
            }else
            {
                $debug_on = 0;
            }
            
        
            ## Ma�aza canl� modda iken test i�lem yapmak i�in 1 olarak g�nderilebilir.
            if($this->local=="local")
            {
                $test_mode = 1;
            }else
            {
                $test_mode = 0;
            }
            
        
        	$no_installment	= 0; // Taksit yap�lmas�n� istemiyorsan�z, sadece tek �ekim sunacaksan�z 1 yap�n
        
        	## Sayfada g�r�nt�lenecek taksit adedini s�n�rlamak istiyorsan�z uygun �ekilde de�i�tirin.
        	## S�f�r (0) g�nderilmesi durumunda y�r�rl�kteki en fazla izin verilen taksit ge�erli olur.
        	$max_installment = 0;
        
        	$currency = "TL";
        
        	####### Bu k�s�mda herhangi bir de�i�iklik yapman�za gerek yoktur. #######
        	$hash_str = $merchant_id .$user_ip .$merchant_oid .$email .$payment_amount .$user_basket.$no_installment.$max_installment.$currency.$test_mode;
        	$paytr_token=base64_encode(hash_hmac('sha256',$hash_str.$merchant_salt,$merchant_key,true));
        	$post_vals=array(
        			'merchant_id'=>$merchant_id,
        			'user_ip'=>$user_ip,
        			'merchant_oid'=>$merchant_oid,
        			'email'=>$email,
        			'payment_amount'=>$payment_amount,
        			'paytr_token'=>$paytr_token,
        			'user_basket'=>$user_basket,
        			'debug_on'=>$debug_on,
        			'no_installment'=>$no_installment,
        			'max_installment'=>$max_installment,
        			'user_name'=>$user_name,
        			'user_address'=>$user_address,
        			'user_phone'=>$user_phone,
        			'merchant_ok_url'=>$merchant_ok_url,
        			'merchant_fail_url'=>$merchant_fail_url,
        			'timeout_limit'=>$timeout_limit,
        			'currency'=>$currency,
                    'test_mode'=>$test_mode
        		);
        	
        	$ch=curl_init();
        	curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($ch, CURLOPT_POST, 1) ;
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        	$result = @curl_exec($ch);
        
        	if(curl_errno($ch))
        		die("PAYTR IFRAME connection error. err:".curl_error($ch));
        
        	curl_close($ch);
        	
        	$result=json_decode($result,1);
        		
        	if($result['status']=='success')
        		$token=$result['token'];
        	else
        		die("PAYTR IFRAME failed. reason:".$result['reason']);
        	#########################################################################
            return $token;
    }
    
    function payTrOnay()
    {
        global $db;     
    	$post = $_POST;
    
    	####################### D�ZENLEMES� ZORUNLU ALANLAR #######################
    	#
    	## API Entegrasyon Bilgileri - Ma�aza paneline giri� yaparak B�LG� sayfas�ndan alabilirsiniz.
    	$merchant_key 	= $this->merchant_key;
    	$merchant_salt	= $this->merchant_salt;
    	###########################################################################
    
    	####### Bu k�s�mda herhangi bir de�i�iklik yapman�za gerek yoktur. #######
    	#
    	## POST de�erleri ile hash olu�tur.
    	$hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );
    	#
    	## Olu�turulan hash'i, paytr'dan gelen post i�indeki hash ile kar��la�t�r (iste�in paytr'dan geldi�ine ve de�i�medi�ine emin olmak i�in)
    	## Bu i�lemi yapmazsan�z maddi zarara u�raman�z olas�d�r.
    	if( $hash != $post['hash'] )
    		die('PAYTR notification failed: bad hash');
            if($db->veriSaydir("gecicisiparisler",array("SiparisID"),array($post['merchant_oid']))<1)
            {
                exit;
            }
    	   ###########################################################################
    
    	   ## BURADA YAPILMASI GEREKENLER
    	   ## 1) Sipari�in durumunu $post['merchant_oid'] de�erini kullanarak veri taban�n�zdan sorgulay�n.
    	   ## 2) E�er sipari� zaten daha �nceden onayland�ysa veya iptal edildiyse  echo "OK"; exit; yaparak sonland�r�n.
    	if( $post['status'] == 'success' ) { ## �deme Onayland�
            
            $tur = $db->VeriOkuTek("gecicisiparisler","SiparisTUR","SiparisID",$post['merchant_oid']);
            $id =  $db->VeriOkuTek("gecicisiparisler","SiparisTURID","SiparisID",$post['merchant_oid']);
            if($tur =="Vitrin")
            {
                $db->VeriOkuCoklu("gecici_siparis_uye_vitrin",array("VitrinID"),array($id));
                foreach($db->bilgial as $row)
                {
                     $bugun = date("Y-m-d");
                     $yenitarih = strtotime('365 day',strtotime($bugun));
                     $yenitarih = date('Y-m-d',$yenitarih);
                     
                     
                     $ekle =$db->veriEkleSayiAl("uye_vitrin",array("NULL","?","?","?","?","?","?","?","?","?","?","?","NOW()","?"),
                     array($_SESSION["KullaniciID"],$_POST["VitrinBASLIK"],$_POST["VitrinACIKLAMA"],$_POST["VitrinADRES"],
                     $yeniadres,$yeniadres2,$_POST["VitrinFACEBOOK"],$_POST["VitrinTWITTER"],$_POST["VitrinGOOGLE"],
                     $_POST["VitrinINSTAGRAM"],$_POST["VitrinKATEGORIID"],$yenitarih
                     ));
                }
            }
    		## BURADA YAPILMASI GEREKENLER
    		## 1) Sipari�i onaylay�n.
    		## 2) E�er m��terinize mesaj / SMS / e-posta gibi bilgilendirme yapacaksan�z bu a�amada yapmal�s�n�z.
    		## 3) 1. ADIM'da g�nderilen payment_amount sipari� tutar� taksitli al��veri� yap�lmas� durumunda
    		## de�i�ebilir. G�ncel tutar� $post['total_amount'] de�erinden alarak muhasebe i�lemlerinizde kullanabilirsiniz.
    
    	} else { ## �demeye Onay Verilmedi
            $db->veriEkle("gecici_siparis_odeme_hatalari",array("NULL","?","?","?"),array($post['failed_reason_code'],$post['failed_reason_msg'],$post['merchant_oid']));
    		## BURADA YAPILMASI GEREKENLER
    		## 1) Sipari�i iptal edin.
    		## 2) E�er �demenin onaylanmama sebebini kay�t edecekseniz a�a��daki de�erleri kullanabilirsiniz.
    		## $post['failed_reason_code'] - ba�ar�s�z hata kodu
    		## $post['failed_reason_msg'] - ba�ar�s�z hata mesaj�
    
    	}
    
    	## Bildirimin al�nd���n� PayTR sistemine bildir.
    	echo "OK";
    	exit;
    }
}


?>