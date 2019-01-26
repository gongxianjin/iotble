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
		$this->_db = M('devicelist');
	}
   
    public function getMachineByDevicename($devicename='') {
        $res = $this->_db->where('devicename="'.$devicename.'"')->find();
        return $res;
    }
    public function getMachineById($Id) {
        $res = $this->_db->where('xc_devicelist.id='."'$Id'")
                         ->find();
        return $res;
    }

    public function getMachineBydeviceId($deviceid) {
        $res = $this->_db->where('xc_devicelist.deviceid='."'$deviceid'")
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
        $conditions = array();
        $where = array();
        if(isset($data['devicename']) && $data['devicename'])  {
            $where['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['deviceid']) && $data['deviceid'])  {
            $where['deviceid'] =  array('like','%'.$data['deviceid'].'%'); 
        }
        if(!empty($where)){
          $where['_logic'] = 'or';
          $conditions['_complex'] = $where;
	    }
        if(isset($data['company_id']) && $data['company_id'])  {
            $conditions['company_id'] = $data['company_id'];
        }
        if(isset($data['admin_id']) && $data['admin_id'])  {
            $conditions['admin_id'] = $data['admin_id'];
        }
        $offset = ($page - 1) * $pageSize; 
        $res = $this->_db->where($conditions)->order('xc_devicelist.id desc')
            ->limit($offset,$pageSize)
            ->select();
//        dump($this->_db->getLastSql());die;
        return $res;
    }

    public function getMachineCount($data = array()){
        $conditions = array();
        $where = array();
        if(isset($data['devicename']) && $data['devicename']) {
            $where['devicename'] = array('like','%'.$data['devicename'].'%');
        }
        if(isset($data['deviceid']) && $data['deviceid'])  {
            $where['deviceid'] =  array('like','%'.$data['deviceid'].'%'); 
        }
        if(!empty($where)){
            $where['_logic'] = 'or';
            $conditions['_complex'] = $where;
        }
        if(isset($data['company_id']) && $data['company_id'])  {
            $conditions['company_id'] = $data['company_id'];
        }
        if(isset($data['admin_id']) && $data['admin_id'])  {
            $conditions['admin_id'] = $data['admin_id'];
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
