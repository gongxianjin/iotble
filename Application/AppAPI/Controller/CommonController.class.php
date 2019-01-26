<?php
namespace AppAPI\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function __construct() {
        header("Content-type: text/html; charset=utf-8");
        parent::__construct();
        $this->_init();
    }

    /**
     * 初始化
     * @return
     */
    private function _init() {
        // 验证token，判断是否登录
        $token = get_header_value('ww-token');
        if($token){
            $jwt = S('jwt');
            for($i=0; $i<count($jwt); $i++){
                if($jwt[$i]['token'] == $token){
                    $client_jwt = explode('.', $token);
                    $payload = json_decode(base64_decode($client_jwt[1]), true);
                    // 超过规定时间未操作则销毁token
                    $time = time();
                    if($time - $jwt[$i]['recent'] >= C('jwt_remove')){
                        array_splice($jwt, $i, 1);
                        header('Content-Type:application/json; charset=utf-8');
                        $this->ajaxReturn(array('code' =>403 ,'msg'=>'token已销毁！' ));
                    }
                    // token过期则生成新的token
                    if($payload['exp'] <= $time){
                        header('Content-Type:application/json; charset=utf-8');
                        $this->ajaxReturn(array('code' =>402 ,'msg'=>'token已过期！' ));
                    }
                    $jwt[$i]['recent'] = $time;
                    S('jwt', $jwt);
                    // 缓存用户ID
                    $_SESSION['admin_id'] = $jwt[$i]['admin_id'];
                    $ret = D('Admin')->getAdminByAdminId($_SESSION['admin_id']);
                    session('User', $ret);
                    return true;
                }
            }
            
        }
        header('Content-Type:application/json; charset=utf-8');
        $this->ajaxReturn(array('code' =>401 ,'msg'=>'请登录！' ));
    }


    /**
     * @return 获取排行的数据
     */
    public function getRank() {
        $conds['status'] = 1;
        $news = D("News")->getRank($conds,10);
        return $news;
    }

    public function error($message = '') {
        $message = $message ? $message : '系统发生错误';
        $this->assign('message',$message);
        $this->display("Index/error");
    }
}