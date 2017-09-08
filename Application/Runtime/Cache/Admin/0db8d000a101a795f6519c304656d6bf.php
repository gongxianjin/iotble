<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
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
	<script src="/Public/js/kindeditor/kindeditor-all.js"></script>
	<div id="page-wrapper">

		<div class="container-fluid">

			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">

					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i>  <a href="/admin.php?c=singlepage">权限管理</a>
						</li>
						<li class="active">
							<i class="fa fa-edit"></i> 角色编辑
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-6">

					<form class="form-horizontal" id="singcms-form">

						<div class="form-group">
							<label for="inputname" class="col-sm-2 control-label">角色名称:</label>
							<div class="col-sm-5">
								<input type="text" name="name" class="form-control" id="inputname" value="<?php echo ($data["name"]); ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">权限信息:</label>
							<div class="col-sm-10">
								<div class="checkbox">
									<?php if(is_array($powers)): $k = 0; $__LIST__ = $powers;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><p style="background:#eee;"><?php echo ($vo["c_arlias"]); ?>:</p>
										<div style="line-height:24px;padding: 10px 0 20px 16px;">　
											<?php if(is_array($vo['item'])): $i = 0; $__LIST__ = $vo['item'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><label style="display:inline-block;width:120px;">
													<input type="checkbox"  <?php if(in_array($vo["c_name"].'-'.$key, $cname)){ ?>checked="checked"<?php } ?> value="<?php echo ($key); ?>" name="power_id[<?php echo ($key); ?>]"><?php echo ($v); ?>
												</label><?php endforeach; endif; else: echo "" ;endif; ?>
										</div><?php endforeach; endif; else: echo "" ;endif; ?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<input type="hidden" value="<?php echo ($data["id"]); ?>" name="id"/>
								<button type="button" class="btn btn-default" id="singcms-button-submit">提交</button>
							</div>
						</div>

					</form>
				</div>
			</div>
			<!-- /.row -->

		</div>
		<!-- /.container-fluid -->

	</div>
	<!-- /#page-wrapper -->

</div>
<script>
	var SCOPE = {
		'save_url' : '/admin.php?c=sysrole&a=updateRun',
		'jump_url' : '/admin.php?c=sysrole',
	};

	$(function(){
		$('.checkbox input[type=checkbox]').change(function(){
			if($(this).parent().index()==0){
				if(this.checked){
					$(this).parent().parent().find('input[type=checkbox]:not(:eq(0))').prop("checked", this.checked);
				}else{
					$(this).parent().parent().find('input[type=checkbox]:not(:eq(0))').prop("checked", this.checked);
				}
			}else{
				if(this.checked){
					$(this).parent().parent().find('label:eq(0) input[type=checkbox]').prop("checked", this.checked);
				}
			}
		});
	});
</script>
<script src="/Public/js/admin/common.js"></script>



</body>

</html>