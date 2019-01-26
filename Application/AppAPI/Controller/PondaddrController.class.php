<?php
namespace AppAPI\Controller;
use Think\Controller;
use Think\Exception;

class PondaddrController extends CommonController {

    /**
     * 获得指定国家的所有省份
     *
     * @access      public
     * @param       int     country    国家的编号
     * @return      array
     */
    protected function get_regions($type = 1, $parent = 1)
    {
        $data = array(
            'region_type'=>$type,
            'parent_id'=>$parent,
        );
        $res = M('region')->field('region_id,region_name')->where($data)->select();
        return $res;
    }

    public function regions(){
        if($_POST){
            $type   = !empty($_POST['type'])   ? intval($_POST['type'])   : 0;
            $parent = !empty($_POST['parent']) ? intval($_POST['parent']) : 0;
            $arr['regions'] = $this->get_regions($type, $parent);
            $arr['target'] = $_POST['target'];
            $this->ajaxReturn(array('status'=>200,'data'=>$arr));
        }
    }

    public function Add(){
        if($_POST) {
            $pond_num   = !empty($_POST['pond_num'])   ? intval($_POST['pond_num'])   : 0;
            $province   = !empty($_POST['province_app'])   ? ($_POST['province_app'])   : null;
            $city = !empty($_POST['city_app']) ? ($_POST['city_app']) : null;
            $district = !empty($_POST['district_app']) ? ($_POST['district_app']) : null;
            $company_id   = $_SESSION['User']['company_id'];
            if(!$company_id){
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用单位管理员角色登录！' ));
            }
            if(!$province) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请选择省！' ));
            }
            if(!$city) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请选择市！' ));
            }
            if(!$district) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'请选择区！' ));
            }
            if(!isset($_POST['address']) || !$_POST['address']) {
                $this->ajaxReturn(array('code' =>2 ,'msg'=>'详细地址不能为空！' ));
            }
            $lat = !empty($_POST['lat']) ? intval($_POST['lat']) : 0;
            $lng = !empty($_POST['lng']) ? intval($_POST['lng']) : 0;
            $data = array(
                'pond_num' => $pond_num,
                'company_id' => $company_id,
                'province_app'   => $province,
                'city_app' => $city,
                'district_app' => $district,
                'address' => $_POST['address'],
                'lat' => $lat,
                'lng' => $lng,
            );
            if(M('dev_address')->add($data)){
                $this->ajaxReturn(array('code' =>200 ,'msg'=>'新增成功','data'=>'0'));
            }else{
                $this->ajaxReturn(array('code' =>1 ,'msg'=>'新增失败！' ));
            }
        }else{
		$this->ajaxReturn(array('code' =>1 ,'msg'=>'参数错误！' ));
	}
    }

    public function index() {
        $dev_address = M('dev_address')->where(array('company_id'=>$_SESSION['User']['company_id']))->select();
        foreach($dev_address as $key=>$item){
            $dev_address[$key]['province'] = M('region')->where(array('region_id' => $item['province']))->getField('region_name');
            $dev_address[$key]['city'] = M('region')->where(array('region_id' => $item['city']))->getField('region_name');
            $dev_address[$key]['district'] = M('region')->where(array('region_id' => $item['district']))->getField('region_name');
	    $dev_address[$key]['company_name'] =  M('company')->where(array('company_id'=>$item['company_id']))->getField('company_name');
            unset($dev_address[$key]['company_id']);
	    unset($dev_address[$key]['lat']);
	    unset($dev_address[$key]['lng']);
        }
        $this->ajaxReturn(array('code' =>200 ,'msg'=>'成功！','data'=>$dev_address));
    }

    public function updateRun(){
        isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
        $pond_num   = !empty($_POST['pond_num'])   ? intval($_POST['pond_num'])   : 0;
        $province   = !empty($_POST['province_app'])   ? ($_POST['province_app'])   : null;
        $city = !empty($_POST['city_app']) ? ($_POST['city_app']) : null;
        $district = !empty($_POST['district_app']) ? ($_POST['district_app']) : null;
        $company_id   = $_SESSION['User']['company_id'];
        if(!$company_id){
            $this->ajaxReturn(array('code' =>2 ,'msg'=>'请使用单位管理员角色登录！' ));
        }
        if(!isset($_POST['address']) || !$_POST['address']) {
            return show(0,'详细地址不能为空');
        }
        $lat = !empty($_POST['lat']) ? intval($_POST['lat']) : 0;
        $lng = !empty($_POST['lng']) ? intval($_POST['lng']) : 0;
        $data = array(
            'pond_num' => $pond_num,
            'company_id' => $company_id,
            'province_app'   => $province,
            'city_app' => $city,
            'district_app' => $district,
            'address' => $_POST['address'],
            'lat' => $lat,
            'lng' => $lng,
        );
        if( M('dev_address')->where('id='."'$id'")->save($data)){
            $this->ajaxReturn(array('code' =>200 ,'msg'=>'修改成功','data'=>'0'));
        }else{
            $this->ajaxReturn(array('code' =>1 ,'msg'=>'修改失败！' ));
        }
    }

    //删除
    public function del(){
        try {
            if ($_POST) {
                isset($_POST['id']) ? $address_id = $_POST['id'] : $this->error('非法操作');
                // 执行数据更新操作
                if( M('dev_address')->delete($address_id)){
                    $this->ajaxReturn(array('code' =>200 ,'msg'=>'删除成功','data'=>'0'));
                }else{
                    $this->ajaxReturn(array('code' =>1 ,'msg'=>'删除失败！' ));
                }
            }
        }catch(Exception $e) {
            $this->ajaxReturn(array('code' =>1 ,'msg'=>$e->getMessage()));
        }
        $this->ajaxReturn(array('code' =>1 ,'msg'=>'没有提交的数据！' ));
    }


}