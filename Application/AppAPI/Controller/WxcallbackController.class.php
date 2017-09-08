<?php
namespace AppAPI\Controller;
use Think\Controller;
class WxcallbackController extends Controller {

    //在类初始化方法中，引入相关类库
    public function _initialize() {
        vendor('Weixin.JsApiPay');
    }

    protected function Set_order_back($transaction_id,$order_id){
        M('order_info')->startTrans();
        $Ordermodel = M('order_info');
        $data = array(
            'trade_no' => $transaction_id,
            'order_status' => 2,
            'pay_status' => 1,
            'pay_id'=> -1,
            'pay_name'=>'微信支付',
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

    // 微信支付回调
	public function wxpay_notify(){
        $WxApi=new \JsApiPay();
        $p =$WxApi->notify();
        if($p['code']){
            //商户订单号
            $out_trade_no = $p['out_trade_no'];
            //微信订单号
            $transaction_id = $p['transaction_id']; 

            $order_info = M('order_info')->where('(order_sn="'.$out_trade_no.'") and pay_status=0')->find();
            if($order_info){
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
                }
                // 购买商品数目
                $goods_counts = M('order_goods')
                    ->where('(order_id="'.$order_info['order_id'].'")')
                    ->getField('goods_number');

                $devicelistmodel = M('devicelist');
                $devicelistmodel->startTrans();
                $devicelists = $devicelistmodel->lock()->where('(id="'.$order_info['devicelistid'].'")')->find();
                if($devicelists['sto'] < $goods_counts){
                    // 事务回滚
                    $devicelistmodel->rollback();
                    $this->Set_order_back($transaction_id,$order_info['order_id']);
                    exit('库存不足');
                }
                //修改订单状态
                M('order_info')->startTrans();
                $Order = M('order_info');
                $data = array(
                    'trade_no' => $transaction_id,
                    'pay_status' => 1,
                    'pay_id'=>-1,
                    'pay_name'=>'微信支付',
                    'pay_time'=>time(),
                );
                $res = $Order->where('(order_id="'.$order_info['order_id'].'")')->save($data);
                if ($res){
                    // 提交事务
                    $Order->commit();
                }else{
                    // 事务回滚
                    $Order->rollback();
                    $this->Set_order_back($transaction_id,$order_info['order_id']);
                    exit('订单更新失败');
                }
                //修改库存
                $data = array(
                    'sto' => $devicelists['sto']-$goods_counts,
                );
                $res = $devicelistmodel->where('(id="'.$order_info['devicelistid'].'")')->save($data);
                if ($res) {
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
                        if ($mqtt->connect(true, null, 'server', 'xcent123!@#')) {
                            $mqtt->publish($topic, $reqjson, 0);
                            $mqtt->close();
                        } else {
                            $devicelistmodel->rollback();
                            $this->Set_order_back($transaction_id,$order_info['order_id']);
                            exit('服务器连接失败');
                        }
                        exit('发布成功');
                    } catch (Exception $e) {
                        $devicelistmodel->rollback();
                        $this->Set_order_back($transaction_id,$order_info['order_id']);
                        exit($e->getMessage());
                    }
                    // 提交事务
                    $devicelistmodel->commit();
                }else{
                    // 事务回滚
                    $devicelistmodel->rollback();
                    $this->Set_order_back($transaction_id,$order_info['order_id']);
                }
            }
        }else{ 
            exit('fail');
        }
	}	



	
}