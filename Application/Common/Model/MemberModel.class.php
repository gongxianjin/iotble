<?php
namespace Common\Model;
use Think\Model;

/**
 * 用户组操作
 * @author  victor
 */
class MemberModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('user');
	}
   
    public function getMemberByUsername($username='') {
        $res = $this->_db->where('username="'.$username.'"')->find();
        return $res;
    }
    public function getMemberByUserId($userId=0) {
        $res = $this->_db->join('left join xc_user_role on xc_user_role.user_id = xc_user.user_id')
                         ->where('xc_user.user_id='.$userId)
                         ->field('xc_user.user_id,xc_user.username,xc_user.phone,xc_user.status,xc_user_role.role_id,xc_user.reg_time,xc_user.last_login')
                         ->find();
        return $res;
    }

    public function updateByUserId($id, $data) {
        if(!$id || !is_numeric($id)) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {
            throw_exception('更新的数据不合法');
        }
        M('user_role')->where('user_id='.$id)->save(array('role_id'=>$data['role_id']));
        //更新角色关联表
        unset($data['role_id']);
        return $this->_db->where('user_id='.$id)->save($data); // 根据条件更新记录
    }

    public function insert($data = array()) {
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getMember($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['username']) && $data['username'])  {
            $conditions['username'] = array('like','%'.$data['username'].'%');
        }
        $conditions['status'] = array('neq',-1);
        $offset = ($page - 1) * $pageSize;
        return $this->_db->join('left join xc_user_role on xc_user_role.user_id = xc_user.user_id')
                ->where($conditions)->order('xc_user.user_id desc')
                ->field('xc_user.user_id,xc_user.username,xc_user.phone,xc_user.status,xc_user_role.role_id,xc_user.reg_time,xc_user.last_login')
                ->limit($offset,$pageSize)
                ->select();
    }

    public function getMemberCount($data = array()){
        $conditions = $data;
        if(isset($data['username']) && $data['username']) {
            $conditions['username'] = array('like','%'.$data['username'].'%');
        }
        $conditions['status'] = array('neq',-1);
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
        return  $this->_db->where('user_id='.$id)->save($data); // 根据条件更新记录
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
