<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class AdminController extends CommonController {

    public function index() {
        $admins = D('Admin')->getAdmins($_SESSION['adminUser']['company_id']);
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

}