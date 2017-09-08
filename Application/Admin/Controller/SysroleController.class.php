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
		$sysrole = M('sysrole')->select();
		if($sysrole){
			foreach($sysrole as $keys=>$item){
				$power_ids = M('role_permission')->where(array('role_id'=>$item['id']))->select();
				if($power_ids){
					foreach($power_ids as $item){
						$power_names = M('power')->find($item['permission_id']);
						$sysrole[$keys]['power_name'] .= $power_names['c_arlias'].'-'.$power_names['a_arlias'].'|';
					}
					$sysrole[$keys]['power_name'] = substr($sysrole[$keys]['power_name'],0,-1);
				}
			}
		}
		$this->assign('sysrole',$sysrole);
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
					'ids'=> array(),
				);
			}
			$return[$item['c_name']]['item'][$item['id']] = $item['a_arlias'];
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
			if((!$name) || (!$power_id)) {
				return show(0,'提交有误');
			}
			$role = array(
					'name' => $name
			);
			//添加操作日志
			$log = '新增权限角色'.$role['name'];
			$this->addOperLog($log);
			$role_id = M('sysrole')->add($role);
			//增加
			foreach($power_id as $k=>$v){
				$data = array(
						'role_id' => $role_id,
						'permission_id' => $v,
				);
				M('role_permission')->add($data);
			}
			show(1,'新增成功');
		}else{
			$power = M('power')->select(array('order'=>'id ASC'));
			$this->powers = $this->_find_parent($power);
			$this->display();
		}
	}

	public function update(){
		isset($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
		$data = M('sysrole')->where(array('id'=>$id))->find();
		$power_l = '';
		if(!$data){
			$this->error('修改用户信息有误');
		}
		$res = M('role_permission')->where(array('role_id'=>$id))->select();
		if($res){
			foreach($res as $key=>$item){
				$powers[$key] = M('power')->find($item['permission_id']);
			}
		}
		if($powers){
			foreach($powers as $item){
				$power_l .=$item['c_name'].'-'.$item['id'].'|';
			}
		}
		$data['power_id']=substr($power_l,0,-1);
		$this->data = $data;
		$this->cname = $aname = array();
		$this->cname=explode("|",$data['power_id']);
//		dump($this->cname);die;
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
//		dump($this->powers);die;
		$this->display();
	}


	public function updateRun(){
		isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
		$name = $_POST['name'];
		$power_id = $_POST['power_id'];
		$power_ids = implode(',',$power_id);
		$role_permssions = array();
		if((!$name) || (!$power_id)) {
			return show(0,'提交有误');
		}
		//减少
		$res = M('role_permission')->where(array('role_id'=>$id))->select();
		if($res){
			foreach($res as $key=>$item){
				if(!strstr($power_ids,$item['permission_id'])){
					$role_permssions[$key] = $item['id'];
				}
			}
		}
		if(!empty($role_permssions)){
			foreach($role_permssions as $item){
				M('role_permission')->delete($item);
			}
		}
		//增加
		foreach($power_id as $k=>$v){
			$condition['role_id'] = $id;
			$condition['permission_id'] = $v;
			$condition['_logic'] = 'and';
			$rt = M('role_permission')->where($condition)->find();
			if(empty($rt)){
				$data = array(
					'role_id' => $id,
					'permission_id' => $v,
				);
				M('role_permission')->add($data);
			}
		}
		//添加操作日志
		$log = '更新权限角色';
		$this->addOperLog($log);
		return show(1,'修改成功');
	}

	public function Del(){
		isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
		//添加操作日志
		$log = '删除权限角色'.$id;
		$this->addOperLog($log);
		$res = M('role_permission')->where(array('role_id'=>$id))->select();
		foreach($res as $item){
			M('role_permission')->delete($item['id']);
		}
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