<?php
/**
 * 权限管理
 * Enter description here ...
 * @author Administrator
 *
 */

namespace Admin\Controller;
use Think\Controller;

class SysroleController extends CommonController {
	
	/**
	 * 初始化方法
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $module
	 */
	public function __construct($id, $module=null){
		parent::__construct($id, $module);
	}

	/**
	 * 角色列表
	 * Enter description here ...
	 */
	public function Index(){
		//查询角色列表
		$this->sysrole = M('sysrole')->select();
		$this->display();
	} 
	
	/**
	 * 组装权限数据
	 * Enter description here ...
	 * @param unknown_type $ar
	 */
	public function _find_parent($ar) {
		$return = array();
		foreach($ar as $item){
			if(!isset($return[$item['c_name']])){
				$return[ $item['c_name'] ] = array(
					'c_arlias' => $item['c_arlias'],
					'c_name'=>$item['c_name'],
					'item' => array(),
				);
			}
			$return[$item['c_name']]['item'][$item['a_name']] = $item['a_arlias'];
		}
		
		return $return;
	}
	/**
	 * 添加角色
	 * Enter description here ...
	 */
	public function Add(){
		//找出所有权限
		if($_POST){
			$name = $_POST['name'];
			$power_id = $_POST['power_id'];
			$power_l='';
			foreach ($power_id as $k=>$power_i){
				$power_l .=$power_i.'|';
			}
			$power_l=substr($power_l,0,-1);
			if((!$name) || (!$power_l)) {
				return show(0,'提交有误');
			}
			$data = array(
					'name' => $name,
					'power_id' => $power_l,
			);
			//添加操作日志
			$log = '新增权限角色'.$data['name'];
			$this->addOperLog($log);
			M('sysrole')->add($data) ? show(1,'新增成功'):show(0,'新增失败');
		}else{
			$power = M('power')->select(array('order'=>'id ASC'));
			$this->powers = $this->_find_parent($power);
			$this->display();
		}
	}

	public function update(){
		isset($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
		$this->data = M('sysrole')->where(array('id'=>$id))->find();
		if(!$this->data){
			$this->error('修改用户信息有误');
		}
		$this->cname = $aname = array();
		$this->cname=explode("|",$this->data['power_id']);

		$return = array();
		foreach ($this->cname as $v){
			$ls_l = explode('-', $v);
			if(!isset($return[$ls_l[0]])){
				$return[ $ls_l[0] ] = array(

				);
			}
			$return[ $ls_l[0] ][$ls_l[1]] = $ls_l[1];
		}

		$power = M('power')->select(array('order'=>'id ASC'));
		$this->powers = $this->_find_parent($power);
		$this->display();
	}


	public function updateRun(){
		isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
		$name = $_POST['name'];
		$power_id = $_POST['power_id'];
		$power_l='';
		foreach ($power_id as $k=>$power_i){
			$power_l .=$power_i.'|';
		}
		$power_l=substr($power_l,0,-1);
		if((!$name) || (!$power_l)) {
			return show(0,'提交有误');
		}
		$data = array(
				'name' => $name,
				'power_id' => $power_l,
		);
		//添加操作日志
		$log = '更新权限角色'.$data['name'];
		$this->addOperLog($log);
		M('sysrole')->where('id='.$id)->save($data) ? show(1,'修改成功'):show(0,'修改失败');
	}

	public function Del(){
		isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
		//添加操作日志
		$log = '删除权限角色'.$id;
		$this->addOperLog($log);
		// 执行数据更新操作
		if(M('sysrole')->delete($id)){
			return show(1, '删除成功');
		}else{
			return show(1, '删除失败');
		}
	}

	
	/**
	 * 导入基本权限
	 * Enter description here ...
	 */
	public function Initrole(){
		//清空权限表
		$sql = 'truncate xc_power';
		M()->execute($sql);
		foreach ($this->oper_meta_set as $key=>$val){
			foreach ($val['items'] as $k=>$item){
				$data = array(
					'c_name'=>$key,
					'c_arlias'=>$val['arlias'],
					'a_name'=>$k,
					'a_arlias'=>$item,
				);
				M('power')->add($data);
			}
		}
		header('Content-Type:text/html;charset=UTF-8');
		$this->redirect('sysrole/index');
		exit;
	}
}