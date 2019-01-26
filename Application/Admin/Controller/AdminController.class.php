<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class AdminController extends CommonController {

    public function index() {
        $conds = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;
        $username = $_GET['username'];
        if($username) {
            $conds['username'] = $username;
            $this->assign('name', $username);
        }
        if($_SESSION['adminUser']['company_id'] != 0){
            //观察员和单位管理员查看本单位所有数据
            $conds['company_id'] = $_SESSION['adminUser']['company_id'];
        }
        $admins = D('Admin')->getAdmins($conds,$page,$pageSize);
        $count = D("Admin")->getAdminsCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();
        $this->assign('pageres',$pageres);
        $this->assign('admins', $admins);
        $this->display();
    }

    public function add() {
        // 保存数据
        if(IS_POST) {
            if(!isset($_POST['username']) || !$_POST['username']) {
                return show(0, '用户名不能为空');
            }
            if(!isset($_POST['password']) || !$_POST['password']) {
                return show(0, '密码不能为空');
            }
            $_POST['password'] = getMd5Password($_POST['password']);
            // 判定用户名是否存在
            $admin = D("Admin")->getAdminByUsername($_POST['username']);
            if($admin && $admin['status']!=-1) {
                return show(0,'该用户存在');
            }
            // 判定手机号是否存在
            $phone = D("Admin")->getAdminByPhone($_POST['phone']);
            if($phone) {
                return show(0,'该用户存在');
            }
            //一个单位只能有一个单位管理员
            if($_POST['company_id']){
                //查出改单位下的单位管理员
                $data = array(
                    'name' => array('in','用户单位管理员'),
                );
                $sysuser = M('sysrole')->where($data)->find();
                $ret = M('admin')->where(array('company_id'=>$_POST['company_id'],'role_id'=>$sysuser['id']))->find();
                if($ret && ($_POST['role_id'] == $sysuser['id'])){
                    return show(0, '该单位已有单位管理员');
                }
            }else{
                return show(0, '请选择单位');
            }
            // 新增
            $id = D("Admin")->insert($_POST);
            if(!$id) {
                return show(0, '新增失败!');
            }
            $this->addOperLog('添加用户'.$admin['username']);
            return show(1, '新增成功');
        }
        if($_SESSION['adminUser']['company_id'] == 0){
            $sys = M('sysrole')->select();
        }else{
            $data = array(
                'name' => array('in','检测员,观察员'),
            );
            $sys = M('sysrole')->where($data)->select();
        }
        $company = M('company')->select();
        $this->assign('sys',$sys);
        $this->assign('company',$company);
        $this->display();
    }

    public function setStatus() {
        $data = array(
            'admin_id'=>intval($_POST['id']),
            'status' => intval($_POST['status']),
        );
        return parent::setStatus($_POST,'Admin');
    }

    public function personal() {
        $sys = M('sysrole')->select();
        $res = $this->getLoginUser();
        $user = D("Admin")->getAdminByAdminId($res['admin_id']);
        $this->assign('vo',$user);
        $this->assign('sys',$sys);
        $this->display();
    }

    public function save() {
        $user = $this->getLoginUser();
        if(!$user) {
            return show(0,'用户不存在');
        }
        $data['realname'] = $_POST['realname'];
            $data['email'] = $_POST['email'];
            $data['phone'] = $_POST['phone'];
            $data['role_id'] = $_POST['role_id'];
            try {
                $id = D("Admin")->updateByAdminId($user['admin_id'], $data);
                if($id === false) {
                    return show(0, '配置失败');
                }
                $this->addOperLog('配置'.$user['username']);
                return show(1, '配置成功');
            }catch(Exception $e) {
                return show(0, $e->getMessage());
        }
    }

    public function edit() {
        if ($_POST) {
            isset($_POST['admin_id']) ? $admin_id = $_POST['admin_id'] : $this->error('非法操作');
            $user = D("Admin")->getAdminByAdminId($admin_id);
            if(!$user) {
                return show(0,'用户不存在');
            }
            $data['realname'] = $_POST['realname'];
            $data['email'] = $_POST['email'];
            $data['phone'] = $_POST['phone'];
            $data['role_id'] = $_POST['role_id'];
            $data['company_id'] = $_POST['company_id'];
            //一个单位只能有一个单位管理员
            if($_POST['company_id']){
                //查出改单位下的单位管理员
                $where = array(
                    'name' => array('in','用户单位管理员'),
                );
                $sysuser = M('sysrole')->where($where)->find();
                $ret = M('admin')->where(array('company_id'=>$_POST['company_id'],'role_id'=>$sysuser['id']))->find();
                if($ret && ($_POST['role_id'] == $sysuser['id'])){
                    return show(0, '该单位已有单位管理员');
                }
            }else{
                return show(0, '请选择单位');
            }
            try {
                $id = D("Admin")->updateByAdminId($user['admin_id'], $data);
                if($id === false) {
                    return show(0, '修改失败');
                }
                $this->addOperLog('修改'.$user['username']);
                return show(1, '修改成功');
            }catch(Exception $e) {
                return show(0, $e->getMessage());
            }
        }else{
            !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
            $user = D("Admin")->getAdminByAdminId($id);
            $this->assign('vo',$user);
            if($_SESSION['adminUser']['company_id'] == 0){
                $sys = M('sysrole')->select();
            }else{
                $data = array(
                    'name' => array('in','检测员,观察员'),
                );
                $sys = M('sysrole')->where($data)->select();
            }
            $company = M('company')->select();
            $this->assign('sys',$sys);
            $this->assign('company',$company);
            $this->display();
        }
    }

    //删除
    public function del(){
        try {
            if ($_POST) {
                isset($_POST['id']) ? $admin_id = $_POST['id'] : $this->error('非法操作');
                //添加操作日志
                $log = '删除用户ID'.$admin_id;
                $this->addOperLog($log);
                // 执行数据更新操作
                if(M('admin')->delete($admin_id)){
                    return show(1, '删除成功');
                }else{
                    return show(1, '删除失败');
                }
            }
        }catch(Exception $e) {
            return show(0,$e->getMessage());
        }
        return show(0,'没有提交的数据');
    }


}