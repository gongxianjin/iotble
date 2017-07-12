<?php
/**
 * 后台菜单相关
 */

namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class DevcategoryController extends CommonController {
    
    public function add(){
        if($_POST) {
            if(!isset($_POST['devicetypeName']) || !$_POST['devicetypeName']) {
                return show(0,'类型名称不能为空');
            }
            if(!isset($_POST['devicetypeDesc']) || !$_POST['devicetypeDesc']) {
                return show(0,'类型描述不能为空');
            }
            $data = array(
                'devicetypeId'   => create_uuid(),
                'devicetypeName' => $_POST['devicetypeName'],
                'devicetypeDesc' => $_POST['devicetypeDesc'],
            );
            //添加操作日志
            $log = '新增设备分类'.$data['devicetypeName'];
            $this->addOperLog($log);
            if(M('devicetype')->add($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            $this->display();
        }
        //echo "welcome to singcms";
    }

    public function index() {
        $this->cate = M('devicetype')->select();
    	$this->display();
    }

    public function update(){
        isset($_GET['id']) ? $DevcategoryId = $_GET['id'] : $this->error('非法操作');
        $devcate = M('devicetype')->select($DevcategoryId);
        $this->devcate = $devcate[0];
        $this->display();
    }

    public function updateRun(){
        isset($_POST['devicetypeid']) ? $devicetypeid = $_POST['devicetypeid'] : $this->error('非法操作');
        $data = array(
            'devicetypeid' => $_POST['devicetypeid'],
            'devicetypeName' => $_POST['devicetypeName'],
            'devicetypeDesc' => $_POST['devicetypeDesc'],
        );
        //添加操作日志
        $log = '更新设备分类'.$data['devicetypeName'];
        $this->addOperLog($log);
        $devicetypeid = $data['devicetypeid'];
        unset($data['devicetypeid']);
        M('devicetype')->where('devicetypeid='."'$devicetypeid'")->save($data) ? show(1,'修改成功'):show(0,'修改失败');
    }

    //删除
    public function del(){
        try {
            if ($_POST) {
                isset($_POST['id']) ? $devicetypeid = $_POST['id'] : $this->error('非法操作');
                //添加操作日志
                $log = '删除设备分类'.$devicetypeid;
                $this->addOperLog($log);
                // 执行数据更新操作
                if(M('devicetype')->delete($devicetypeid)){
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