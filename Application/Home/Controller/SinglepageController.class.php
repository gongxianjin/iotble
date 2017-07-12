<?php

namespace Home\Controller;
use Think\Controller;
class SinglepageController extends CommonController{
	//视图
	public function index(){
		$filename = $_GET['filename'];
		$this->filename = $filename;
		$where = array('filename'=>$filename);
		$singlepage = M('singlepage')->where($where)->find();
		$this->singlepage = $singlepage;
		$type = $singlepage['type'];
		$templates = $singlepage['templates'];
		$this->pagelist = M('singlepage')->where(array('type'=>$type))->order('sort asc')->select();

//		$this->cases = M('casescate')->where('pid=0')->order('id ASC')->select();
//		$this->research = M('researchcate')->where('pid=0')->order('id ASC')->select();
//		$this->team = M('teamcate')->where('pid=0')->order('id ASC')->select();
//		$this->article = M('articlecate')->where('pid=0')->order('id ASC')->select();
		$this->single = M('singlepage')->order('sort ASC')->select();


		if (empty($templates)) {
			$this->display();
		}else{
			$this->display($templates);
		}
	}
}
?>