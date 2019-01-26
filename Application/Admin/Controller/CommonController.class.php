<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Auth;

/**
 * use Common\Model 这块可以不需要使用，框架默认会加载里面的内容
 */
class CommonController extends Controller {

	//基本权限配置
	protected $oper_meta_set = array(

		//菜单管理
		'menu'=>array(
				'arlias'=>'菜单管理',
				'items'=>array(
						'index'=>'菜单列表','add'=>'添加菜单', 'edit'=>'编辑菜单','setStatus'=>'删除菜单',
				),
		),

		//权限管理
		'sysrole'=>array(
				'arlias'=>'权限管理',
				'items'=>array(
						'index'=>'管理列表', 'add'=>'添加角色', 'update'=>'编辑角色','del'=>'删除角色', 'initrole'=>'导入权限',
				),
		),

		//用户管理
		'admin'=>array(
				'arlias'=>'用户管理',
				'items'=>array(
						'index'=>'用户列表', 'add'=>'添加用户', 'personal'=>'配置用户','setStatus'=>'删除用户','edit'=>'编辑用户',
				),
		),

		//单位管理
		'company'=>array(
				'arlias'=>'单位管理',
				'items'=>array(
						'index'=>'单位列表', 'add'=>'添加单位', 'update'=>'编辑单位','del'=>'删除单位'
				),
		),

		//分析仪管理
		'machine'=>array(
				'arlias'=>'分析仪管理',
				'items'=>array(
						'index'=>'分析仪列表','add'=>'添加分析仪','edit'=>'编辑分析仪','set'=>'设置分析仪','machinelist'=>'预警列表','setStatus'=>'停用分析仪','additem'=>'添加测试项目','itemlist'=>'测试数据列表',
				),
		),

		//地点管理
		'pondaddr'=>array(
				'arlias'=>'地点管理',
				'items'=>array(
						'index'=>'地址列表', 'add'=>'添加地址', 'update'=>'编辑地址','del'=>'删除地址'
				),
		),

		//系统设置
		'basic'=>array(
				'arlias'=>'基本设置',
				'items'=>array(
						'index'=>'基本配置','add'=>'修改配置','cache'=>'缓存配置',
				),
		),

		//操作日志管理
		'operlog'=>array(
				'arlias'=>'操作日志管理',
				'items'=>array(
						'index'=>'操作日志列表', 'del'=>'删除操作日志',
				),
		),

		//数据统计管理
		'datastatic'=>array(
				'arlias'=>'数据统计管理',
				'items'=>array(
						'index'=>'数据统计列表'
				),
		),

	);

	public function __construct() {
		parent::__construct();
		$this->_init();
	}
	/**
	 * 初始化
	 * @return
	 */
	private function _init() {
		// 如果已经登录
		$isLogin = $this->isLogin();
		if(!$isLogin) {
			// 跳转到登录页面
			$this->redirect('/admin.php?c=login');
//			$this->redirect('admin/login/index');
//			$this->redirect('/admin.php/login');
		}
		//检查权限
		if(!checkOperModule(CONTROLLER_NAME,ACTION_NAME)){
			$this->error('权限不足');die;
		}
	}

	/**
	 * 获取登录用户信息
	 * @return array
	 */
	public function getLoginUser() {
		return session("adminUser");
	}

	/**
	 * 判定是否登录
	 * @return boolean 
	 */
	public function isLogin() {
		$user = $this->getLoginUser();
		if($user && is_array($user)) {
			return true;
		}

		return false;
	}

	public function setStatus($data, $models) {
		try {
			if ($_POST) {
				$id = $data['id'];
				$status = $data['status'];
				if (!$id) {
					return show(0, 'ID不存在');
				}
				$res = D($models)->updateStatusById($id, $status);
				if ($res) {
					return show(1, '操作成功');
				} else {
					return show(0, '操作失败');
				}
			}
			return show(0, '没有提交的内容');
		}catch(Exception $e) {
			return show(0, $e->getMessage());
		}
	}

	public function listorder($model='') {
		$listorder = $_POST['listorder'];
		$jumpUrl = $_SERVER['HTTP_REFERER'];
		$errors = array();
		try {
			if ($listorder) {
				foreach ($listorder as $id => $v) {
					// 执行更新
					$id = D($model)->updateListorderById($id, $v);
					if ($id === false) {
						$errors[] = $id;
					}
				}
				if ($errors) {
					return show(0, '排序失败-' . implode(',', $errors), array('jump_url' => $jumpUrl));
				}
				return show(1, '排序成功', array('jump_url' => $jumpUrl));
			}
		}catch (Exception $e) {
			return show(0, $e->getMessage());
		}
		return show(0,'排序数据失败',array('jump_url' => $jumpUrl));
	}

	// 添加操作日志记录
	protected function addOperLog($log, $dubug=false){

		$adminuser = $this->getLoginUser();
		if(!$log) return;

		$data = array(
			'log_time' => time(),
			'user_id'  => $adminuser['admin_id'],
			'log_info' => $log,
			'ip_address' => get_client_ip(),
		);

		// 执行数据更新操作
		if(!M('admin_log')->add($data) && $dubug){
			dump(M('admin_log')->getError());
		}

	}



}