<?php
namespace Common\Model;
use Think\Model;

/**
 * 日志组操作
 * @author  victor
 */
class OperlogModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('admin_log al');
	}

    public function getOperlogBysword($sword='') {
        if($sword)  {
            $conditions['log_info'] = array('like','%'.$sword.'%');
        }
        $res = $this->_db->where($conditions)->find();
        return $res;
    }
    public function getOperlogById($Id) {
        $res = $this->_db->where('log_id='."'$Id'")
                         ->find();
        return $res;
    }

    public function getOperlogs($data,$page,$pageSize=10) {

        if(isset($data['log_info']) && $data['log_info']) {
            $conditions['log_info'] = array('like','%'.$data['log_info'].'%');
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
        $res = $this->_db->join('left join xc_admin am on am.admin_id = al.user_id')
            ->where($conditions)
            ->order('al.log_id desc')
            ->limit($offset,$pageSize)
            ->select();
        //dump($this->_db->getLastSql());die;
        return $res;

    }

    public function getOperlogsCount($data = array()){
        $conditions = $data;
        if(isset($data['log_info']) && $data['log_info']) {
            $conditions['log_info'] = array('like','%'.$data['log_info'].'%');
        }
        if(isset($data['begin_time']) && $data['begin_time']){
            $conditions['log_time'] = array('egt',"$data[begin_time]");
        }
        if(isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('elt',"$data[end_time]");
        }
        if(isset($data['begin_time']) && $data['begin_time'] && isset($data['end_time']) && $data['end_time']){
            $conditions['log_time'] = array('between',"$data[begin_time],$data[end_time]");
        }
        return $this->_db->where($conditions)->count();
    }


}
