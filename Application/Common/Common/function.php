<?php

/**
 * 公用的方法
 */

function  show($status, $message,$data=array()) {
    $reuslt = array(
        'status' => $status,
        'message' => $message,
        'data' => $data,
    );

    exit(json_encode($reuslt));
}
function getMd5Password($password) {
    return md5($password . C('MD5_PRE'));
}
function getMenuType($type) {
    return $type == 1 ? '后台菜单' : '前端导航';
}

function getRoleName($roleid){
    $res = M('sysrole')->find($roleid);
    return $res['name'];
}

function getMemberRoleName($roleid){
    if($roleid){
        $res = M('role')->find($roleid);
        return $res['role_name'];
    }else{
        return false;
    }
}

function status($status) {
    if($status == 0) {
        $str = '关闭';
    }elseif($status == 1) {
        $str = '正常';
    }elseif($status == -1) {
        $str = '删除';
    }
    return $str;
}

function devstatus($status) {
    if($status == 0) {
        $str = '离线';
    }elseif($status == 1) {
        $str = '在线';
    }
    return $str;
}

function orderstatus($status) {
    if($status == 1) {
        $str = '成功';
    }elseif($status == 4) {
        $str = '失败';
    }else{
        $str = "无";
    }
    return $str;
}

function is_show($show) {
    if($show == 0) {
        $str = '不显示';
    }elseif($show == 1) {
        $str = '显示';
    }
    return $str;
}
function getAdminMenuUrl($nav) {
    if($nav['a']){
        $url = '/admin.php?c='.$nav['c'].'&a='.$nav['a'];
    }else{
        $url = '/admin.php?c='.$nav['c'];
    }
    if($nav['f']=='index') {
        $url = '/admin.php?c='.$nav['c'];
    }
    return $url;
}
function getActive($navc){
    $c = strtolower(CONTROLLER_NAME);
    if(strtolower($navc) == $c) {
        return 'class="active"';
    }
    return '';
}
function showKind($status,$data) {
    header('Content-type:application/json;charset=UTF-8');
    if($status==0) {
        exit(json_encode(array('error'=>0,'url'=>$data)));
    }
    exit(json_encode(array('error'=>1,'message'=>'上传失败')));
}
function getLoginUsername() {
    return $_SESSION['adminUser']['username'] ? $_SESSION['adminUser']['username']: '';
}
function getCatName($navs, $id) {
    foreach($navs as $nav) {
        $navList[$nav['menu_id']] = $nav['name'];
    }
    return isset($navList[$id]) ? $navList[$id] : '';
}
function getCopyFromById($id) {
    $copyFrom = C("COPY_FROM");
    return $copyFrom[$id] ? $copyFrom[$id] : '';
}
function isThumb($thumb) {
    if($thumb) {
        return '<span style="color:red">有</span>';
    }
    return '无';
}

/**
+----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
+----------------------------------------------------------
 * @static
 * @access public
+----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
+----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    $len = substr($str);
    if(function_exists("mb_substr")){
        if($suffix)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if($suffix && $len>$length)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}



// 检测单元操作权限
function checkOperModule($c, $a='index'){
    if(!$c || !$a) return false;
    $ingnore_controller = array('Index');
    $ingnore_action = array('updateRun','select', 'order','save','listorder');
    if(in_array($c, $ingnore_controller) || in_array($a, $ingnore_action)) return true;
//		if($_SESSION['SADMIN_ID']['admin_id'] ==  1) return true;
    static $G_POWER;
    if(!$G_POWER){
        $G_POWER = M('sysrole')->find($_SESSION['adminUser']['role_id']);
    }
    return $G_POWER && stristr($G_POWER['power_id'], $c.'-'.$a) ? true :false;
}



/**
 * 生成UUID
 * @access public
 * @return string
 * @author knight
 */

function create_uuid(){

    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ( $chars, 0, 8 ) . '-'
        . substr ( $chars, 8, 4 ) . '-'
        . substr ( $chars, 12, 4 ) . '-'
        . substr ( $chars, 16, 4 ) . '-'
        . substr ( $chars, 20, 12 );
    return $uuid ;

}