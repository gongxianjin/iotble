<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class MemberController extends CommonController {

    public function index() {
        $conds = array();
        $username = $_GET['username'];
        if($username) {
            $conds['username'] = $username;
        }
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;

        $members = D('Member')->getMember($conds,$page,$pageSize);
        $count = D("Member")->getMemberCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();

        $this->assign('pageres',$pageres);
        $this->assign('members', $members);
        $this->display();
    }

    public function add() {
        // 保存数据
        if(IS_POST) {
            if(!isset($_POST['phone']) || !$_POST['phone']) {
                return show(0, '用户名不能为空');
            }
            if(!isset($_POST['password']) || !$_POST['password']) {
                return show(0, '密码不能为空');
            }
            if(!isset($_POST['role_id']) || !$_POST['role_id']) {
                return show(0, '请选择一个角色');
            }
            $_POST['username'] = $_POST['phone'];
            $_POST['password'] = getMd5Password($_POST['password']);
            // 判定用户名是否存在
            $member = D("Member")->getMemberByUsername($_POST['username']);
            if($member && $member['status']!=-1) {
                return show(0,'该用户存在');
            }
            //添加操作日志
            $log = '新增用户'.$_POST['username'];
            $this->addOperLog($log);
            // 新增用户
            $id = D("Member")->insert($_POST);
            // 新增用户绑定
            $roles = array(
                        'user_id'=>$id,
                        'role_id'=>$_POST['role_id'],
                        );
            M('user_role')->add($roles);
            if(!$id) {
                return show(0, '新增失败');
            }
            return show(1, '新增成功');
        }
        $roles = M('role')->select();
        $this->assign('roles',$roles);
        $this->display();
    }

    public function setStatus() {
        $data = array(
            'user_id'=>intval($_POST['id']),
            'status' =>intval($_POST['status']),
        );
        return parent::setStatus($_POST,'Member');
    }

    public function edit() {
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $user = D("Member")->getMemberByUserId($id);
        $this->assign('vo',$user);
        $roles = M('role')->select();
        $this->assign('roles',$roles);
        $this->display();
    }

    public function save() {
        $user_id = $_POST['user_id'];
        if(!$user_id){
            $this->error('非法操作');
        }
        $data['phone'] = $_POST['phone'];
        $data['role_id'] = $_POST['role_id'];
        try {
            //添加操作日志
            $log = '更新用户'.$data['phone'];
            $this->addOperLog($log);
            $id = D("Member")->updateByUserId($user_id, $data);
            if($id === false) {
                return show(0, '更新失败');
            }
            return show(1, '更新成功');
        }catch(Exception $e) {
            return show(0, $e->getMessage());
        }
    }

}