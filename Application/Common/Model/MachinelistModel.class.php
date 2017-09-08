<?php
namespace Common\Model;
use Think\Model;

/**
 * 货道操作
 * @author  victor
 */
class MachinelistModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('devicelist');
	}
   
    public function getMachinelistByDeviceId($deviceId='') {
        $res = $this->_db->where('deviceId="'.$deviceId.'"')->find();
        return $res;
    }
    public function getMachinelistById($Id) {
        $res = $this->_db->where('id='."'$Id'")
                         ->find();
        return $res;
    }

    public function updateByDevlistId($id, $data) {
        if(!$id) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {
            throw_exception('更新的数据不合法');
        }
        return $this->_db->where('id='."'$id'")->save($data); // 根据条件更新记录
    }

    public function insert($data = array()) {
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getMachinelist($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['devicename']) && $data['devicename'])  {
            $conditions['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['deviceid']) && $data['deviceid'])  {
            $conditions['deviceid'] = $data['deviceid'];
        }
        $offset = ($page - 1) * $pageSize;
        return $this->_db->join('left join xc_goods on xc_devicelist.goods_id = xc_goods.goods_id')
                ->where($conditions)->order('xc_devicelist.time desc')
                ->field('xc_devicelist.id,xc_devicelist.lane,xc_devicelist.deviceId,xc_goods.goods_name,xc_goods.shop_price,xc_goods.goods_img,xc_devicelist.alarm_sto,xc_devicelist.sto,xc_devicelist.max_sto,xc_devicelist.status,xc_devicelist.time')
                ->limit($offset,$pageSize)
                ->select();
    }

    public function getMachinelistCount($data = array()){
        $conditions = $data;
        if(isset($data['devicename']) && $data['devicename']) {
            $conditions['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['deviceid']) && $data['deviceid']){
            $conditions['deviceid'] = $data['deviceid'];
        }
        return $this->_db->where($conditions)->count();
    }


    /**
     * 通过id更新的状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function updateStatusById($id, $status) {
        if(!is_numeric($status)) {
            throw_exception("status不能为非数字");
        }
        if(!$id || !is_numeric($id)) {
            throw_exception("ID不合法");
        }
        $data['status'] = $status;
        return  $this->_db->where('id='.$id)->save($data); // 根据条件更新记录
    }



}
