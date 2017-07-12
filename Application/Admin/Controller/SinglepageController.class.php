<?php


namespace Admin\Controller;
use Think\Controller;

class SinglepageController extends CommonController{

	public function index(){
		$conditions['status'] = array('neq',-1);
		$this->singlepage = M('singlepage')->where($conditions)->order('sort ASC')->select();
		$this->display();
	}

	public function add(){
		if($_POST) {

			if(!isset($_POST['title']) || !$_POST['title']) {
				return show(0,'文档标题不存在');
			}
			if(!isset($_POST['etitle']) || !$_POST['etitle']) {
				return show(0,'英文标题不存在');
			}
			if(!isset($_POST['filename']) || !$_POST['filename']) {
				return show(0,'文件名不存在');
			}
			if(!isset($_POST['banner']) || !$_POST['banner']) {
				return show(0,'Banner不存在');
			}
			if(!isset($_POST['content']) || !$_POST['content']) {
				return show(0,'content不存在');
			}
			$data = array(
					'title' => $_POST['title'],
					'etitle' => $_POST['etitle'],
					'filename' => $_POST['filename'],
					'content' => $_POST['content'],
					'banner' => $_POST['banner'],
					'sort' => 0,
					'time' => time()
			);
			if(isset($data['content']) && $data['content']) {
				$data['content'] = htmlspecialchars($data['content']);
				if(get_magic_quotes_gpc())//如果get_magic_quotes_gpc()是打开的
				{
					$data['content']=stripslashes($data['content']);//将字符串进行处理
				}
			}
			//添加操作日志
			$log = '新增文章'.$data['title'];
			$this->addOperLog($log);
			M('singlepage')->add($data) ? show(1,'新增成功'):show(0,'新增失败');

		}else {
			$this->display();
		}
	}	

	public function update(){
		isset($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
		$this->singlepage = M('singlepage')->where(array('id'=>$id))->select();
		$this->display();
	}

	public function updateRun(){
		isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
		$data = array(
			'id' => $_POST['id'],
			'title' => $_POST['title'],
			'etitle' => $_POST['etitle'],
			'type' => $_POST['type'],
			'sort' => $_POST['sort'],
			'filename' => $_POST['filename'],
			'content' => $_POST['content'],
			'templates' => $_POST['templates'],
			'banner' =>  $_POST['banner'],
			'time' => time()
			);
		$singlepageId = $data['id'];
		unset($data['id']);
		if(get_magic_quotes_gpc())//如果get_magic_quotes_gpc()是打开的
		{
			$data['content']=stripslashes($data['content']);//将字符串进行处理
		}
		//添加操作日志
		$log = '修改文章'.$data['title'];
		$this->addOperLog($log);
		M('singlepage')->where('id='.$singlepageId)->save($data) ? show(1,'修改成功'):show(0,'修改失败');
	}


	public function setStatus(){
		try {
			if ($_POST) {
				$id = $_POST['id'];
				$status = $_POST['status'];
				if (!$id) {
					return show(0, 'ID不存在');
				}
				if(!is_numeric($status)) {
					throw_exception('status不能为非数字');
				}
				if(!$id || !is_numeric($id)) {
					throw_exception('id不合法');
				}
				$data['status'] = $status;

				$res = M('singlepage')->where('id='.$id)->save($data);

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


	public function listorder() {
		$listorder = $_POST['listorder'];
		$jumpUrl = $_SERVER['HTTP_REFERER'];
		$errors = array();
		if($listorder) {
			try {
				foreach ($listorder as $singlepageId => $v) {
					// 执行更新
					if(!$singlepageId || !is_numeric($singlepageId)) {
						return show(0, 'ID不合法');
					}

					$data = array(
							'sort' => intval($v),
					);

					$res = M('singlepage')->where('id='.$singlepageId)->save($data);
					if ($res === false) {
						$errors[] = $singlepageId;
					}

				}
			}catch(Exception $e) {
				return show(0,$e->getMessage(),array('jump_url'=>$jumpUrl));
			}
			if($errors) {
				return show(0,'排序失败-'.implode(',',$errors),array('jump_url'=>$jumpUrl));
			}
			return show(1,'排序成功',array('jump_url'=>$jumpUrl));
		}

		return show(0,'排序数据失败',array('jump_url'=>$jumpUrl));
	}



}