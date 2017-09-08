<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;


class PayController extends Controller {

    private $app_id = 'wxf63073c000a25390';
    private $mch_id = '1484204972';
    private $makesign = '8934e7d15453e97507ef794cf7b0519d';
    private $parameters=NULL;
    private $notify='http://www.xcentiot.com/appAPI/wxcallback/wxpay_notify';
    private $app_secret='6fc5fe5277b96d72636300476c5f3345';
    public $error  = 0;
    public $orderid = null;
    public $openid = '';

    //在类初始化方法中，引入相关类库
    public function _initialize() {
        vendor('Alipay.pay');
        vendor('Weixin.JsApiPay');
    }

    //doalipay方法
    /*该方法其实就是将接口文件包下alipayapi.php的内容复制过来
      然后进行相关处理
    */
    public function doalipay(){
        header('Content-type:text/html;charset=utf-8');
        //下单支付方式
        $pay = new \Pay();
        if($_GET){
            $out_trade_no = $_GET['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
            $subject = $_GET['goods_name'];  //订单名称 //必填 通过支付页面的表单进行传递
            $total_amount =$_GET['order_amount'];   //付款金额  //必填 通过支付页面的表单进行传递
            $body = $_GET['goods_name'];;  //订单描述 通过支付页面的表单进行传递
            $pay->Payinfo($out_trade_no, $subject,$total_amount, $body);
            //直接跳转到支付成功页面
        }
    }



    //进行微信支付
    public function wxpay(){
        /*
        $_SESSION['openid']=null;
        if($_SESSION['openid']==null){
            $this->Wxcallback();  //获取用户的信息
        }
        */

//        //①、获取用户openid
//        $tools = new \JsApiPay();
//        if($_SESSION['openid']){
//            $openId = $_SESSION['openid'];
//        }else{
//            $openId = $tools->GetOpenid();
//            $_SESSION['openid'] = $openId;
//        }
//        $reannumb = $this->randomkeys(6);  //生成随机数 以后可以当做 订单
//        $pays =0.01;
//        //echo $pays;
//
//        $conf = $this->payconfig('Bm'.$reannumb,$pays, '费用支付');
//        //$conf = $this->payconfig($reannumb,$pays * 100, '费用支付');
//        if (!$conf || $conf['return_code'] == 'FAIL') exit("<script>alert('对不起，微信支付接口调用错误!" . $conf['return_msg'] . "');history.go(-1);</script>");
//        $this->orderid = $conf['prepay_id'];
//
//        //生成页面调用参数
//        $jsApiObj["appId"] = $conf['appid'];
//        $timeStamp = time();
//        $jsApiObj["timeStamp"] = "$timeStamp";
//        $jsApiObj["nonceStr"] = $this->createNoncestr();
//        $jsApiObj["package"] = "prepay_id=" . $conf['prepay_id'];
//        $jsApiObj["signType"] = "MD5";
//        $jsApiObj["paySign"] = $this->MakeSign($jsApiObj);
//        $json = json_encode($jsApiObj);
//        $this->assign('parameters',$json);  //
//        $this->title="微信安全支付";
//        session('order',null);
//        session('user_table',null);
        //①、获取用户openid
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();
        $_SESSION['openid'] = $openId;
        $this->display();                 //下面对应的就是我给你发的那个 （wxPay.html）HTML模板
    }


    public function get_wx_openid(){
        $deviceid = $_GET['deviceId'];
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid($deviceid);
        $url = 'http://youzi.wenweikeji.com/view/goodsList.html?deviceId='.$deviceid.'&openid='.$openId;
        if($deviceid && $openId){
            header("Location:".$url."#get_wx_openid_redirect");
        }
    }


    public function pays(){
        if($_GET['openid']){
            $out_trade_no = $_GET['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
            $body = $_GET['goods_name'];  //订单名称 //必填 通过支付页面的表单进行传递
            $pays =$_GET['order_amount'];   //付款金额  //必填 通过支付页面的表单进行传递
            $openid = $_GET['openid'];
            $conf = $this->payconfig($out_trade_no,$pays * 100, $body,$openid);
            //print_r($conf);die;
        }else{
            $conf = null;
        }
        if (!$conf || $conf['return_code'] == 'FAIL') exit("<script>alert('对不起，微信支付接口调用错误!" . $conf['return_msg'] . "');history.go(-1);</script>");
        $this->orderid = $conf['prepay_id'];
        //生成页面调用参数
        $jsApiObj["appId"] = $conf['appid'];
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $this->createNoncestr();
        $jsApiObj["package"] = "prepay_id=" . $conf['prepay_id'];
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $this->MakeSign($jsApiObj);
        $jsApiParameters = json_encode($jsApiObj);
        $shtml = <<<EOT
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>微信支付</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			{$jsApiParameters},
			function(res){
				WeixinJSBridge.log(res.err_msg);
				//alert(res.err_code+'----'+res.err_desc+'-----'+res.err_msg);
				switch(res.err_msg){
					case 'get_brand_wcpay_request:cancel':
						alert('cancel')
						document.getElementById('wxchat').style.display = 'none';
						document.getElementById('wxchat_f_msg').innerHTML = '您已取消支付，可到订单中心再次发起支付';
						document.getElementById('wxchat_fail').style.display = 'block';

					break;

					case 'get_brand_wcpay_request:fail':
						document.getElementById('wxchat').style.display = 'none';
						//document.getElementById('wxchat_f_msg').innerHTML = '支付失败，可到订单中心再次发起支付';
						document.getElementById('wxchat_fail').style.display = 'block';
					break;

					case 'get_brand_wcpay_request:ok':
						document.getElementById('wxchat').style.display = 'none';
						document.getElementById('wxchat_ok').style.display = 'none';

					default:
						//alert(window.location.href)
						//alert(res.err_msg);

					break;
				}


				/*var str = '';
				for(var i in res){
					str += i+'='+res[i]+'&';
				}
				alert(str)*/
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall);
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}

	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress);
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			//callpay();
		}
	};
	callpay();
	</script>
