<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class IndexController extends CommonController {

    public function index(){
        $this->show('这是接口的入口地址，说明环境安装已经正常。','utf-8');
    }
    public function get_header_value($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }


    protected function curl_get($url, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }


    public function opendoor(){
        try {
            if(I('deviceid')){
                $token = $this->get_header_value('ww-token');
                $jwt = explode('.', $token);
                $payload = json_decode(base64_decode($jwt[1]), true);
                //调用接口判断token
                $result = $this->curl_get('http://youzi.wenweikeji.com/api/verifyToken',['ww-token:'.$token]);
		        $result = json_decode($result,true);
                if($result['code'] != 200){
                    $result['data'] = new \stdClass();
		            $result=json_encode($result,true);
                    exit($result);
                }
                $device_id = I('deviceid');
                //判断当前设备是否是该送货员负责的设备
                $deviceinfomodel = M('deviceinfo');
                $deviceinfo = $deviceinfomodel->where('(id="'.$device_id.'")')->find();
                if($payload['user_id'] != $deviceinfo['userid']){
                    $this->ajaxReturn(array('code'=>230,'msg'=>'对不起，该柜子不属于您'));
                }
                if(!$deviceinfo['devstatus']){
                    $this->ajaxReturn(array('code'=>240,'msg'=>'当前设备不在线'));
                }
                $opendevmodel = M('opendev_log');
                $data = array(
                    'log_id' => substr ( create_uuid(), 4, 14),
                    'device_id'  => $device_id,
                    'sn' => $deviceinfo['userid'],
                    'lane' => 0,
                    'num' => 0,
                    'sto' => 0,
                    'charge' => 0,
                    'locked' => 1,
                    'op' => 0,
                    'log_time' => date('Y-m-d H:i:s')
                );
                $opendevmodel->add($data);
                $data_dev['id'] = $device_id;
                $data_dev['sn'] = $data['log_id'];
                $data_dev['lane'] = 0;
                $data_dev['num'] = 0;
                $data_dev['sto'] = 0;
                $data_dev['lock'] = 1;
                $reqjson = json_encode($data_dev);
                $mqtt = new \Org\Util\phpMQTT("120.77.245.43", 1883, "iotserver_victor_test"); //Change client name to something unique
                $topic = "/zq100n/dev/sub/$device_id";
                if ($mqtt->connect(true,null,'server','xcent123!@#')) {
                    $mqtt->publish($topic,$reqjson,0);
                    $mqtt->close();
                }else{
                    $this->ajaxReturn(array('code'=>500,'msg'=>'服务链接失败'));
                }
                $data = array(
                    'deviceid' =>$device_id,
                    'log_id' =>$data['log_id'],
                );
                $this->ajaxReturn(array('code'=>200,'msg'=>'开柜中','data'=>$data));
            }else{
                $this->ajaxReturn(array('code'=>220,'msg'=>'设备ID不能为空'));
            }
        }catch(Exception $e) {
            exit($e->getMessage());
        }
    }

    public function hasopendoor(){
        if(I('deviceid') && I('log_id')) {
            $token = $this->get_header_value('ww-token');
            $jwt = explode('.', $token);
            $payload = json_decode(base64_decode($jwt[1]), true);
            //调用接口判断token
            $result = $this->curl_get('http://youzi.wenweikeji.com/api/verifyToken',['ww-token:'.$token]);
            $result = json_decode($result,true);
            if($result['code'] != 200){
                $result['data'] = new \stdClass();
                $result=json_encode($result,true);
                exit($result);
            }
            $device_id = I('deviceid');
            //判断当前设备是否是该送货员负责的设备
            $deviceinfomodel = M('deviceinfo');
            $deviceinfo = $deviceinfomodel->where('(id="'.$device_id.'")')->find();
            if($payload['user_id'] != $deviceinfo['userid']){
                $this->ajaxReturn(array('code'=>230,'msg'=>'对不起，该柜子不属于您'));
            }
            if(!$deviceinfo['devstatus']){
                $this->ajaxReturn(array('code'=>240,'msg'=>'当前设备不在线'));
            }
            $log_id = I('log_id');
            $opendevmodel = M('opendev_log');
            $opendevlog = $opendevmodel->where('(log_id="' . $log_id . '")')->find();
            $data = array(
                'lock' => $opendevlog['locked'],
                'op' => $opendevlog['op'],
                'deviceid' => $device_id,
            );  
            if ($opendevlog['op'] && $opendevlog['locked']) {
                $this->ajaxReturn(array('code' => 200, 'msg' => '开柜成功', 'data' => $data));
            } else {
                unset($data['locked']);
                $this->ajaxReturn(array('code' => 210, 'msg' => '开柜失败', 'data' => $data));
            }
        }else{
                $this->ajaxReturn(array('code'=>220,'msg'=>'设备ID和日志ID不能为空'));
        }
    }


    public function setstock(){
        if($_POST['devicelistId'] && $_POST['num']) {
            $token = $this->get_header_value('ww-token');
            $jwt = explode('.', $token);
            $payload = json_decode(base64_decode($jwt[1]), true);
            //调用接口判断token
            $result = $this->curl_get('http://youzi.wenweikeji.com/api/verifyToken',['ww-token:'.$token]);
            $result = json_decode($result,true);
            if($result['code'] != 200){
                $result['data'] = new \stdClass();
                $result=json_encode($result,true);
                exit($result);
            }
            $devicelistid = $_POST['devicelistId'];
            //补货库存
            $num = $_POST['num'];
            //现有库存
            $devicelistmodel = M('devicelist');
            $devicelistinfo = $devicelistmodel->where('(id="'.$devicelistid.'")')->find();
            //最新库存
            $newsto = $devicelistinfo['sto'] + $num;
            if($newsto > $devicelistinfo['max_sto']){
                $this->ajaxReturn(array('code'=>250,'msg'=>'设置错误'));
            }
            $device_id = $devicelistinfo['deviceid'];
            //判断当前设备是否是该送货员负责的设备
            $deviceinfomodel = M('deviceinfo');
            $deviceinfo = $deviceinfomodel->where('(id="'.$device_id.'")')->find(); 
            if($payload['user_id'] != $deviceinfo['userid']){
                $this->ajaxReturn(array('code'=>230,'msg'=>'对不起，该柜子不属于您'));
            }
            if(!$deviceinfo['devstatus']){
                $this->ajaxReturn(array('code'=>240,'msg'=>'当前设备不在线'));
            }
            //设置通知消息为已配送
            M('chargelist')->where(array('devicelistId'=>$devicelistid))->save(array('status'=>2));
            // 新增配送记录
            $reps = array(
                'deviceId'=>$device_id,
                'goods_id'=>$devicelistinfo['goods_id'],
                'num'=> $num,
                'devicelistId'=>$devicelistid
            );
            if(!M('replenish_log')->add($reps)){
                $this->ajaxReturn(array('code'=>240,'msg'=>'配货失败'));
            }
            $data['id'] = $device_id;
            $data['sn'] = "0";
            $data['lane'] = intval($devicelistinfo['lane']);
            $data['num'] = 0;
            $data['sto'] = intval($newsto);
            $data['lock'] = 0;
            $reqjson = json_encode($data);
            $mqtt = new \Org\Util\phpMQTT("120.77.245.43", 1883, "iotserver_victor_test"); //Change client name to something unique
            $topic = "/zq100n/dev/sub/$device_id";
            if ($mqtt->connect(true, null, 'server', 'xcent123!@#')) {
                $mqtt->publish($topic, $reqjson, 0);
                $mqtt->close();
            } else {
                $this->ajaxReturn(array('code'=>500,'msg'=>'服务链接失败'));
            }
            $this->ajaxReturn(array('code'=>200,'msg'=>'配货成功'));
        }else{
            $this->ajaxReturn(array('code'=>220,'msg'=>'货道ID和补货库存数不能为空'));
        }
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

    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return $t2 . '' .  ceil( ($t1 * 1000) );
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

    public function getLotteryList()
    {
        $openid=I('openid');

        $re=D('users')->where('lid>=0 AND phone!=\'\'')->field('lname,name,phone')->join('left join think_lottery on think_users.lid=think_lottery.id')->select();
        foreach ($re as $key => &$value) {
            # code...
            $value['phone']=substr_replace($value['phone'], '****', 3,4);
            $value['name']=cc_msubstr($value['name'],0,4);
        }
        $where['openid']=$openid;
        $where['lid']=array('gt',0);
        $my=D('users')->where($where)->join('left join think_lottery on think_users.lid=think_lottery.id')->find();
        if(!$my){
            $my['lname']='';
        }
        $this->ajaxReturn(array('ret'=>0,'data'=>$re,'my'=>$my['lname']));
    }

    /*概率抽奖*/
    public function getLottery()
    {
        /*
        if(!IS_POST){

          $this->ajaxReturn( array('ret' => 3,'msg'=>'数据需要提交!' ));
        }
    */
        $this->ajaxReturn( array('ret' => 2,'msg'=>'活动已经结束','reg'=>‘1’));
    $openid=I('openid');


    if($openid=='' || is_null($openid)){
        $this->ajaxReturn( array('ret' => 3,'msg'=>'no openid' ));
    }


    $tm=date('Y-m-d H:i:s',mktime(0,0,0));

    $ops=M('ops');

    $where['openid']=array('eq',$openid);
    $where['time']=array('egt',$tm);
    $opsre=$ops->where($where)->count();
    if($opsre>=5000){
        $this->ajaxReturn( array('ret' =>5000,'msg'=>'今天已经超过800次了！' ));
    }


    $userlottery=M('users');
    $userlottery->startTrans();
    $where['openid']=array('eq',$openid);
    $where['lid']=array('GT','0');
    $re=$userlottery->lock(true)->where( $where)->find();

    if ($re) {
        $userlottery->rollback();
        if($re['name']=='' || $re['phone']=='' || $re['addr']==''){
            $reg=1;
            $msg='已经中过奖，每个人只能获得一次奖品，请注意填写资料';
        }else{
            $reg=0;
            $msg='再接再厉，未来是你的！快与好基友一起挑战吧';
        }
        $this->ajaxReturn( array('ret' => 9,'msg'=>$msg,'reg'=>$reg));
    }


    $allLottery=D('lottery');
    $lotterys=$allLottery->where('num>0')->select();

    $newLists=array_sort($lotterys,'gl');

    $total=0;
    foreach ($newLists as $v => $d) {
        # code...
        $total+=$d['gl'];
    }


    $rd=rand(0,$total);
    $total2=0;

    foreach ($newLists as $key => $value) {

        $total2+=$value['gl'];


        if($rd<$total2){
            $reops= D('ops')->add(array('openid'=>$openid,'lid'=>$value['id']));

            if($value['num']>0){
                $allLottery->where('id='.$value['id'])->setDec('num',1);
            }
            $re=$userlottery->where(array('openid'=>$openid))->save(array('nickname'=>$reops,'lid'=>$value['id'],'logintime'=>date('Y-m-d H:i:s',time())));

            if($re){
                $userlottery->commit();
                $this->ajaxReturn( array('ret' => 0,'lid'=>$value['id'],'name'=>$value['lname'],'rd'=>$rd));
            }else{
                $userlottery->rollback();
                $this->ajaxReturn( array('ret' => 2,'msg'=>'数据写入错误!' ));
            }


            break;
        }
    }
    $userlottery->rollback();
    $this->ajaxReturn( array('ret' => 1,'msg'=>'意外错误!'));

  }
}