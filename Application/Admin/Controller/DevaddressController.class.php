<?php
/**
 * 后台菜单相关
 */

namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class DevaddressController extends CommonController {

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
            $this->ajaxReturn(array('status'=>1,'data'=>$arr));
        }
    }

    public function add(){
        if($_POST) {
            $province   = !empty($_POST['province'])   ? intval($_POST['province'])   : 0;
            $city = !empty($_POST['city']) ? intval($_POST['city']) : 0;
            $district = !empty($_POST['district']) ? intval($_POST['district']) : 0;
            if(!isset($_POST['address']) || !$_POST['address']) {
                return show(0,'详细地址不能为空');
            }
            $lat = !empty($_POST['lat']) ? intval($_POST['lat']) : 0;
            $lng = !empty($_POST['lng']) ? intval($_POST['lng']) : 0;
            $data = array(
                'province'   => $province,
                'city' => $city,
                'district' => $district,
                'address' => $_POST['address'],
                'lat' => $lat,
                'lng' => $lng,
            );
            //添加操作日志
            $log = '新增地址'.$data['address'];
            $this->addOperLog($log);
            if(M('dev_address')->add($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            $this->province_list = $this->get_regions(1, 1);
            $this->display();
        }
    }

    public function index() {
        $dev_address = M('dev_address')->select();
        foreach($dev_address as $key=>$item){
            $dev_address[$key]['province'] = M('region')->where(array('region_id' => $item['province']))->getField('region_name');
            $dev_address[$key]['city'] = M('region')->where(array('region_id' => $item['city']))->getField('region_name');
            $dev_address[$key]['district'] = M('region')->where(array('region_id' => $item['district']))->getField('region_name');
        }
        $this->assign('address',$dev_address);
    	$this->display();
    }

    public function update(){
        isset($_GET['id']) ? $DevaddressId = $_GET['id'] : $this->error('非法操作');
        $this->devaddress = $devaddress = M('dev_address')->find($DevaddressId);
        $this->province_list = $this->get_regions(1, 1);
        $this->city_list = $this->get_regions(2, $devaddress['province']);
        $this->district_list = $this->get_regions(3, $devaddress['city']);
        $this->display();
    }

    public function updateRun(){
        isset($_POST['address_id']) ? $address_id = $_POST['address_id'] : $this->error('非法操作');
        $province   = !empty($_POST['province'])   ? intval($_POST['province'])   : 0;
        $city = !empty($_POST['city']) ? intval($_POST['city']) : 0;
        $district = !empty($_POST['district']) ? intval($_POST['district']) : 0;
        if(!isset($_POST['address']) || !$_POST['address']) {
            return show(0,'详细地址不能为空');
        }
        $lat = !empty($_POST['lat']) ? intval($_POST['lat']) : 0;
        $lng = !empty($_POST['lng']) ? intval($_POST['lng']) : 0;
        $data = array(
            'province'   => $province,
            'city' => $city,
            'district' => $district,
            'address' => $_POST['address'],
            'lat' => $lat,
            'lng' => $lng,
        );
        //添加操作日志
        $log = '更新设备地址'.$data['address'];
        $this->addOperLog($log);
        M('dev_address')->where('address_id='."'$address_id'")->save($data) ? show(1,'修改成功'):show(0,'修改失败');
    }

    //删除
    public function del(){
        try {
            if ($_POST) {
                isset($_POST['id']) ? $address_id = $_POST['id'] : $this->error('非法操作');
                //添加操作日志
                $log = '删除设备地址ID'.$address_id;
                $this->addOperLog($log);
                // 执行数据更新操作
                if(M('dev_address')->delete($address_id)){
                    return show(1, '删除成功');
                }else{
                    return show(1, '删除失败');
                }
            }
        }catch(Exception $e) {
            return show(0,$e->getMessage());
        }
        return show(0,'没有提交的数据');
    }


}