<?php
namespace Common\Model;
use Think\Model;

/**
 * 用户组操作
 * @author  victor
 */
class MachineModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('deviceinfo');
	}
   
    public function getMachineByDevicename($devicename='') {
        $res = $this->_db->where('devicename="'.$devicename.'"')->find();
        return $res;
    }
    public function getMachineById($Id) {
        $res = $this->_db->where('xc_deviceinfo.id='."'$Id'")
                         ->find();
        return $res;
    }

    public function updateByDevId($id, $data) {
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

    public function getMachine($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['devicename']) && $data['devicename'])  {
            $conditions['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['id']) && $data['id'])  {
            $conditions['id'] = $data['id'];
        }
        $devicetype = $_GET['devicetype'];
        if($devicetype) {
            $conds['devicetype'] = $devicetype;
        }
        $offset = ($page - 1) * $pageSize;
        return $this->_db->join('left join xc_devicetype on xc_devicetype.devicetypeId = xc_deviceinfo.devicetype')
                ->where($conditions)->order('xc_deviceinfo.devtime desc')
                ->field('xc_deviceinfo.id,xc_deviceinfo.devicename,xc_devicetype.devicetypeName,xc_deviceinfo.vol_m,xc_deviceinfo.inv,xc_deviceinfo.rssi,xc_deviceinfo.devtime,xc_deviceinfo.devstatus')
                ->limit($offset,$pageSize)
                ->select();
    }

    public function getMachineCount($data = array()){
        $conditions = $data;
        if(isset($data['devicename']) && $data['devicename']) {
            $conditions['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['id']) && $data['id'])  {
            $conditions['id'] = $data['id'];
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
