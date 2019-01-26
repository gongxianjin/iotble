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

        //分析仪(个数)
        $machinecount = D('Machine')->getMachineCount();
        //用户数
        $usercount = M('admin')->where(array('status'=>1))->count();
        //地点个数
        $pondaddrcount = M('dev_address')->count();

        //销售总额/销售总量
//        $ordercount = D('Order')->getOrdersCount(array('order_status'=>1));
//        $ordersum = D('Order')->getOrderSum(array('order_status'=>1));
        //已退款总额
//        $orderbacksum = D('Order')->getOrderSum(array('order_status'=>3));

        //热销商品
//        $maxordergoods = M('order_goods')->field('sum(goods_number) as sum,goods_name')->group('goods_id')->order('sum desc')->find();
        $this->assign('admincount', $adminCount);
        $this->assign('machinecount', $machinecount);
        $this->assign('usercount', $usercount);
        $this->assign('pondaddrcount', $pondaddrcount);
//        $this->assign('ordersum', $ordersum);
//        $this->assign('orderbacksum', $orderbacksum);
//        $this->assign('maxordergoods', $maxordergoods);


        $conds = array();
        $deviceid = $_GET['deviceid'];
        if($deviceid) {
            $conds['deviceid'] = $deviceid;
        }
        $devicename = $_GET['devicename'];
        if($devicename) {
            $conds['devicename'] = $devicename;
        }
        $username = $_GET['username'];
        if($username) {
            $con['username'] = array('like','%'.$username.'%');
            $res = M('admin')->where($con)->find();
            if(!empty($res)){
                $conds['admin_id'] = $res['admin_id'];
            }else{
                $conds['admin_id'] = 0;
            }
        }

        if($_SESSION['adminUser']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $conds['company_id'] = $_SESSION['adminUser']['company_id'];
            //检测员查看自己上传的数据
            $data = array(
                'name' => array('in','检测员'),
            );
            $sys = M('sysrole')->where($data)->find();
            if($_SESSION['adminUser']['role_id'] == $sys['id']){
                $conds['admin_id'] = $_SESSION['adminUser']['admin_id'];
                $this->test_id = $conds['admin_id'];
            }
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        $machine = D('Machine')->getMachine($conds,$page,$pageSize);
        $count = D("Machine")->getMachineCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();
        $this->assign('pageres',$pageres);
        $this->assign('machines', $machine);

        $this->display();
    }

}