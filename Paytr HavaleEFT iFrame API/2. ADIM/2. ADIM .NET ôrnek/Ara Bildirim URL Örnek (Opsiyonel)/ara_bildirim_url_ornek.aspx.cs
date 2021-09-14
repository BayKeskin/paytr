// Ara Bildirim URL için örnek kodlar

using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Cryptography;
using System.Text;
using System.Web;
using System.Net.Mail;
using System.Web.UI;
using System.Web.UI.WebControls;

public partial class ara_bildirim_url_ornek : System.Web.UI.Page {

    // ####################### DÜZENLEMESİ ZORUNLU ALANLAR #######################
    //
    // API Entegrasyon Bilgileri - Mağaza paneline giriş yaparak BİLGİ sayfasından alabilirsiniz.
    string merchant_key     = "YYYYYYYYYYYYYY";
    string merchant_salt    = "ZZZZZZZZZZZZZZ";
    // ###########################################################################

    protected void Page_Load(object sender, EventArgs e) {

        // ####### Bu kısımda herhangi bir değişiklik yapmanıza gerek yoktur. #######
        // 
        // POST değerleri ile hash oluştur.
        string merchant_oid = Request.Form["merchant_oid"];
        string merchant_oid = Request.Form["bank"];
        string hash = Request.Form["hash"];

        string Birlestir = string.Concat(merchant_oid, bank, merchant_salt);
        HMACSHA256 hmac = new HMACSHA256(Encoding.UTF8.GetBytes(merchant_key));
        byte[] b = hmac.ComputeHash(Encoding.UTF8.GetBytes(Birlestir));
        string token = Convert.ToBase64String(b);

        //
        // Oluşturulan hash'i, paytr'dan gelen post içindeki hash ile karşılaştır (isteğin paytr'dan geldiğine ve değişmediğine emin olmak için)
        // Bu işlemi yapmazsanız maddi zarara uğramanız olasıdır.
        if (hash.ToString() != token) {
            Response.Write("PAYTR notification failed: bad hash");
            return;
            }

        //###########################################################################

        //## DÖNÜLEN POST DEĞERLERİ
        /*
         Request.Form[merchant_oid]      => Sipariş Numarası
         Request.Form[status]            => "info"
         Request.Form[hash]              => PayTR Tarafından Hesaplanan Hash Değeri

         ## AŞAĞIDAKİLER MÜŞTERİNİN FORMA GİRDİĞİ BİLGİLERDİR ##
         Request.Form[payment_sent_date] => Ödeme Yapılan Tarih
         Request.Form[bank]              => Ödeme Yapılan Banka
         Request.Form[user_name]         => Ödeme Yapan Adı Soyadı
         Request.Form[user_phone]        => Ödeme Yapan Telefon Numarası
         Request.Form[tc_no_last5]       => T.C. Kimlik Numarası Son 5 Hanesi
        */
        //###########################################################################
    }
}