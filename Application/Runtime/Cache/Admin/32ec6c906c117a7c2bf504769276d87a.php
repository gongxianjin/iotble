<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo (C("WEB_NAME")); ?> - 后台管理系统 - 先讯物联(www.xcentiot.com)</title>
  <meta name="description" content="<?php echo (C("WEB_DESRIPTION")); ?>" />
  <meta name="keywords" content="<?php echo (C("WEB_KEYWORDS")); ?>" />
  <link rel="stylesheet" href="/Public/css/login.css" />
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <script type="text/javascript">
    var URL = '<?php echo U("admin/login/verify");?>&';
    bg = new Array(2); //设定图片数量，如果图片数为3，这个参数就设为2，依次类推
    bg[0] = '/Public/images/a.jpg'; //显示的图片路径，可用http://
    bg[1] = '/Public/images/b.jpg';
    bg[2] = '/Public/images/c.jpg';
    index = Math.floor(Math.random() * bg.length);
    document.write("<BODY BACKGROUND="+bg[index]+">");
  </script>
</head>
<body>
<a href="/" title="返回 -> <?php echo (C("WEB_NAME")); ?>"><div id="top"></div></a>
<div class="login">
  <form class="form-signin" enctype="multipart/form-data"  method="post" id="login">
    <table border="1" width="100%">
      <tr>
        <th>账号:</th>
        <td>
          <input type="username" name="username"  class="len250" />
        </td>
      </tr>
      <tr>
        <th>密码:</th>
        <td>
          <input type="password" class="len250" name="password" />
        </td>
      </tr>
      <tr>
        <th>验证:</th>
        <td>
          <input type="code" class="len250" name="code"/> <a href="javascript:void(login.change_code(this));"><img src="<?php echo U('admin/login/verify');?>" id="code"/> </a>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-left:104px;"> <input type="button" class="submit" value="登录" onclick="login.check()"/></td>
      </tr>
      <tr>
        <td colspan="2" style="padding-left:104px;color:#333;font-size:13px;line-height:24px;"> 技术支持：<a href="http://www.xcentiot.com" title="成都先讯物联" style="color:#333;" target="_blank">成都先讯物联 www.xcentiot.com</a>&nbsp;&nbsp;&nbsp;&nbsp;QQ：617699485</td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript" src="/Public/js/jquery.js"></script>
<script type="text/javascript" src="/Public/js/dialog/layer.js"></script>
<script type="text/javascript" src="/Public/js/dialog.js"></script>
<script type="text/javascript" src="/Public/js/admin/login.js"></script>
</body>
</html>