<?php
namespace Common\Model;
use Think\Model;

/**
 * 用户组操作
 * @author  victor
 */
class GoodsModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('goods');
	}
   
    public function getGoodsByGoodsname($goods_name='') {
        $res = $this->_db->where('goods_name="'.$goods_name.'"')->find();
        return $res;
    }
    public function getGoodsById($Id) {
        $res = $this->_db->where('goods_id='."'$Id'")
                         ->find();
        return $res;
    }

    public function updateByGoodsId($id, $data) {
        if(!$id || !is_numeric($id)) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {
            throw_exception('更新的数据不合法');
        }
        return $this->_db->where('goods_id='.$id)->save($data); // 根据条件更新记录
    }

    public function insert($data = array()){
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getGoods($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['goods_name']) && $data['goods_name'])  {
            $conditions['goods_name'] = array('like','%'.$data['goods_name'].'%');
        }
        $offset = ($page - 1) * $pageSize;
        return $this->_db->where($conditions)->order('goods_id desc')
                ->limit($offset,$pageSize)
                ->select();
    }

    public function getGoodsCount($data = array()){
        $conditions = $data;
        if(isset($data['goods_name']) && $data['goods_name']) {
            $conditions['goods_name'] = array('like','%'.$data['goods_name'].'%');
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
