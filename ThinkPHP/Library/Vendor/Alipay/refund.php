<?php
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AlipayTradeRefundContentBuilder.php';

class Refund{

    public function Refundinfo($out_trade_no,$trade_no,$refund_amount,$refund_reason,$out_request_no){

        $config['app_id'] = C("ALIPAY.app_id");
        $config['merchant_private_key'] = C("ALIPAY.merchant_private_key");
        $config['notify_url'] = C("ALIPAY.notify_url");
        $config['return_url'] = C("ALIPAY.return_url");
        $config['charset'] = C("ALIPAY.charset");
        $config['sign_type'] = C("ALIPAY.sign_type");
        $config['gatewayUrl'] = C("ALIPAY.gatewayUrl");
        $config['alipay_public_key'] = C("ALIPAY.alipay_public_key");

        $RequestBuilder = new \AlipayTradeRefundContentBuilder();
        $RequestBuilder->setTradeNo($trade_no);
        $RequestBuilder->setOutTradeNo($out_trade_no);
        $RequestBuilder->setRefundAmount($refund_amount);
        $RequestBuilder->setRefundReason($refund_reason);
        $RequestBuilder->setOutRequestNo($out_request_no);

        $Response = new \AlipayTradeService($config);
        $result=$Response->Refund($RequestBuilder);
        return $result;
    }



}