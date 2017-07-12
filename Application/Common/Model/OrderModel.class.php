<?php
namespace Common\Model;
use Think\Model;

/**
 * 用户组操作
 * @author  victor
 */
class OrderModel extends Model {
	private $_db = '';

	public function __construct() {
		$this->_db = M('order_info');
	}
   
    public function getOrdersByOrdersn($ordersn='') {
        $res = $this->_db->where('order_sn="'.$ordersn.'"')->find();
        return $res;
    }
    public function getOrdersById($Id) {
        $res = $this->_db->where('order_id='."'$Id'")
                         ->find();
        return $res;
    }

    public function updateByOrderId($id, $data) {
        if(!$id || !is_numeric($id)) {
            throw_exception("ID不合法");
        }
        if(!$data || !is_array($data)) {
            throw_exception('更新的数据不合法');
        }
        return $this->_db->where('order_id='.$id)->save($data); // 根据条件更新记录
    }

    public function insert($data = array()){
        if(!$data || !is_array($data)) {
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getOrders($data,$page,$pageSize=10) {
        $conditions = $data;
        if(isset($data['order_sn']) && $data['order_sn'])  {
            $conditions['order_sn'] = array('like','%'.$data['order_sn'].'%');
        }
        $offset = ($page - 1) * $pageSize;
        return $this->_db->join('left join xc_deviceinfo on xc_deviceinfo.id = xc_order_info.dev_id')
            ->join('left join xc_order_goods on xc_order_goods.order_id = xc_order_info.order_id')
            ->where($conditions)->order('xc_order_info.order_id desc')
            ->field('xc_order_info.order_id,xc_order_info.order_sn,xc_order_info.dev_id,xc_deviceinfo.devicename,xc_order_goods.goods_name,xc_order_info.pay_name,xc_order_info.order_amount,xc_order_info.add_time,xc_order_info.order_status')
            ->limit($offset,$pageSize)
            ->select();
    }

    public function getOrdersCount($data = array()){
        $conditions = $data;
        if(isset($data['order_sn']) && $data['order_sn']) {
            $conditions['order_sn'] = array('like','%'.$data['order_sn'].'%');
        }
        return $this->_db->where($conditions)->count();
    }


}
