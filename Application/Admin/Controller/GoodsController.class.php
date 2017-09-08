<?php
/**
 * 后台Index相关
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class GoodsController extends CommonController {

    public function index() {
        $conds = array();
        $goods_name = $_GET['goods_name'];
        if($goods_name) {
            $conds['goods_name'] = $goods_name;
        }

        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = 10;

        $goods = D('Goods')->getGoods($conds,$page,$pageSize);
        $count = D("Goods")->getGoodsCount($conds);
        $res  =  new \Think\Page($count,$pageSize);
        $pageres = $res->show();

        $this->assign('pageres',$pageres);
        $this->assign('goods', $goods);
        $this->display();
    }

    public function update() {
        !empty($_GET['id']) ? $id = $_GET['id'] : $this->error('非法操作');
        $goods = D("Goods")->getGoodsById($id);
        $this->assign('vo',$goods);
        $this->display();
    }

    public function save() {
        $goods_id = $_POST['goods_id'];
        if(!$goods_id){
            $this->error('非法操作');
        }
        $data['goods_name'] = $_POST['goods_name'];
        $data['shop_price'] = $_POST['shop_price'];
        $data['market_price'] = $_POST['shop_price'];
        $data['goods_img'] = $_POST['goods_img'];
        //生成缩略图
        $img = new \Think\Image();
        //打开图片
        $img->open(str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))).'/'.$data['goods_img']);
        //生成
        $img->thumb(200,300);
        //保存
        $data['goods_thumb'] = str_replace('.JPG','_small.JPG',$data['goods_img']);
        $img->save(str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))).'/'.$data['goods_thumb']);
        //添加操作日志
        $log = '编辑商品'.$data['goods_name'];
        $this->addOperLog($log);
        try {
            $id = D("Goods")->updateByGoodsId($goods_id, $data);
            if($id === false) {
                return show(0, '更新失败');
            }
            return show(1, '更新成功');
        }catch(Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    public function add(){
        if($_POST) {
            if(!isset($_POST['goods_name']) || !$_POST['goods_name']) {
                return show(0,'商品名称不能为空');
            }
            if(!isset($_POST['shop_price']) || !$_POST['shop_price']) {
                return show(0,'商品价格不能为空');
            }
            $data = array(
                'goods_name' => $_POST['goods_name'],
                'shop_price' => $_POST['shop_price'],
                'market_price' => $_POST['shop_price'],
                'goods_img' => $_POST['goods_img'],
                'add_time' => time()
            );
            //生成缩略图
            $img = new \Think\Image();
            //打开图片
            $img->open(str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))).'/'.$data['goods_img']);
            //生成
            $img->thumb(200,300);
            //保存
            $data['goods_thumb'] = str_replace('.JPG','_small.JPG',$data['goods_img']);
            $img->save(str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))).'/'.$data['goods_thumb']);
            //添加操作日志
            $log = '新增商品'.$data['goods_name'];
            $this->addOperLog($log);
            if(M('goods')->add($data)){
                show(1,'新增成功');
            }else{
                show(0,'新增失败');
            }
        }else{
            $this->display();
        }
        //echo "welcome to singcms";
    }

}