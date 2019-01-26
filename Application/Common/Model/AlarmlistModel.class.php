<?php
namespace Common\Model;
use Think\Model;

/**
 * 测试数据操作
 * @author  victor
 */
class AlarmlistModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('devhistory_alarm');
	}
   
    public function getAlarmlistByDeviceId($deviceId='') {
        $res = $this->_db->where('deviceId="'.$deviceId.'"')->find();
        return $res;
    }
    public function getAlarmlistById($Id) {
        $res = $this->_db->where('log_id='."'$Id'")
                         ->find();
        return $res;
    }

    public function updateByAlarmlistId($id, $data) {
        if(!$id) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {            throw_exception('更新的数据不合法');
        }
        return $this->_db->where('log_id='."'$id'")->save($data); // 根据条件更新记录
    }

    public function insert($data = array()) {
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getAlarmlist($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['deviceid']) && $data['deviceid'])  {
            $conditions['deviceid'] = $data['deviceid'];
        }
        if(isset($data['begin_time']) && $data['begin_time']){
            $conditions['log_time'] = array('egt',$data['begin_time']);
        }
        if(isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('elt',$data['end_time']);
        }
        if(isset($data['begin_time']) && $data['begin_time'] && isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('between',array($data['begin_time'],$data['end_time']));
        }
        $offset = ($page - 1) * $pageSize;
        $res = $this->_db->where($conditions)->order('log_time desc')
                ->limit($offset,$pageSize)
                ->select();
        //dump($this->_db->getLastSql());die;
        return $res;
    }

    public function getAlarmlistCount($data = array()){
        $conditions = $data;
        if(isset($data['deviceid']) && $data['deviceid']){
            $conditions['deviceid'] = $data['deviceid'];
        }
        if(isset($data['begin_time']) && $data['begin_time']){
            $conditions['log_time'] = array('egt',$data['begin_time']);
        }
        if(isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('elt',$data['end_time']);
        }
        if(isset($data['begin_time']) && $data['begin_time'] && isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('between',array($data['begin_time'],$data['end_time']));
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
        return  $this->_db->where('log_id='.$id)->save($data); // 根据条件更新记录
    }



}
