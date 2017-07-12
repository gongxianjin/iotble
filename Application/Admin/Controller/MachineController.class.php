<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class MachineController extends CommonController {

    public function index() {
        $conds = array();
        $id = $_GET['id'];
        if($id) {
            $conds['id'] = $id;
        }
        $devicename = $_GET['devicename'];
        if($devicename) {
            $conds['devicename'] = $devicename;
        }

        $devicetype = $_GET['devicetype'];
        if($devicetype) {
            $conds['devicetype'] = $devicetype;
        }

        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;

        $machine = D('Machine')->getMachine($conds,$page,$pageSize);
//        dump($machine);die;
        $count = D("Machine")->getMachineCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();

        $this->assign('pageres',$pageres);
        $this->assign('machines', $machine);
        $this->display();
    }

    public function edit() {
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $mechine = D("Machine")->getMachineById($id);
        $this->assign('vo',$mechine);
        //设备类别
        $devicetype = M('devicetype')->select();
        $this->assign('devicetype',$devicetype);
        //会员
        $users = M('user')->select();
        $this->assign('users',$users);
        //商品
        $goods = M('goods')->select();
        $this->assign('goods',$goods);
        $this->display();
    }

    public function save() {
        $dev_id= $_POST['dev_id'];
        if(!$dev_id){
            $this->error('非法操作');
        }
        $data['devicename'] = $_POST['devicename'];
        $data['devicetype'] = $_POST['devicetypeid'];
        $data['goods_id'] = $_POST['goods_id'];
        $data['userid'] = $_POST['user_id'];
        //添加操作日志
        $log = '编辑设备'.$data['devicename'];
        $this->addOperLog($log);
        try {
            $id = D("Machine")->updateByDevId($dev_id, $data);
            if($id === false) {
                return show(0, '更新失败');
            }
            return show(1, '更新成功');
        }catch(Exception $e) {
            return show(0, $e->getMessage());
        }
    }

}