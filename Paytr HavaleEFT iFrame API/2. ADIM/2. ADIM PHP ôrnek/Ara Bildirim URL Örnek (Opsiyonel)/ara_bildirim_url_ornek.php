<?php
// Ara Bildirim URL için örnek kodlar

$post = $_POST;

####################### DÜZENLEMESİ ZORUNLU ALANLAR #######################
#
## API Entegrasyon Bilgileri - Mağaza paneline giriş yaparak BİLGİ sayfasından alabilirsiniz.
$merchant_key 	= 'YYYYYYYYYYYYYY';
$merchant_salt	= 'ZZZZZZZZZZZZZZ';
###########################################################################

####### Bu kısımda herhangi bir değişiklik yapmanıza gerek yoktur. #######
#
## POST değerleri ile hash oluştur.
$hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$post['bank'].$merchant_salt,$merchant_key,true));

## Oluşturulan hash'i, paytr'dan gelen post içindeki hash ile karşılaştır (isteğin paytr'dan geldiğine ve değişmediğine emin olmak için)
## Bu işlemi yapmazsanız maddi zarara uğramanız olasıdır.
if( $hash != $post['hash'] )
    die('PAYTR notification failed: bad hash');
###########################################################################

## DÖNÜLEN POST DEĞERLERİ
/*
    $post[merchant_oid]      => Sipariş Numarası
    $post[status]            => "info"
    $post[hash]              => PayTR Tarafından Hesaplanan Hash Değeri

    ## AŞAĞIDAKİLER MÜŞTERİNİN FORMA GİRDİĞİ BİLGİLERDİR ##
    $post[payment_sent_date] => Ödeme Yapılan Tarih
    $post[bank]              => Ödeme Yapılan Banka
    $post[user_name]         => Ödeme Yapan Adı Soyadı
    $post[user_phone]        => Ödeme Yapan Telefon Numarası
    $post[tc_no_last5]       => T.C. Kimlik Numarası Son 5 Hanesi
*/
###########################################################################