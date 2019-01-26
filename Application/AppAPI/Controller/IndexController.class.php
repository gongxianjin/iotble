<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class IndexController extends Controller {

    public function index(){
        $this->show('这是接口的入口地址，说明环境安装已经正常。','utf-8');
    }

    public function hasReged()
    {
        $openid=I('openid');
        $users=D('users');
        $where=array('openid' =>$openid);
        $re=$users->where($where)->find();
        if($re){
            $this->ajaxReturn(array('ret'=>0,'msg'=>'ok','user'=>$re));
        }else{
            $this->ajaxReturn(array('ret'=>1,'msg'=>'not reg'));
        }
    }


    public function getUser()
    {
        # code...
        $openid=I('openid');
        $users=D('users');
        $where=array('openid' =>$openid);
        $re=$users->where($where)->find();
        $re2=M('help')->where(array('openid'=>$openid))->count();

        if($re){
            $this->ajaxReturn(array('ret'=>0,'msg'=>'ok','data'=>$re,'count'=>$re2));
        }else{
            $this->ajaxReturn(array('ret'=>1,'msg'=>'读取用户信息失败'));
        }
    }


    public function regNew()
    {
        # code...
        $data['openid']=I('openid');
        $data['nickname']=I('nickname');
        $data['sex']=I('sex');
        $data['headimgurl']=I('headimgurl');
        $data['uid']=I('uid');
        if(!$data['openid']){
            $this->ajaxReturn(array('ret' =>2 ,'msg'=>'openid uid错误' ));
        }

        $re=D('users')->add($data);
        if($re){
            $this->ajaxReturn(array('ret' =>0 ,'msg'=>'ok','data'=>$data ));
        }else{
            $this->ajaxReturn(array('ret' =>1 ,'msg'=>'接受用户信息失败' ));
        }
    }





    public function postPhone()
    {
        # code...
        $name=I('name');
        $phone=I('phone');
        $addr=I('addr');
        $openid=I('openid');

        /*$where['phone']=array('eq',$phone);
        $where['openid']=array('neq',$openid);
        $re=M('users')->where($where)->find();
        if($re){
          $this->ajaxReturn(array('ret'=>1,'msg'=>'手机号重复'));
        }*/

        $re=M('users')->where(array('openid'=>$openid))->save(array('name'=>$name,'addr'=>$addr,'phone'=>$phone));
        //if($re){
        $this->ajaxReturn(array('ret'=>0,'msg'=>'提交成功，我们将在15个工作日内邮寄奖品给您！'));
        /*}else{
          $this->ajaxReturn(array('ret'=>1,'msg'=>'生成错误'));
        }*/
    }

    public function saveImg()
    {

        $data = base64_decode(I("base64"));

        $urlUploadImages = "./upload/";
        $nameImage = 'userimg_'.time().rand(1,10000).".jpg";
        $img = imagecreatefromstring($data);
        $des= imagecreatetruecolor(750 , 1256);
        $isok=imagecopy($des, $img, 0, 0, 0, 0, 750, 1256);
        if($isok){
            imagejpeg($des, $urlUploadImages.$nameImage,80);

            imagedestroy($img);
            imagedestroy($des);
            $path=str_replace('./', 'http://2016.ithinky.com/haier07/api/', $urlUploadImages).$nameImage;


            $this->ajaxReturn(array('ret'=>0,'msg'=>'ok','img'=>$path));

        }else{
            $this->ajaxReturn(array('ret'=>1,'msg'=>'生成错误'));
        }

    }
    public function read()
    {
        # code...
        $openid=I('openid');
        if($openid=='' || is_null($openid)){
            $this->ajaxReturn( array('ret' => 3,'msg'=>'no openid' ));
        }
        $arr=array();
        for ($i=0; $i < 1; $i++) {
            array_push($arr, array('openid' => $openid ));
        }
        D('read')->addAll($arr);
        $re=D('read')->count();
        $this->ajaxReturn(array('ret'=>0,'data'=>$re));
    }
    public function getNumofRead()
    {
        $re=D('read')->count();
        $this->ajaxReturn(array('ret'=>0,'data'=>$re));
    }
}