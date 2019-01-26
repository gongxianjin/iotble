<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class MachineController extends CommonController {

    public function index() {
        $conds = array();
        $deviceid = $_GET['deviceinfo'];
        if(!empty($deviceid)) {
            $conds['deviceid'] = $deviceid;
        }
        $devicename = $_GET['deviceinfo'];
        if(!empty($devicename)) {
            $conds['devicename'] = $devicename;
        }
        $username = $_GET['username'];
        if(!empty($username)) {
            $con['username'] = array('like','%'.$username.'%');
            $res = M('admin')->where($con)->find();
            if(!empty($res)){
                $conds['admin_id'] = $res['admin_id'];
            }else{
                $conds['admin_id'] = 0;
            }
        }
        if($_SESSION['User']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $conds['company_id'] = $_SESSION['User']['company_id'];
            //检测员查看自己上传的数据
            $data = array(
                'name' => array('in','检测员'),
            );
            $sys = M('sysrole')->where($data)->find();
            if($_SESSION['User']['role_id'] == $sys['id']){
                $conds['admin_id'] = $_SESSION['User']['admin_id'];
            }
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        $machine = D('Machine')->getMachine($conds,$page,$pageSize);
        foreach($machine as $key=>$item){
            //查出角色名称和公司名称
           $machine[$key]['company_name'] = M('company')->where(array('company_id'=>$item['company_id']))->getField('company_name');
           $machine[$key]['test_name'] = M('admin')->where(array('admin_id'=>$item['admin_id']))->getField('username');
           $machine[$key]['start_time'] = date('Y-m-d',$item['start_time']);
           $machine[$key]['end_time'] = date('Y-m-d',$item['end_time']);
            unset($machine[$key]['company_id']);
            //unset($machine[$key]['admin_id']);
        }
        $count = D("Machine")->getMachineCount($conds);
        $res = array(
            'total'=> $count,
            'per_page'=>$pageSize,
            'current_page'=>$page,
            'last_page'=>ceil($count/$pageSize),
            'data'=>$machine,
        );
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$res));
    }

    public function Add(){
        if($_POST) {
            if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'内置分析仪ID不能为空！' ));
            }
            if(!isset($_POST['devicename']) || !$_POST['devicename']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'分析仪名称不能为空！' ));
            }
            $company_id   = $_SESSION['User']['company_id'];
            if(!$company_id){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用单位管理员角色登录！' ));
            }
            //测试员ID
            $admin_id = !empty($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
            if(!isset($_POST['begin_time']) || !$_POST['begin_time']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'开始时间不能为空！' ));
            }
            if(!isset($_POST['end_time']) || !$_POST['end_time']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'结束时间不能为空！' ));
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
            if(M('devicelist')->add($data)){
                $this->ajaxReturn(array('code' =>200 ,'msg'=>'新增成功','data'=>0));
            }else{
                $this->ajaxReturn(array('code' =>1 ,'msg'=>'新增失败！' ));
            }
        }
    }

    public function edit() {
        !empty($_GET['id']) ? $id = $_GET['id'] : 0;
        if(!$id){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'ID不能为空！' ));
        }
        $machine = D("Machine")->getMachineById($id);
        $machine['start_time'] = date('Y-m-d',$machine['start_time']);
        $machine['end_time'] = date('Y-m-d',$machine['end_time']);
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$machine));
    }

    public function Save() {
        $dev_id = $_POST['dev_id'];
        if(!$dev_id){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'非法操作！' ));
        }
        if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'内置分析仪ID不能为空！' ));
        }
        if(!isset($_POST['devicename']) || !$_POST['devicename']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'分析仪名称不能为空！' ));
        }
        $company_id   = $_SESSION['User']['company_id'];
        if(!$company_id){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用单位管理员角色登录！' ));
        }
        $admin_id = !empty($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
        if(!isset($_POST['begin_time']) || !$_POST['begin_time']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'开始时间不能为空！' ));
        }
        if(!isset($_POST['end_time']) || !$_POST['end_time']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'结束时间不能为空！' ));
        }
        $data = array(
            'deviceid' => $_POST['deviceid'],
            'devicename'   => $_POST['devicename'],
            'admin_id' => $admin_id,
            'company_id' => $company_id,
            'start_time' => strtotime($_POST['begin_time']),
            'end_time' => strtotime($_POST['end_time'])
        );
        try {
            $id = D("Machine")->updateByDevId($dev_id, $data);
            if($id === false) {
                $this->ajaxReturn(array('code' =>0 ,'msg'=>'更新失败！' ));
            }
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'更新成功！','data'=>0));
        }catch(Exception $e) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>$e->getMessage()));
        }
    }

    public function setStatus() {
        $data = array(
            'id'=>intval($_GET['id']),
            'status' => intval($_GET['status']),
        ); 
        if (!$data['id']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'ID不存在！' ));
        }
        $res = D('Machine')->updateStatusById($data['id'], $data['status']);
        if ($res) {
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'操作成功','data'=>0));
        } else {
            $this->ajaxReturn(array('code' =>1 ,'msg'=>'操作失败！' ));
        }
    }


    public function machinelist(){
        !empty($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : null;
        $address = $_GET['deviceid'];
        if($address){
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $condition['address'] = array('like','%'.$address.'%');
            $ponds = M('dev_address')->where($condition)->find();
        }
        $where = array();
        //分析仪设备详情
        if($_SESSION['User']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $where['company_id'] = $_SESSION['User']['company_id'];
            //检测员查看自己上传的数据
            $data = array(
                'name' => array('in','检测员'),
            );
            $sys = M('sysrole')->where($data)->find();
            if($_SESSION['User']['role_id'] == $sys['id']){
                $where['admin_id'] = $_SESSION['User']['admin_id'];
            }
            if(!$ponds['address']){
                $where['deviceid'] = array('like','%'.$deviceid.'%');
            }
        }
        $machines = M('devicelist')->where($where)->select();
        foreach($machines as $key=>$item){
            if(count($machines) == 1){
                $mechine['deviceid'] = $item['deviceid'];
            }elseif((count($machines)-1) == $key){
                $mechine['deviceid'] .= $item['deviceid'];
            }else{
                $mechine['deviceid'] .= $item['deviceid'].',';
            }
        }
        //测试数据列表
        $conds = array();
        if($mechine['deviceid']){
            $map['deviceid'] = array('in',$mechine['deviceid']);
            //dump($conds['deviceid']);die;
        }
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
        if($ponds['address']){
            $map['pond_num']= $ponds['pond_num'];
            $map['_logic'] = 'AND';
        }else{
            $map['pond_num'] = 0;
            $map['_logic'] = 'OR';
        }
        $conds['_complex'] = $map;
        $machinelist = D('Machinelist')->getMachinelist($conds,$page,$pageSize);
        foreach($machinelist as $key=>$item){
            $condition['pond_num'] = $item['pond_num'];
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $machinelist[$key]['address'] = $res['address'];
            }
            $machinelist[$key]['time'] = date('Y-m-d',$item['time']);
        }
        $count = D("Machinelist")->getMachinelistCount($conds);
        $res = array(
            'total'=> $count,
            'per_page'=>$pageSize,
            'current_page'=>$page,
            'last_page'=>ceil($count/$pageSize),
            'data'=>$machinelist,
        );
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$res));
    }


    public function additem(){
        if($_POST) {
            if(!isset($_POST['deviceid']) || !$_POST['deviceid']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'内置分析仪ID不能为空！' ));
            }
            if(!isset($_POST['itemid']) || !$_POST['itemid']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'标识ID不能为空！' ));
            }
            if(!isset($_POST['item']) || !$_POST['item']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'测试项目不能为空！' ));
            }
            if(!isset($_POST['unit']) || !$_POST['unit']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'计量单位不能为空！' ));
            }
            $alarm   = !empty($_POST['alarm'])   ? doubleval($_POST['alarm'])   : 0;
            $pond_num = !empty($_POST['pond_num']) ? intval($_POST['pond_num']) : 0;
            $data = array(
                'deviceid' => $_POST['deviceid'],
                'itemid'   => $_POST['itemid'],
                'item' => $_POST['item'],
                'unit' => $_POST['unit'],
                'alarm' => $alarm,
                'pond_num' => $pond_num,
            );
            if(D('Machinelist')->insert($data)){
                $this->ajaxReturn(array('code' =>200 ,'msg'=>'新增成功','data'=>0));
            }else{
                $this->ajaxReturn(array('code' =>1 ,'msg'=>'新增失败！' ));
            }
        }
    }


    public function set(){
        if($_POST) {
            $id= $_POST['id'];
            if(!$id){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'非法操作'));
            }
            $mechinelist = D("Machinelist")->getMachinelistById($id);
            if(empty($mechinelist)){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'无效测试数据'));
            }
            $device_id = $mechinelist['deviceid'];
            $mechine = D("Machine")->getMachineBydeviceId($device_id);
            if(!$mechine['status']){
                $log = '分析仪'.$mechine['devicename'].'离线,无法操作';
                $this->ajaxReturn(array('code' =>2 ,'msg'=>$log));
            }
            //更新商品容量和库存预警值
            $param['alarm'] = $_POST['alarm'];
            $res = D("Machinelist")->updateByDevlistId($id, $param);
            if($res === false){
                $this->ajaxReturn(array('code' =>1 ,'msg'=>'更新失败'));
            }else{
                $this->ajaxReturn(array('code' =>200 ,'msg'=>'更新成功','data'=>0));
            }
        }
    }

    public function upload(){
        try {
            $msg = file_get_contents("php://input");
            $res = json_decode($msg);

            $deviceid = $res->deviceid;
            $devicedata = $res->devicedata;
            //检查该分析仪器的状态
            $machine = D("Machine")->getMachineBydeviceId($deviceid);
            if(!$machine){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'查无该分析仪！'));
            }
            if(!$machine['status']){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'该分析仪已设置离线！'));
            }
            $time = time();
            if($time < $machine['start_time'] || $time > $machine['end_time']){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'该分析仪不在使用期！'));
            }
            //检查该设备是否该单位的
            if($machine['company_id'] != $_SESSION['User']['company_id']){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'该分析仪不属于本单位！'));
            }
            if($machine['admin_id'] != $_SESSION['admin_id']){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用测试员账号进行上传数据！'));
            }
            $machinelist =  M('deviceinfo')->where('deviceId="'.$deviceid.'"')->select();
            //检查该检测项目是否在后台设置的检测项目中
            foreach($machinelist as $key=>$item){
                if($devicedata){
                    foreach($devicedata as $k=>$v){
                        if($v->item == $item['item']){
                            //分析仪设置时间需要再使用器
                            //dump(strtotime($v->time));die;
                            if(strtotime($v->time) < $machine['start_time'] || strtotime($v->time) > $machine['end_time']){
                                $this->ajaxReturn(array('code' =>2 ,'msg'=>'该分析仪系统设置时间不在使用期！'));
                            } 
                            //判断该测试项目是否报警
                            if($v->result > $item['alarm']){
                                $data = array(
                                    'deviceid'=>$deviceid,
                                    'itemid'=>$v->id,
                                    'item'=>$v->item,
                                    'unit'=>$v->unit,
                                    'result'=>$v->result,
                                    'pond_num'=>$v->pond_num,
                                    'log_time'=>strtotime($v->time)
                                );
                                M('devhistory_alerm')->add($data);
                            }else{
                                //更新测试数据
                                $newdata = array(
                                    'itemid'=>$v->id,
                                    'result'=>$v->result,
                                    'time'=>strtotime($v->time)
                                );
                                $conditon['deviceid'] = $deviceid;
                                $conditon['item'] = $v->item;
                                $conditon['_logic'] = 'AND';
                                M('deviceinfo')->where($conditon)->save($newdata);
                            }
                        }
                    }
                }
            }
            if($devicedata){
                foreach($devicedata as $k=>$v){
                    $data = array(
                        'id'=>create_uuid(),
                        'deviceid'=>$deviceid,
                        'itemid'=>$v->id,
                        'item'=>$v->item,
                        'unit'=>$v->unit,
                        'result'=>$v->result,
                        'pond_num'=>$v->pond_num,
                        'history_time'=>strtotime($v->time)
                    );
                    M('deviceinfo_history')->add($data);
                }
            }
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'上传成功！','data'=>0));
        }catch(Exception $e) {
            $this->ajaxReturn(array('code' =>1 ,'msg'=>$e->getMessage()));
        }
    }

    public function itemlist(){
        !empty($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : null;
        $address = $_GET['deviceid'];  
	    if($address){
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $condition['address'] = array('like','%'.$address.'%'); 
            $ponds = M('dev_address')->where($condition)->find(); 
        } 
        $where = array();
        //分析仪设备详情
        if($_SESSION['User']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $where['company_id'] = $_SESSION['User']['company_id'];
            //检测员查看自己上传的数据
            $data = array(
                'name' => array('in','检测员'),
            );
            $sys = M('sysrole')->where($data)->find();
            if($_SESSION['User']['role_id'] == $sys['id']){
                $where['admin_id'] = $_SESSION['User']['admin_id'];
            }  
	    if(!$ponds['address']){
	    	$where['deviceid'] = array('like','%'.$deviceid.'%');
	    } 
        }  
        $machines = M('devicelist')->where($where)->select(); 
        foreach($machines as $key=>$item){
            if(count($machines) == 1){
                $mechine['deviceid'] = $item['deviceid'];
            }elseif((count($machines)-1) == $key){
                $mechine['deviceid'] .= $item['deviceid'];
            }else{
                $mechine['deviceid'] .= $item['deviceid'].',';
            }
        }
        //测试数据列表
        $conds = array();
	    if($mechine['deviceid']){
            $map['deviceid'] = array('in',$mechine['deviceid']);
            //dump($conds['deviceid']);die;
        }
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
      	if($ponds['address']){
                $map['pond_num']= $ponds['pond_num'];
                $map['_logic'] = 'AND';
        }else{
                $map['pond_num'] = 0;
                $map['_logic'] = 'OR';
        }
        $conds['_complex'] = $map;
        $itemlist = D('Itemlist')->getItemlist($conds,$page,$pageSize);
        foreach($itemlist as $key=>$item){
            $condition['pond_num'] = $item['pond_num'];
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $itemlist[$key]['address'] = $res['address'];
            }
            $itemlist[$key]['history_time'] = date('Y-m-d',$item['history_time']);
        }
        $count = D("Itemlist")->getItemlistCount($conds);
        $res = array(
            'total'=> $count,
            'per_page'=>$pageSize,
            'current_page'=>$page,
            'last_page'=>ceil($count/$pageSize),
            'data'=>$itemlist,
        );
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$res));
    }


    public function alarmlist() {
        !empty($_GET['deviceid']) ? $deviceid = $_GET['deviceid'] : null;
        $address = $_GET['deviceid'];
        if($address){
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $condition['address'] = array('like','%'.$address.'%');
            $ponds = M('dev_address')->where($condition)->find();
        }
        $where = array();
        //分析仪设备详情
        if($_SESSION['User']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $where['company_id'] = $_SESSION['User']['company_id'];
            //检测员查看自己上传的数据
            $data = array(
                'name' => array('in','检测员'),
            );
            $sys = M('sysrole')->where($data)->find();
            if($_SESSION['User']['role_id'] == $sys['id']){
                $where['admin_id'] = $_SESSION['User']['admin_id'];
            }
            if(!$ponds['address']){
                $where['deviceid'] = array('like','%'.$deviceid.'%');
            }
        }
        $machines = M('devicelist')->where($where)->select();
        foreach($machines as $key=>$item){
            if(count($machines) == 1){
                $mechine['deviceid'] = $item['deviceid'];
            }elseif((count($machines)-1) == $key){
                $mechine['deviceid'] .= $item['deviceid'];
            }else{
                $mechine['deviceid'] .= $item['deviceid'].',';
            }
        }
        //测试数据列表
        $conds = array();
        if($mechine['deviceid']){
            $map['deviceid'] = array('in',$mechine['deviceid']);
            //dump($conds['deviceid']);die;
        }
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
        if($ponds['address']){
            $map['pond_num']= $ponds['pond_num'];
            $map['_logic'] = 'AND';
        }else{
            $map['pond_num'] = 0;
            $map['_logic'] = 'OR';
        }
        $conds['_complex'] = $map;
        $alarmlist = D('Alarmlist')->getAlarmlist($conds,$page,$pageSize);
        foreach($alarmlist as $key=>$item){
            $condition['pond_num'] = $item['pond_num'];
            $condition['company_id'] = $_SESSION['User']['company_id'];
            $res = M('dev_address')->where($condition)->find();
            if($res['address']){
                $alarmlist[$key]['address'] = $res['address'];
            }
            $alarmlist[$key]['log_time'] = date('Y-m-d',$item['log_time']);
        }
        $count = D("Alarmlist")->getAlarmlistCount($conds);
        $res = array(
            'total'=> $count,
            'per_page'=>$pageSize,
            'current_page'=>$page,
            'last_page'=>ceil($count/$pageSize),
            'data'=>$alarmlist,
        );
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$res));
    }

}