<?php
namespace App\Model\Tool;

use Endroid\QrCode\QrCode;

class QRCodeGeneration
{
    public function generateQRCodeForPayment(String $data = '请使用微信或者支付宝扫描付款')
    {
        app('debugbar')->info(env('PURCHASE_QRCODE_URL_WECHATPAY'));
        $qrcode = new QrCode($data);
        //$qrcode->writeFile('./imgsrc/qrcode.png');
        $qrcode->setLabel('长按二维码识别');
        return response($qrcode->writeString(), 200)->header('Content-Type', $qrcode->getContentType());
    }

    public function generatePaymentRedirectQRCode()
    {
        $qrcode = new QrCode(url('charge-redirect'));
        $qrcode->setLabel('可使用支付宝/微信直接扫描付款');
        $qrcode->writeFile('./imgsrc/qrcode.png');
        return '生成成功，可到充值界面查看生成的二维码';
    }

}