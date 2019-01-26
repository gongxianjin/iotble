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

    public function add(){
        if($_POST) {
            if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
                return show(0,'内置分析仪ID不能为空');
            }
            if(!isset($_POST['devicename']) || !$_POST['devicename']) {
                return show(0,'分析仪名称不能为空');
            }
            $company_id   = !empty($_POST['company_id'])   ? intval($_POST['company_id'])   : 0;
            $admin_id = !empty($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
            if(!isset($_POST['begin_time']) || !$_POST['begin_time']) {
                return show(0,'开始时间不能为空');
            }
            if(!isset($_POST['end_time']) || !$_POST['end_time']) {
                return show(0,'结束时间不能为空');
            }
            $data = array(
                'deviceid' => $_POST['deviceid'],
                'devicename'   => $_POST['devicename'],
                'admin_id' => $admin_id,
                'company_id' => $company_id,
                'start_time' => strtotime($_POST['begin_time']),
                'end_time' => strtotime($_POST['end_time']),
                'status' => 1,
            );
            //添加操作日志
            $log = '新增分析仪'.$_POST['deviceid'];
            $this->addOperLog($log);
            if(M('devicelist')->add($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            $company = M('company')->select();
            $this->assign('company',$company);
            $data = array(
                'name' => array('in','检测员'),
            );
            $sysrole = M('sysrole')->where($data)->find();
            $admin = M('admin')->where(array('role_id'=>$sysrole['id'],'company_id'=>$_SESSION['adminUser']['company_id']))->select();
            $this->assign('admin',$admin);
            $this->display();
        }
    }

    public function edit() {
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $mechine = D("Machine")->getMachineById($id);
        $this->assign('vo',$mechine);
        $company = M('company')->select();
        $this->assign('company',$company);
        $data = array(
            'name' => array('in','检测员'),
        );
        $sysrole = M('sysrole')->where($data)->find();
        $admin = M('admin')->where(array('role_id'=>$sysrole['id'],'company_id'=>$_SESSION['adminUser']['company_id']))->select();
        $this->assign('admin',$admin);
        $this->display();
    }

    public function save() {
        $dev_id= $_POST['dev_id'];
        if(!$dev_id){
            $this->error('非法操作');
        }
        if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
            return show(0,'内置分析仪ID不能为空');
        }
        if(!isset($_POST['devicename']) || !$_POST['devicename']) {
            return show(0,'分析仪名称不能为空');
        }
        $company_id   = !empty($_POST['company_id'])   ? intval($_POST['company_id'])   : 0;
        $admin_id = !empty($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
        if(!isset($_POST['begin_time']) || !$_POST['begin_time']) {
            return show(0,'开始时间不能为空');
        }
        if(!isset($_POST['end_time']) || !$_POST['end_time']) {
            return show(0,'结束时间不能为空');
        }
        $data = array(
            'deviceid' => $_POST['deviceid'],
            'devicename'   => $_POST['devicename'],
            'admin_id' => $admin_id,
            'company_id' => $company_id,
            'start_time' => strtotime($_POST['begin_time']),
            'end_time' => strtotime($_POST['end_time'])
        );
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


    public function set(){
        if($_POST) {
            $id= $_POST['id'];
            if(!$id){
                $this->error('非法操作');
            }
            $mechinelist = D("Machinelist")->getMachinelistById($id);
            if(empty($mechinelist)){
                return show(1, '无效货道');
            }
            $device_id = $mechinelist['deviceid'];
            $mechine = D("Machine")->getMachineBydeviceId($device_id);
            if(!$mechine['status']){
                $log = '分析仪'.$mechine['devicename'].'离线,无法操作';
                return show(1, $log);
            }
            //添加操作日志
            $log = '设置测试项目'.$mechinelist['item'].'预警值为'.$_POST['alarm'];
            $this->addOperLog($log);
            //更新商品容量和库存预警值
            $param['alarm'] = $_POST['alarm'];
            $res = D("Machinelist")->updateByDevlistId($id, $param);
            if($res === false) {
                return show(0, '更新失败');
            }else{
                return show(1, '更新成功');
            }
        }else{
            !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
            $mechinelist = D("Machinelist")->getMachinelistById($id);
            $this->assign('vo',$mechinelist);
            $this->display();
        }

    }

    public function machinelist(){
        !empty($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : $this->error('非法操作');
        $address = $_GET['address'];
        //分析仪设备详情
        $mechine = D("Machine")->getMachineBydeviceId($deviceid);
        $this->assign('mechine',$mechine);
        //测试数据列表
        $conds = array();
        $conds['deviceid'] = $deviceid;
        $begin_time = $_GET['begin_time'];
        if($begin_time) {
            $conds['begin_time'] = strtotime($begin_time);
        }
        $end_time = $_GET['end_time'];
        if($end_time) {
            $conds['end_time'] = strtotime($end_time);
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        $machinelist = D('Machinelist')->getMachinelist($conds,$page,$pageSize);
        foreach($machinelist as $key=>$item){
            $condition['pond_num'] = $item['pond_num'];
            $condition['company_id'] = $mechine['company_id'];
            if($address){
                $condition['address'] = array('like','%'.$address.'%');
            }
            $condition['_logic'] = 'AND';
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $machinelist[$key]['address'] = $res['address'];
            }else{
                unset($machinelist[$key]);
            }
        }
        $count = D("Machinelist")->getMachinelistCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();
        $this->assign('pageres',$pageres);
        $this->assign('machinelist', $machinelist);
        $this->display();
    }


    public function setStatus() {
        $data = array(
            'id'=>intval($_POST['id']),
            'status' => intval($_POST['status']),
        );
        return parent::setStatus($_POST,'Machine');
    }


    public function additem(){
        if($_POST) {
            if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
                return show(0,'内置分析仪ID不能为空');
            }
//            if(!isset($_POST['itemid']) || !$_POST['itemid']) {
//                return show(0,'标识ID不能为空');
//            }
            if(!isset($_POST['item']) || !$_POST['item']) {
                return show(0,'测试项目不能为空');
            }
            if(!isset($_POST['unit']) || !$_POST['unit']) {
                return show(0,'计量单位不能为空');
            }
            $alarm   = !empty($_POST['alarm'])   ? doubleval($_POST['alarm'])   : 0;
            $pond_num = !empty($_POST['pond_num']) ? intval($_POST['pond_num']) : 0;
            $data = array(
                'deviceid' => $_POST['deviceid'],
//                'itemid'   => $_POST['itemid'],
                'item' => $_POST['item'],
                'unit' => $_POST['unit'],
                'alarm' => $alarm,
                'pond_num' => $pond_num,
            );
            //添加操作日志
            $log = '新增测试项目'.$_POST['item'];
            $this->addOperLog($log);
            if(D('Machinelist')->insert($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            isset($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : $this->error('非法操作');
            $this->assign('deviceid',$deviceid);
            $this->display();
        }
    }


    public function itemlist(){
        !empty($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : $this->error('非法操作');
        $address = $_GET['address'];
        //分析仪设备详情
        $mechine = D("Machine")->getMachineBydeviceId($deviceid);
        $this->assign('mechine',$mechine);
        //测试数据列表
        $conds = array();
        $conds['deviceid'] = $deviceid;
        $begin_time = $_GET['begin_time'];
        if($begin_time) {
            $conds['begin_time'] = strtotime($begin_time);
        }
        $end_time = $_GET['end_time'];
        if($end_time) {
            $conds['end_time'] = strtotime($end_time);
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        if($address){
            $condition['company_id'] = $mechine['company_id'];
            $condition['address'] = array('like','%'.$address.'%');
            $condition['_logic'] = 'AND';
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $conds['pond_num']= $res['pond_num'];
            }else{
                $conds['pond_num'] = 0;
            }
        }
        //相对条件下的测试项和测试单位
        $Model = new \Think\Model();// 实例化一个model对象 没有对应任何数据表
        $titles = $Model->query("SELECT itemid,item,unit FROM `xc_deviceinfo_history` group by itemid;");
        //list
        $itemlist = D('Itemlist')->getItemlist($conds,$page,$pageSize);
        foreach($itemlist as $key=>$item){
            $condition['pond_num'] = $item['pond_num'];
            $condition['company_id'] = $mechine['company_id'];
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $itemlist[$key]['address'] = $res['address'];
            }
        }
        foreach ($titles as $k=>$v) {
            foreach($itemlist as $key=>$item){
                if($item['itemid'] == $v['itemid']){
                    $titles[$k]['list'][$key] = $item;
                    foreach ($titles[$k]['list'][$key] as $keys=>$title) {
                        $titles[$k]['list'][$key]['keys'] = $key+1;
                    }
                }
            }
        }
        $count = D("Itemlist")->getItemlistCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();
//        dump($titles);die;
        $this->assign('itemlist',$titles);
        $this->assign('pageres',$pageres);
        $this->assign('machinelist', $itemlist);
        $this->display();
    }

}