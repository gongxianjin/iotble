<?php
/**
 * 后台推荐位相关
 */
namespace Admin\Controller;
use Think\Controller;
class ChainlistController extends CommonController {
    public function index()
    {
        $data['status'] = array('neq',-1);
        $chainlists = D("Chainlist")->select($data);
        $this->assign('chainlists',$chainlists);
        $this->assign('nav','加盟招商管理');
        $this->display();
    }
    /**
     * 设置状态
     * status=1 正常 0关闭 -1删除
     */
    public function setStatus(){
        try {
            if ($_POST) {
                $id = $_POST['id'];
                $status = $_POST['status'];
                $res = D("Chainlist")->updateStatusById($id, $status);
                if ($res) {
                    return show(1, '操作成功');
                } else {
                    return show(0, '操作失败');
                }
            }
        }catch (Exception $e) {
            return show(0, $e->getMessage());
        }

        return show(0, '没有提交的内容');
    }
}