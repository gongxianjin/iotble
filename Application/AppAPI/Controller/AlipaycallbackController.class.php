<?php
namespace AppAPI\Controller;
use Think\Controller;

class AlipaycallbackController extends Controller {

    //在类初始化方法中，引入相关类库
    public function _initialize() {
        vendor('Alipay.AlipayTradeService');
    }

    protected function Set_order_back($trade_no,$order_id){
        M('order_info')->startTrans();
        $Ordermodel = M('order_info');
        $data = array(
            'trade_no' => $trade_no,
            'order_status' => 2,
            'pay_status' => 1,
            'pay_id'=>4,
            'pay_name'=>'支付宝(手机版)',
            'pay_time'=>time(),
        );
        $res = $Ordermodel->where('(order_id="'.$order_id.'")')->save($data);
        if ($res){
            // 提交事务
            $Ordermodel->commit();
        }else{
            // 事务回滚
            $Ordermodel->rollback();
        }
        //发送短信通知管理员，进行退款操作

    }

    // 支付宝支付回调支付宝支付回调
	public function alipay_notify(){
        $config = array(
            'app_id'=>C("ALIPAY.app_id"),
            'merchant_private_key'=>C("ALIPAY.merchant_private_key"),
            'notify_url'=>C("ALIPAY.notify_url"),
            'return_url'=>C("ALIPAY.return_url"),
            'charset'=>C("ALIPAY.charset"),
            'sign_type'=>C("ALIPAY.sign_type"),
            'gatewayUrl'=>C("ALIPAY.gatewayUrl"),
            'alipay_public_key'=>C("ALIPAY.alipay_public_key"),
        );  
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($_POST);
        /* 实际验证过程建议商户添加以下校验。
          1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
          2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
          3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
          4、验证app_id是否为该商户本身。
          */
        if($result) {
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
                // 1 判断订单是否过期
                $order_info = M('order_info')->where('(order_sn="'.$out_trade_no.'") and pay_status=0')->find();
                if($order_info){
                    //修改支付日志
                    M('pay_log')->startTrans();
                    $paylogmodel = M('pay_log');
                    $data = array(
                        'is_paid'=> 1
                    );
                    $res = $paylogmodel->where('(order_id="'.$order_info['order_id'].'")')->save($data);
                    if ($res){
                        // 提交事务
                        $paylogmodel->commit();
                    }else{
                        // 事务回滚
                        $paylogmodel->rollback();
                        $this->Set_order_back($trade_no,$order_info['order_id']);
                    }
                    // 对比购买商品数目
                    $goods_counts = M('order_goods')
                        ->where('(order_id="'.$order_info['order_id'].'")')
                        ->getField('goods_number');
                    $devicelistmodel = M('devicelist');
                    $devicelistmodel->startTrans();
                    $devicelists = $devicelistmodel->lock()->where('(id="'.$order_info['devicelistid'].'")')->find();
                    if($devicelists['sto'] < $goods_counts){
                        // 事务回滚
                        $devicelistmodel->rollback();
                        $this->Set_order_back($trade_no,$order_info['order_id']);
                        exit('库存不足');
                    }
                    //修改订单状态
                    M('order_info')->startTrans();
                    $Order = M('order_info');
                    $data = array(
                        'trade_no' => $trade_no,
                        'pay_status' => 1,
                        'pay_id'=>4,
                        'pay_name'=>'支付宝(手机版)',
			            'pay_time'=>time(),
                    );
                    $res = $Order->where('(order_id="'.$order_info['order_id'].'")')->save($data);
                    if ($res){
                        // 提交事务
                        $Order->commit();
                    }else{
                        // 事务回滚
                        $Order->rollback();
                        $this->Set_order_back($trade_no,$order_info['order_id']);
                        exit('订单更新失败');
                    }
                    //修改库存
                    $data = array(
                        'sto' => $devicelists['sto']-$goods_counts,
                    );
                    $res = $devicelistmodel->where('(id="'.$order_info['devicelistid'].'")')->save($data);
                    if ($res){
                        try {
                            //库存修改成功
                            $device_id = $devicelists['deviceid'];
                            $data_dev['id'] = $device_id;
                            $data_dev['sn'] = $out_trade_no;
                            $data_dev['lane'] = intval($devicelists['lane']);
                            $data_dev['sto'] = intval($devicelists['sto'] - $goods_counts);
                            $data_dev['num'] = intval($goods_counts);
                            $data_dev['lock'] = 0;
                            $reqjson = json_encode($data_dev);
                            $mqtt = new \Org\Util\phpMQTT("120.77.245.43", 1883, "iotserver_victor_test"); //Change client name to something unique
                            $topic = "/zq100n/dev/sub/$device_id";
                            if ($mqtt->connect(true,null,'server','xcent123!@#')) {
                                $mqtt->publish($topic,$reqjson,0);
                                $mqtt->close();
                            }else{
                                $devicelistmodel->rollback();
                                $this->Set_order_back($trade_no,$order_info['order_id']);
                                exit('服务器连接失败');
                            }
                        }catch(Exception $e) {
                            $devicelistmodel->rollback();
                            $this->Set_order_back($trade_no,$order_info['order_id']);
                            exit($e->getMessage());
                        }
                        // 提交事务
                        $devicelistmodel->commit();
                    }else{
                        // 事务回滚
                        $devicelistmodel->rollback();
                        $this->Set_order_back($trade_no,$order_info['order_id']);
                    }
                    exit('发布成功');
                }
            }
            exit('success');		//请不要修改或删除
        }else{
            //验证失败
            exit('fail');//请不要修改或删除
        }

	}



	
}