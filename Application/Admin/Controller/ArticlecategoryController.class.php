<?php
/**
 * 后台菜单相关
 */

namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class ArticlecategoryController extends CommonController {
    
    public function add(){
        if($_POST) {
            if(!isset($_POST['name']) || !$_POST['name']) {
                return show(0,'分类栏目名称不能为空');
            }
            if(!isset($_POST['sort']) || !$_POST['sort']) {
                return show(0,'排序不能为空');
            }
            if(!isset($_POST['keywords']) || !$_POST['keywords']) {
                return show(0,'栏目关键词不能为空');
            }
            if(!isset($_POST['description']) || !$_POST['description']) {
                return show(0,'栏目描述不能为空');
            }
            $data = array(
                'name' => $_POST['name'],
                'pid' => $_POST['pid'],
                'sort' => $_POST['sort'],
                'keywords' => $_POST['keywords'],
                'description' => $_POST['description'],
                'banner' => $_POST['banner'],
                'show'=>$_POST['show'],
            );
            if(isset($data['description']) && $data['description']) {
                $data['description'] = htmlspecialchars($data['description']);
                if(get_magic_quotes_gpc())//如果get_magic_quotes_gpc()是打开的
                {
                    $data['description']=stripslashes($data['description']);//将字符串进行处理
                }
            }
            //添加操作日志
            $log = '新增分类'.$data['name'];
            $this->addOperLog($log);
            if(M('articlecate')->add($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            $this->pid = I('pid',0,'intval');
            $this->display();
        }
        //echo "welcome to singcms";
    }

    public function index() {
        $Category = new \Org\Util\Category();
        $cate = M('articlecate')->order('sort ASC')->select();
        $this->cate = $Category::unlimitedForLevel($cate,'&nbsp;&nbsp;--');
    	$this->display();
    }

    public function update(){
        isset($_GET['id']) ? $ArticlecategoryId = $_GET['id'] : $this->error('非法操作');
        $html = M('articlecate')->select($ArticlecategoryId);
        $this->html = $html[0];
        $this->display();
    }

    public function updateRun(){
        isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
        $data = array(
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'ename' => $_POST['ename'],
            'sort' => $_POST['sort'],
            'keywords' => $_POST['keywords'],
            'description' => $_POST['description'],
            'banner' => $_POST['banner'],
        );
        $articlecateId = $data['id'];
        unset($data['id']);
        if(get_magic_quotes_gpc())//如果get_magic_quotes_gpc()是打开的
        {
            $data['description']=stripslashes($data['description']);//将字符串进行处理
        }
        //添加操作日志
        $log = '修改分类'.$data['name'];
        $this->addOperLog($log);
        M('articlecate')->where('id='.$articlecateId)->save($data) ? show(1,'修改成功'):show(0,'修改失败');
    }


    public function listorder() {
        $listorder = $_POST['listorder'];
        $jumpUrl = $_SERVER['HTTP_REFERER'];
        $errors = array();
        if($listorder) {
            try {
                foreach ($listorder as $ArticlecategoryId => $v) {
                    // 执行更新
                    if(!$ArticlecategoryId || !is_numeric($ArticlecategoryId)) {
                        return show(0, 'ID不合法');
                    }

                    $data = array(
                        'sort' => intval($v),
                    );

                    $res = M('articlecate')->where('id='.$ArticlecategoryId)->save($data);
                    if ($res === false) {
                        $errors[] = $ArticlecategoryId;
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

    //删除
    public function delete(){
        try {
            if ($_POST) {
                isset($_POST['id']) ? $id = $_POST['id'] : $this->error('非法操作');
                // 执行数据更新操作
                $cate = M('articlecate')->select();
                $Category = new \Org\Util\Category();
                $son = $Category::getChildsId($cate,$id);
                if($son){
                    return show(0, '有子分类，不能被删除，请先删除子分类');
                }else{
                    //添加操作日志
                    $log = '删除分类ID'.$id;
                    $this->addOperLog($log);
                    if(M('articlecate')->delete($id)){
                        return show(1, '删除成功');
                    }else{
                        return show(1, '删除失败');
                    }
                }
            }
        }catch(Exception $e) {
            return show(0,$e->getMessage());
        }
        return show(0,'没有提交的数据');
    }


}