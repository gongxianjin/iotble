<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>成都先讯后台管理平台</title>
    <!-- Bootstrap Core CSS -->
    <link href="/Public/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/Public/css/sb-admin.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="/Public/css/plugins/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="/Public/css/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/Public/css/sing/common.css" />
    <link rel="stylesheet" href="/Public/css/party/bootstrap-switch.css" />
    <link rel="stylesheet" type="text/css" href="/Public/css/party/uploadify.css">

    <!-- jQuery -->
    <script src="/Public/js/jquery.js"></script>
    <script src="/Public/js/bootstrap.min.js"></script>
    <script src="/Public/js/dialog/layer.js"></script>
    <script src="/Public/js/dialog.js"></script>
    <script type="text/javascript" src="/Public/js/party/jquery.uploadify.js"></script>

</head>

    



<body>

<div id="wrapper">

    <?php
 $navs = D("Menu")->getAdminMenus(); $username = getLoginUsername(); $rolename = getRoleName($_SESSION['adminUser']['role_id']); foreach($navs as $k=>$v) { if($v['c'] == 'admin' && $rolename == '检测员' || $rolename == '观察员') { unset($navs[$k]); } } $index = 'index'; ?>
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <a class="navbar-brand" >成都先讯管理平台</a>
  </div>
  <!-- Top Menu Items -->
  <ul class="nav navbar-right top-nav">
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo getLoginUsername()?> <b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li>
          <a href="/admin.php?c=admin&a=personal"><i class="fa fa-fw fa-user"></i> 个人中心</a>
        </li>
        <li class="divider"></li>
        <li>
          <a href="/admin.php?c=login&a=loginout"><i class="fa fa-fw fa-power-off"></i> 退出</a>
        </li>
      </ul>
    </li>
  </ul>

  <ul class="nav navbar-left top-nav">
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-fw fa-bar-chart-o"></i>menu</a>
      <ul class="dropdown-menu">
      <?php if(is_array($navs)): $i = 0; $__LIST__ = $navs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$navo): $mod = ($i % 2 );++$i; if(checkOperModule($navo['c'])){ ?>
      <li <?php echo (getActive($navo["c"])); ?>>
        <a href="<?php echo (getAdminMenuUrl($navo)); ?>"><i class="fa fa-fw fa-bar-chart-o"></i> <?php echo ($navo["name"]); ?></a>
      </li>
      <?php } endforeach; endif; else: echo "" ;endif; ?>
      </ul>
    </li>

  </ul>

  <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
  <div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav side-nav nav_list">
      <li <?php echo (getActive($index)); ?>>
        <a href="/admin.php"><i class="fa fa-fw fa-dashboard"></i> 首页</a>
      </li>
      <?php if(is_array($navs)): $i = 0; $__LIST__ = $navs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$navo): $mod = ($i % 2 );++$i; if(checkOperModule($navo['c'])){ ?>
      <li <?php echo (getActive($navo["c"])); ?>>
        <a href="<?php echo (getAdminMenuUrl($navo)); ?>"><i class="fa fa-fw fa-bar-chart-o"></i> <?php echo ($navo["name"]); ?></a>
      </li>
      <?php } endforeach; endif; else: echo "" ;endif; ?>

    </ul>
  </div>
  <!-- /.navbar-collapse -->
</nav>


<script type="text/javascript">
  setInterval(function(){
    $.get('/admin.php?c=login&a=heart', '');
  }, 5000);
</script>

    <div id="page-wrapper">

    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="row">
            <div class="col-lg-12">

                <ol class="breadcrumb">
                    <li>
                        <i class="fa fa-dashboard"></i>  <a href="/admin.php?c=machine">分析仪管理</a>
                    </li>
                    <li class="active">
                        <i class="fa fa-table"></i>分析仪列表
                    </li>
                </ol>
            </div>
        </div>
        <!-- /.row -->
        <div style="margin:5px 1.4%;margin-top:-12px;">
            <button  id="button-add" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>添加 </button>
        </div>
        <div class="row">
            <form action="/admin.php" method="get">
                <div class="col-md-8">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-addon">内置分析仪ID</span>
                            <input class="form-control" name="deviceid" type="text" value="" placeholder="分析仪ID"/>
                        </div>
                    </div>
                    <input type="hidden" name="c" value="machine"/>
                    <input type="hidden" name="a" value="index"/>
                    <div class="input-group">
                        <input class="form-control" name="devicename" type="text" value="" placeholder="分析仪名称" />
                        <span class="input-group-btn">
                          <button id="sub_data" type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i></button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <h3></h3>
                <div class="table-responsive">
                    <form id="singcms-listorder">
                    <table class="table table-bordered table-hover singcms-table">
                        <thead>
                        <tr>
                            <th>内置分析仪ID</th>
                            <th>分析仪名称</th>
                            <th>测试员</th>
                            <th>单位</th>
                            <th>开始日期</th>
                            <th>结束日期</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($machines)): $i = 0; $__LIST__ = $machines;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                <td><?php echo ($vo["deviceid"]); ?></td>
                                <td><?php echo ($vo["devicename"]); ?></td>
                                <td><?php echo (getAdminName($vo["admin_id"])); ?></td>
                                <td><?php echo (getCompanyName($vo["company_id"])); ?></td>
                                <td><?php echo (date("Y-m-d",$vo["start_time"])); ?></td>
                                <td><?php echo (date("Y-m-d",$vo["end_time"])); ?></td>
                                <?php if($test_id != 0): ?><td><?php echo (devstatus($vo["status"])); ?></td>
                                    <?php else: ?>
                                    <td><span attr-status="<?php if($vo['status'] == 1): ?>0<?php else: ?>1<?php endif; ?>"  attr-id="<?php echo ($vo["id"]); ?>" class="sing_cursor singcms-on-off" id="singcms-on-off" ><?php echo (devstatus($vo["status"])); ?></span></td><?php endif; ?>
                                <td>
                                    <span class="glyphicon glyphicon-th-list" aria-hidden="true" id="singcms-list" attr-pid="<?php echo ($vo["deviceid"]); ?>"></span>
                                    <?php if($test_id == 0): ?><span class="sing_cursor glyphicon glyphicon-edit" aria-hidden="true" id="singcms-edit" attr-id="<?php echo ($vo["id"]); ?>" ></span><?php endif; ?>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <nav>
                        <ul >
                            <?php echo ($pageres); ?>
                        </ul>
                    </nav>
                    </form>

                </div>
            </div>

        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->

</div>
<!-- /#page-wrapper -->

</div>
<!-- /#wrapper -->
<!-- Morris Charts JavaScript -->
<script>
    var SCOPE = {
        'add_url' : '/admin.php?c=machine&a=add',
        'edit_url' : '/admin.php?c=machine&a=edit',
        'list_url' : '/admin.php?c=machine&a=machinelist&deviceid=',
        'set_status_url' : '/admin.php?c=machine&a=setStatus',
        'index_url' : '/',
    }
</script>

<script src="/Public/js/admin/common.js"></script>



</body>

</html>