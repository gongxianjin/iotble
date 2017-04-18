<?php
namespace Common\Model;
use Think\Model;

/**
 * 上传图片类
 * @author  singwa
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

}
