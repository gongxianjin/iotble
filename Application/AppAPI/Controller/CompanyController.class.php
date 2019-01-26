<?php

namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class CompanyController extends Controller {

    public function index() {
        $companys = M('company')->select();
        foreach($companys as $key=>$item){
            $companys[$key]['province'] = M('region')->where(array('region_id' => $item['province']))->getField('region_name');
            $companys[$key]['city'] = M('region')->where(array('region_id' => $item['city']))->getField('region_name');
            $companys[$key]['district'] = M('region')->where(array('region_id' => $item['district']))->getField('region_name');
            $companys[$key]['createtime'] = date('Y-m-d H:i:s',$item['lastlogintime']);
        }
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$companys));
    }

}