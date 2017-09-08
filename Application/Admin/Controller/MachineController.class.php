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
            $admin = M('admin')->where(array('role_id'=>$sysrole['id']))->select();
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
        $admin = M('admin')->where(array('role_id'=>$sysrole['id']))->select();
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
            $mechine = D("Machine")->getMachineById($device_id);
            if(!$mechine['devstatus']){
                $log = '设备'.$mechine['devicename'].'离线,无法操作';
                return show(1, $log);
            }
            $data['id'] = $device_id;
            $data['sn'] = "0";
            $data['lane'] = intval($_POST['lane']);
            $data['sto'] = intval($_POST['sto']);
            $data['num'] = 0;
            $data['lock'] = 0;
            $reqjson = json_encode($data);
            //添加操作日志
            $log = '设置货道'.$mechinelist['lane'].'库存为'.$_POST['sto'];
            $this->addOperLog($log);
            //更新商品容量和库存预警值
            $param['max_sto'] = $_POST['max_sto'];
            $param['alarm_sto'] = $_POST['alarm_sto'];
            $param['goods_id'] = $_POST['goods_id'];
            if($data['sto'] > $param['max_sto'] || $data['sto'] < $param['alarm_sto']){
                return show(1, '设置错误');
            }
            if(!$param['goods_id']){
                return show(1, '请选择商品');
            }
            $res = D("Machinelist")->updateByDevlistId($id, $param);
            if($res === false) {
                return show(0, '更新失败');
            }
            try {
                $mqtt = new \Org\Util\phpMQTT("120.77.245.43", 1883, "iotserver_victor_test"); //Change client name to something unique
                $topic = "/zq100n/dev/sub/$device_id";
                if ($mqtt->connect(true,null,'server','xcent123!@#')) {
                    $mqtt->publish($topic,$reqjson,0);
                    $mqtt->close();
                }else{
                    return show(0, '服务器连接失败');
                }
                return show(1, '发布成功');
            }catch(Exception $e) {
                return show(0, $e->getMessage());
            }
        }else{
            !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
            $mechinelist = D("Machinelist")->getMachinelistById($id);
            //商品
            $goods = M('goods')->select();
            $this->assign('goods',$goods);
            $this->assign('vo',$mechinelist);
            $this->display();
        }

    }

    public function machinelist(){
        !empty($_GET['deviceid']) ? $id = $_GET['deviceid'] : $this->error('非法操作');
        //设备详情
        $mechine = D("Machine")->getMachineById($id);
//        dump($mechine);die;
        $this->assign('vo',$mechine);
        //货道列表
        $conds = array();
        $deviceid = $_GET['deviceid'];
        if($deviceid) {
            $conds['deviceid'] = $deviceid;
        }
        $devicename = $_GET['devicename'];
        if($devicename) {
            $conds['devicename'] = $devicename;
        }

        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;

        $machinelist = D('Machinelist')->getMachinelist($conds,$page,$pageSize);
//        dump($machinelist);die;
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

}