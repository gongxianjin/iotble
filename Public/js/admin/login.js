/***
  前端登录业务类
  @author yanbin
***/
var login = {
    change_code:function(obj){
        $("#code").attr("src",URL+Math.random());
        return false;
    },
    check : function() {
        // 获取登录页面中的用户名 和 密码
        var username = $('input[name="username"]').val();
        var password = $('input[name="password"]').val();
        var code = $('input[name="code"]').val();

        if(!username) {
            dialog.error('用户名不能为空');
        }
        if(!password) {
            dialog.error('密码不能为空');
        }
        if(!code){
            dialog.error('验证码不能为空');
        }

        var url = "/admin.php?c=login&a=check";
        var data = {'username':username,'password':password,'code':code};
        // 执行异步请求  $.post
        $.post(url,data,function(result){
            if(result.status == 0) {
                return dialog.error(result.message);
            }
            if(result.status == 1) {
                return dialog.success(result.message, '/admin.php?c=index');
            }

        },'JSON');

    }
}