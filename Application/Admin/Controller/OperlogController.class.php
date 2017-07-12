<?php
/**
 * 操作日志控制器
 * */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class OperlogController extends CommonController{
	

    public function Index(){
		$conds = array();
		$begin_time = $_GET['begin_time'];
		if($begin_time) {
			$conds['begin_time'] = strtotime($begin_time);
		}
		$end_time = $_GET['end_time'];
		if($end_time) {
			$conds['end_time'] = strtotime($end_time);
		}
		$sword = $_GET['sword'];
		if($sword) {
			$conds['log_info'] = $sword;
		}
		$page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
		$pageSize = 10; 
		$Operlogs = D('Operlog')->getOperlogs($conds,$page,$pageSize);
		$count = D("Operlog")->getOperlogsCount($conds);
		$res  =  new \Think\Page($count,$pageSize);
		$pageres = $res->show();
		$this->assign('pageres',$pageres);
		$this->assign('Operlogs', $Operlogs);
		$this->display();
	}

	//删除
	public function Del(){
		try {
			if ($_POST) {
				!empty($_POST['id']) ? $log_id = $_POST['id'] : $this->error('非法操作');
				//添加操作日志
				$log = '删除日志'.$log_id;
				$this->addOperLog($log);
				// 执行数据更新操作
				if(M('admin_log')->delete($log_id)){
					return show(1, '删除成功');
				}else{
					return show(1, '删除失败');
				}
			}
		}catch(Exception $e) {
			return show(0,$e->getMessage());
		}
		return show(0,'没有提交的数据');
	}

}
