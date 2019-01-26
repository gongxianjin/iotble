<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class UserController extends CommonController {

    //查看用户详情
    public function index(){
        $user = D("Admin")->getAdminByAdminId($_SESSION['User']['admin_id']);
        unset($user['password']);
        //查出角色名称和公司名称
        $user['company_name'] = M('company')->where(array('company_id'=>$user['company_id']))->getField('company_name');
        $user['role_name'] = M('sysrole')->where(array('id'=>$user['role_id']))->getField('name');
        $user['lastlogintime'] =  date('Y-m-d H:i:s',$user['lastlogintime']);
        unset($user['company_id']);
        unset($user['role_id']);
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功','data'=>$user));
    }

    public function userList(){
        $admins = D('Admin')->getAdmins($_SESSION['User']['company_id']);
        foreach($admins as $key=>$item){
            $admins[$key]['company_name'] = M('company')->where(array('company_id'=>$item['company_id']))->getField('company_name');
            $admins[$key]['role_name'] = M('sysrole')->where(array('id'=>$item['role_id']))->getField('name');
            $admins[$key]['lastlogintime'] = date('Y-m-d H:i:s',$item['lastlogintime']);
            unset($admins[$key]['company_id']);
            unset($admins[$key]['role_id']);
            unset($admins[$key]['password']);
        }
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功','data'=>$admins));
    }

    //单位管理员添加本单位下的测试员和管理员
    public function userAdd(){
        # code...
        $data['username'] = I('username');
        $data['password'] = getMd5Password(I('password'));
        $data['phone'] = I('phone');
	
        $data['company_id'] = $_SESSION['User']['company_id'];
        $data['role_id'] = I('role_id');
        $data['status'] = 1;
        if(!$data['phone']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'手机号不能为空！' ));
        }
        if(!$data['company_id']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用单位管理员角色登录！' ));
        }
	if(!$data['role_id']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'角色不能为空！' ));
        }
        // 判定用户名是否存在
        $admin = D("Admin")->getAdminByUsername($data['username']); 
        if($admin && $admin['status']!=-1) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'用户存在！' ));
        }
        // 判定手机号是否存在
        $phone = D("Admin")->getAdminByPhone($data['phone']);
        if($phone) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'手机号存在！'));
        }
        $re=D('Admin')->insert($data);
        if($re){
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'新增成功','data'=>'0'));
        }else{
            $this->ajaxReturn(array('code' =>1 ,'msg'=>'新增失败！' ));
        }
    }

    //单位管理员停用测试员和观察员
    public function setStatus() {
        $data['admin_id'] = I('admin_id');
        $data['status'] = I('status');
        if (!$data['admin_id']) {
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'ID不存在！' ));
        }
        $res = D('Admin')->updateStatusById($data['admin_id'], $data['status']);
        if ($res) {
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'操作成功','data'=>'0'));
        } else {
            $this->ajaxReturn(array('code' =>1 ,'msg'=>'操作失败！' ));
        }
    }


}