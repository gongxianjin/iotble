<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    
    public function index(){

//        $news = D('News')->maxcount();
//        $newscount = D('News')->getNewsCount(array('status'=>1));
//        $positionCount = D('Position')->getCount(array('status'=>1));
//        $this->assign('news', $news);
//        $this->assign('newscount', $newscount);;
//        $this->assign('positioncount', $positionCount);

        //今日登录用户数
        $adminCount = D("Admin")->getLastLoginUsers();
        //销售总额/销售总量
//        $ordercount = D('Order')->getOrdersCount(array('order_status'=>1));
//        $ordersum = D('Order')->getOrderSum(array('order_status'=>1));
        //已退款总额
//        $orderbacksum = D('Order')->getOrderSum(array('order_status'=>3));

        //热销商品
//        $maxordergoods = M('order_goods')->field('sum(goods_number) as sum,goods_name')->group('goods_id')->order('sum desc')->find();
        $this->assign('admincount', $adminCount);
//        $this->assign('ordercount', $ordercount);
//        $this->assign('ordersum', $ordersum);
//        $this->assign('orderbacksum', $orderbacksum);
//        $this->assign('maxordergoods', $maxordergoods);
        $this->display();
    }

}