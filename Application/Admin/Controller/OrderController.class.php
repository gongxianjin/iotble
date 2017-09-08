<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class OrderController extends CommonController {
 

    private $mch_id = '1484204972';

    //在类初始化方法中，引入相关类库
    public function _initialize() {
        vendor('Alipay.refund');
        vendor('Weixin.JsApiPay');
    }

    public function index() {
        $conds = array();
        $pay_id = $_GET['pay_id'];
        if($pay_id) {
            $conds['pay_id'] = $pay_id;
        }
        $order_status = $_GET['order_status'];
        if($order_status) {
            $conds['order_status'] = $order_status;
        }
        $order_sn = $_GET['order_sn'];
        if($order_sn) {
            $conds['order_sn'] = $order_sn;
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        $orders = D('Order')->getOrders($conds,$page,$pageSize);
        $count = D("Order")->getOrdersCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();
        $this->assign('pageres',$pageres);
        $this->assign('orders', $orders);
        $this->display();
    }

    public function update() {
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $goods = D("Goods")->getGoodsById($id);
        $this->assign('vo',$goods);
        $this->display();
    }

    public function setback(){
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $order = D("Order")->getOrdersById($id);
        $this->assign('vo',$order);
        $this->display();
    }

    public function backrun(){
        header('Content-type:text/html;charset=utf-8');
        $order_id = $_POST['order_id'];
        if(!$order_id){
            $this->error('非法操作');
        }
        $order = D("Order")->getOrdersById($order_id);
        //添加操作日志
        $adminuser = session('adminUser');
        $log = $adminuser['username'].'退款'.$order['order_sn'];
        $this->addOperLog($log);
        if($order['pay_id'] == 4){
            $refund = new \Refund();
            $out_trade_no = $order['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
            $trade_no = $order['trade_no'];  //订单名称 //必填 通过支付页面的表单进行传递
            $refund_amount = $order['order_amount'];   //付款金额  //必填 通过支付页面的表单进行传递
            $refund_reason = '规定时间未支付完成,设备执行失败';  //订单描述 通过支付页面的表单进行传递
            $out_request_no = $out_trade_no;
            $return = $refund->Refundinfo($out_trade_no,$trade_no,$refund_amount,$refund_reason,$out_request_no);
            if($return->msg == 'Success'){
               //修改订单状态
                M('order_info')->startTrans();
                $Ordermodel = M('order_info');
                $data = array(
                    'order_status' => 3,
                );
                $res = $Ordermodel->where('(order_id="'.$order_id.'")')->save($data);
                if ($res){
                    // 提交事务
                    $Ordermodel->commit();
                }else{
                    // 事务回滚
                    $Ordermodel->rollback();
                }
                show(1,'退款成功');
            }else{
                show(0,'退款失败');
            }
        }elseif($order['pay_id'] == -1){
            $transaction_id = $order['trade_no'];
            $total_fee = $order['order_amount']*100;
            $refund_fee = $order['order_amount']*100;
            $input = new \WxPayRefund();
            $input->SetTransaction_id($transaction_id);
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);
            $input->SetOut_refund_no($this->mch_id.date("YmdHis"));
            $input->SetOp_user_id($this->mch_id);
            $return = \WxPayApi::refund($input);
            if($return['result_code'] == 'SUCCESS'){
                //修改订单状态
                M('order_info')->startTrans();
                $Ordermodel = M('order_info');
                $data = array(
                    'order_status' => 3,
                );
                $res = $Ordermodel->where('(order_id="'.$order_id.'")')->save($data);
                if ($res){
                    // 提交事务
                    $Ordermodel->commit();
                }else{
                    // 事务回滚
                    $Ordermodel->rollback();
                }
                show(1,'退款成功');
            }else{
                show(0,$return['err_code_des']);
            }
        }
    }

}