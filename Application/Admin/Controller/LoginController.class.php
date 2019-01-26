<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * use Common\Model 这块可以不需要使用，框架默认会加载里面的内容
 */
class LoginController extends Controller {

    public function index(){
        if(session('adminUser')){
            $this->redirect('admin/index');
        }
    	return $this->display();
    }
    
    public function check(){
         $phone = $_POST['username'];
         $password = $_POST['password'];
         $code = $_POST['code'];

        if(!trim($phone)){
             return show(0,'用户名不能为空');
         }
         if(!trim($password)){
             return show(0,'密码不能为空');
         }
        if(!trim($code)){
            return show(0,'验证码不能为空');
        }
        if(I('code','','strtolower') != session('verify')){
            return show(0,'验证码错误');
        }
        $ret = D('Admin')->getAdminByPhone($phone);

        if(!$ret || $ret['status'] !=1) {
            return show(0,'该用户不存在');
        }
        if($ret['status'] == 0) {
            return show(0,'该用户正在审核中');
        }
        if($ret['password'] != getMd5Password($password)) {
            return show(0,'密码错误');
        }

        D("Admin")->updateByAdminId($ret['admin_id'],array('lastlogintime'=>time(),'lastloginip'=>get_client_ip()));

        // 添加操作日志记录
        $data = array(
            'log_time' => time(),
            'user_id'  => $ret['admin_id'],
            'log_info' => '登录系统'.$ret['username'],
            'ip_address' => get_client_ip(),
        );

        // 执行数据更新操作
        if(!M('admin_log')->add($data) ){
            dump(M('admin_log')->getError());
        }
        
        session('adminUser', $ret);

        $ses = array(
            'admin_id' => $ret['admin_id'],
            'username' => $ret['username'],
        );
        $stoken = md5(base64_encode(json_encode($ses)));
        $cookie_expire = time() + '3600';
        cookie('stoken', $stoken, $cookie_expire);

        return show(1,'登录成功');
    }

    public function   loginout(){
        session('adminUser',null);
        $cookie_expire = time() + '3600';
        cookie('stoken', null, $cookie_expire);
        redirect('/admin.php?c=login&a=index');
    }


    public function verify(){ 
        $Image = new \Org\Util\Image;
        ob_clean();
        $Image::verify();
    }

    // 心跳
    public function Heart(){
        $k = array(
            'stoken'
        );
        $cookie_expire = time() + '3600';
        foreach ($k as $v){
            $cookie =  cookie($v);
            if($cookie){
                cookie('stoken', $cookie, $cookie_expire);
            }
        }
    }

}