</head>
<body>

<div id="wxchat" style="text-align:center;margin-top:20px; display:none;">
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">{$pays}元</span></b></font><br/><br/>
	<div align="center">
		<button style="width:96%; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>

</div>

<div id="wxchat_fail" style="text-align:center;margin-top:100px;display:none;">
	<font color="#9ACD32"><b id="wxchat_f_msg">支付失败</b></font><br><br><br><br>
</div>

<div id="wxchat_ok" style="text-align:center;margin-top:100px;display:none;">
	<font color="#f00"><b>支付成功</b></font><br><br><br><br>
</div>
</body>
</html>
EOT;
        exit($shtml);
//        echo json_encode(array('parameters'=>$jsApiObj,'error'=>1));
//        die();
    }


    //订单管理
    #微信JS支付参数获取#
    protected function payconfig($no, $fee, $body,$openid)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data['appid']='wxf63073c000a25390';
        $data['mch_id'] = $this->mch_id;           //商户号
        $data['device_info'] = 'WEB';
        $data['body'] = $body;
        $data['out_trade_no'] = $no;               //订单号
        $data['total_fee'] = $fee;                 //金额
        $data['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];  //ip地址
        $data['notify_url'] = $this->notify;
        $data['trade_type'] = 'JSAPI';
//        $data['openid'] = 'oA_f108hhFpo3rAaR1_22O-oHFxQ';   //获取保存用户的openid
        $data['openid'] = $openid;   //获取保存用户的openid
        $data['nonce_str'] = $this->createNoncestr();
        $data['sign'] = $this->MakeSign($data);
        //print_r($data);die;
        $xml = $this->ToXml($data);
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        $arr = $this->FromXml($tmpInfo);
        return $arr;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function Wxcallback()
    {
        $direct = $this->get_page_url(); //当前访问URL
        //$code =Yii::app()->request->getParam('code');  //获取code码号
        $code =$_GET['code'];  //获取code码号
        if($code==null){
            header("Location:"."https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->app_id."&redirect_uri=".urlencode($direct)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect");
        }else{
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->app_id."&secret=".$this->app_secret."&code={$code}&grant_type=authorization_code";
            $res = $this->request_get($url);
            if($res)
            {
                $data = json_decode($res, true);
                //Yii::app()->session["openid"] = $data['openid'];    //设置session
                $_SESSION['openid'] = $data['openid'];    //设置session
                //$this->redirect(array('/baoming/index'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'获取openid出错','v'=>4));
                die();
            }
        }

    }

    public function request_get($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    #获取当前访问完整URL#
    public function get_page_url($site=false){
        $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'];
        if($site) return $this->seldir().'/'; //访问域名网址
        $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
        return $url;

    }
    //返回访问目录
    public function seldir(){
        $baseUrl = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
        //保证为空时能返回可以使用的正常值
        $baseUrl = empty($baseUrl) ? '/' : '/'.trim($baseUrl,'/');
        return 'http://'.$_SERVER['HTTP_HOST'].$baseUrl;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function randomkeys($length)
    {
        $pattern = '1234567890123456789012345678905678901234';
        $key = null;
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 30)};    //生成php随机数
        }
        return $key;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    protected function MakeSign($arr)
    {
        ksort($arr);
        $string = $this->ToUrlParams($arr);
        $string = $string . "&key=" . $this->makesign;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }



}