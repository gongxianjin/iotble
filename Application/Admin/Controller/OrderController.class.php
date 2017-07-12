<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class OrderController extends CommonController {

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

//    public function save() {
//        $goods_id = $_POST['goods_id'];
//        if(!$goods_id){
//            $this->error('非法操作');
//        }
//        $data['goods_name'] = $_POST['goods_name'];
//        $data['shop_price'] = $_POST['shop_price'];
//        $data['market_price'] = $_POST['shop_price'];
//        $data['goods_img'] = $_POST['goods_img'];
//
//        try {
//            //添加操作日志
//            $log = '编辑订单'.$data['goods_name'];
//            $this->addOperLog($log);
//            $id = D("Goods")->updateByGoodsId($goods_id, $data);
//            if($id === false) {
//                return show(0, '更新失败');
//            }
//            return show(1, '更新成功');
//        }catch(Exception $e) {
//            return show(0, $e->getMessage());
//        }
//    }

}