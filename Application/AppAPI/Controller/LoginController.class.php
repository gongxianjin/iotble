<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class LoginController extends Controller {

    public function index(){
        $this->show('这是接口的入口地址，说明环境安装已经正常。','utf-8');
    }

    public function login(){
        $data['phone'] = I('phone');
        $data['password'] = I('password');
        if(!trim($data['phone'])){
            $this->ajaxReturn(array('code'=>2,'msg'=>'用户名不能为空'));
        }
        if(!trim($data['password'])){
            $this->ajaxReturn(array('code'=>2,'msg'=>'密码不能为空'));
        }
        $ret = D('Admin')->getAdminByPhone($data['phone']);
        if(!$ret || $ret['status'] !=1) {
            $this->ajaxReturn(array('code'=>2,'msg'=>'该用户不存在'));
        }
        if($ret['status'] == 0) {
            $this->ajaxReturn(array('code'=>2,'msg'=>'该用户正在审核中'));
        }
        if($ret['password'] != getMd5Password($data['password'])) {
            $this->ajaxReturn(array('code'=>2,'msg'=>'密码错误'));
        }

        D("Admin")->updateByAdminId($ret['admin_id'],array('lastlogintime'=>time(),'lastloginip'=>get_client_ip()));

        session('User', $ret);

        $param['ww-token'] = jwt($ret['admin_id']);
        $temp['token'] = $param['ww-token'];
        $temp['admin_id'] = $ret['admin_id'];
        $temp['recent'] = time();
        $jwt[] = $temp;
        S('jwt', $jwt);

        $res = array(
            'admin_id' => $ret['admin_id'],
            'username' => $ret['username'],
            'phone' => I('phone'),
            'ww_token'=> $param['ww-token'],
             //查出角色名称和公司名称
            'company_name' => M('company')->where(array('company_id'=>$ret['company_id']))->getField('company_name'),
            'role_name' => M('sysrole')->where(array('id'=>$ret['role_id']))->getField('name'),
        );

        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功','data'=>$res));
    }

    public function regNew()
    {
        # code...
        $data['username'] = I('username');
        $data['password'] = getMd5Password(I('password'));
        $data['phone'] = I('phone');
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
        $data['company_id'] = intval(I('company_id'));
	if(!$data['company_id']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'单位不能为空！' ));
        }
        $sysrole = M('sysrole')->where(array('name'=>'检测员'))->find();
        $data['role_id'] = $sysrole['id'];
	    if(!$data['role_id']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'角色不能为空！' ));
        }
        if(!$data['phone']){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'手机号不能为空！' ));
        }
        $re=D('Admin')->insert($data);
        if($re){
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'注册成功，请等待审核','data'=>'0'));
        }else{
            $this->ajaxReturn(array('code' =>1 ,'msg'=>'注册失败！' ));
        }
    }

    public function logout(){
        $token = get_header_value('ww-token');
        if($token){
            $jwt = S('jwt');
            for($i=0; $i<count($jwt); $i++){
                if($jwt[$i]['token'] == $token){
                    array_splice($jwt, $i, 1);
                    S('jwt', $jwt);
                    S('admin_id', 0);
                    break;
                }
            }
        };
        session(null);
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！' ));
    }

    /**
     * 刷新token
     * @return array
     */
    public function token(){
        $token = get_header_value('ww-token');
        if($token){
            $jwt = S('jwt');
            for($i=0; $i<count($jwt); $i++){
                if($jwt[$i]['token'] == $token){
                    $jwt[$i]['token'] = jwt($jwt[$i]['admin_id']);
                    S('jwt', $jwt);
                    $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>array('ww_token'=>$jwt[$i]['token']) ));
                }
            }
        }
        $this->ajaxReturn(array('code' =>401 ,'msg'=>'请登录！' ));
    }



}