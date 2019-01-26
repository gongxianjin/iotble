<?php
namespace Common\Model;
use Think\Model;

/**
 * 用户组操作
 * @author  victor
 */
class AdminModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('admin');
	}
   
    public function getAdminByUsername($username='') {
        $res = $this->_db->where('username="'.$username.'"')->find();
        return $res;
    }

    public function getAdminByPhone($phone='') {
        $res = $this->_db->where('phone="'.$phone.'"')->find();
        return $res;
    }

    public function getAdminByAdminId($adminId=0) {
        $res = $this->_db->where('admin_id='.$adminId)->find();
        return $res;
    }

    public function updateByAdminId($id, $data) {
        if(!$id || !is_numeric($id)) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {
            throw_exception('更新的数据不合法');
        }
        return  $this->_db->where('admin_id='.$id)->save($data); // 根据条件更新记录
    }

    public function insert($data = array()) {
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getAdmins($data,$page,$pageSize=10) {
        $conditions = array();
        if(isset($data['company_id']) && $data['company_id']){
            $conditions = array(
                'status' => array('neq',-1),
                'company_id' => array('eq',$data['company_id']),
            );
        }else{
            $conditions = array(
                'status' => array('neq',-1),
            );
        }
        if(isset($data['username']) && $data['username'])  {
            $conditions['username'] = array('like','%'.$data['username'].'%');
        }
        $offset = ($page - 1) * $pageSize;
        return $this->_db->where($conditions)->order('admin_id desc')
            ->limit($offset,$pageSize)
            ->select();
    }

    public function getAdminsCount($data){
        $conditions = array();
        if(isset($data['company_id']) && $data['company_id']){
            $conditions = array(
                'status' => array('neq',-1),
                'company_id' => array('eq',$data['company_id']),
            );
        }else{
            $conditions = array(
                'status' => array('neq',-1),
            );
        }
        if(isset($data['username']) && $data['username'])  {
            $conditions['username'] = array('like','%'.$data['username'].'%');
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
        return  $this->_db->where('admin_id='.$id)->save($data); // 根据条件更新记录

    }

    public function getLastLoginUsers() {
        $time = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $data = array(
            'status' => 1,
            'lastlogintime' => array("gt",$time),
        );

        $res = $this->_db->where($data)->count();
        return $res['tp_count'];
    }


}
