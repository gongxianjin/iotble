<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
class DatastaticController extends CommonController {
    
    public function index(){
        //销售总额/销售总量
        $ordercount = D('Order')->getOrdersCount(array('order_status'=>1));
        $ordersum = D('Order')->getOrderSum(array('order_status'=>1));
        //已退款总额
        $orderbacksum = D('Order')->getOrderSum(array('order_status'=>3));
        //热销商品
        $maxordergoods = M('order_goods')->field('sum(goods_number) as sum,goods_name')->group('goods_id')->order('sum desc')->find();
        $data_array = M('deviceinfo')->order('pas desc')->select();
        //$data['table'] = $data_array ;
        $b = array_rand($data_array);

        $data = array(
            'deviceid'=>$data_array[$b]['id'],
        );
        $line_one = M('devhistory_day')->where($data)->select();
        if(!$line_one){
            $this->redirect('Datastatic/index');
        }
        $this->assign('ordercount', $ordercount);
        $this->assign('ordersum', $ordersum);
        $this->assign('orderbacksum', $orderbacksum);
        $this->assign('maxordergoods', $maxordergoods);
        $this->assign('line_one', $line_one);
        $this->display();
    }

}