<?php
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AlipayTradeWapPayContentBuilder.php';

class Pay{

    public function Payinfo($order,$ordername,$price,$ordertitial){
    //商户订单号，商户网站订单系统中唯一订单号，必填
    $out_trade_no = $order;
    //订单名称，必填
    $subject = $ordername;
    //付款金额，必填
    $total_amount = $price;
    //商品描述，可空
    $body = $ordertitial;
    //超时时间
    $timeout_express="1m";
    $config['app_id'] = C("ALIPAY.app_id");
    $config['merchant_private_key'] = C("ALIPAY.merchant_private_key");
    $config['notify_url'] = C("ALIPAY.notify_url");
    $config['return_url'] = C("ALIPAY.return_url");
    $config['charset'] = C("ALIPAY.charset");
    $config['sign_type'] = C("ALIPAY.sign_type");
    $config['gatewayUrl'] = C("ALIPAY.gatewayUrl");
    $config['alipay_public_key'] = C("ALIPAY.alipay_public_key");
    $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
    $payRequestBuilder->setBody($body);
    $payRequestBuilder->setSubject($subject);
    $payRequestBuilder->setOutTradeNo($out_trade_no);
    $payRequestBuilder->setTotalAmount($total_amount);
    $payRequestBuilder->setTimeExpress($timeout_express);
    $payResponse = new \AlipayTradeService($config);
    $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
    }

    //生成订单编号
    public function  getOrderNum(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}