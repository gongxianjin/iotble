<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin','AppAPI'),
    'DEFAULT_MODULE'       =>    'Home',  // 默认模块

    //URL地址不区分大小写
    'URL_CASE_INSENSITIVE' =>true,
    'URL_MODEL'=>1,
    'LOAD_EXT_CONFIG' => 'db', //如需创建新的数据库配置文件，必须在此声明
    'MD5_PRE'=>'xcent_cms',
    'HTML_FILE_SUFFIX' => '.html',

    'verify_length' => '1',
    'VERIFY_WIDTH' => '160',
    'VERIFY_HEIGHT' => '36',
    'VERIFY_BGCOLOR' => '#F3FBFE',
    'VERIFY_SEED' => '3456789aAbBcCdDeEfFgGhHjJkKmMnNpPqQrRsStTuUvVwWxXyY',
    'VERIFY_FONTFILE' => './Public/fonts/font.ttf',
    'VERIFY_SIZE' => '26',
    'VERIFY_COLOR' => '#444444',
    'VERIFY_NAME' => 'verify',
    'VERIFY_FUNC' => 'strtolower',

    'trade_expire'=>'60',


    //支付宝配置参数
    'ALIPAY'=>array(

        //应用ID,您的APPID。
        'app_id' => "2017062107533808",

        "method" =>"alipay.trade.wap.pay",

        "format" =>'json',////

        "payment_type"=>'1',// 支付类型 ，无需修改

        //商户私钥，您的原始格式RSA私钥
        'merchant_private_key' => "MIIEowIBAAKCAQEArpG2LmK0EcvGUtE8tMKDpevuvN8Q4x7hpmZ329hAKTbUA/XU4RmN3MEiw2l+/YGQ/xzghERlgsS2rA02UuUfMTeluHHnETuj9iCh/PS2qRxER4IR1arQyLhDWRN+moEHfQTXiosJ8YCq18pcmlXJ0YcPDOkK8gp6A03Orgm2a8YjYvB5sVq8vSop9yN2auFZVA+byQ4QLwWe8+M2iCnA4GaMg5NJOwzmlZElv+MN/K5AC0ybTUWddsqtctkjhYILIzh5Yeon4KHYSiE/uJB7oJEgdI0wfnIvjqp8wXkjp9XnPHwbDAE95TzLLR4ZuN4pJcLDzXDG8XeI1JsXdShpqQIDAQABAoIBACAzfrCVwnOZ7C5wrEsHqnMbz0EFwL60fplMQ4XJISS5GWjVmvwe051J/xieyC8JDG8Sq5OabkMt+ChKk5+85gEVK7uXPzFdmAQZxue7WEpfjXTHWHTaeoLcAohW4T6s2G3GS+Ahf6cNvfXqPSS2+HBvAma2Qi+doR4k5yARaJX+e407cjvDfcbpg/alAzAF/lqXriVoCixuEzNeVT7XwouIQPm6OsbUzk/hcSbOZhQhgvlf7JBp/MTuiR/cuLhRQ6ynm6Ngnb+yqw5kyb0QknBq7lR10HwkeSVJMLfgpKn28uXTQ3uOKs3+cveDt49UlCgTgssFl9zgHZZfSk5dj4ECgYEA3PzU06TT3kEhfMqimarKf5rz4foG+eDCmZ4rJmot9YgC62xlEZIJIWZevAC859qyL51pgc2Ki35gEUpiV1RV26s/8Ue+hf2cqeciLDiGKhJJ8iBRl5d2QZN4ofT9NTkX7emjOZnIGmoGbKb6+TqMvMYTbCEoq94CR+BDPcEMF1ECgYEAyjoq0njpr/OhnTmG/F2jDPUOwBM2G3vkM4g2jplrgxtXnI8pNYqSc/V0VYoMZqq1FD5OYUIYmhfbVIKiyiMcpGfe8csXxC+K30rAuvyHcEL3pfIOeMdeahRNfVivQE7xioNQ9vx9kpxpUfebPLhsPk0vFJ4bRy0fxsc4F8HgxtkCgYEAqqlG4zvDszyxU+JZuDrBr9JGzhl9EbSWFHSl9kDBxYCIYK+RTgRtsLGSL7aSLKwkN8llFa1adWffYPsE/1ROChsygm9Zn7jAKCYrqeLtGciN+sMiv+NknDf7TXgZo+S3qjBosa8lbeC7nWVHJPomfhKqJTNmaXRGZqO2yhYv9EECgYB8svmM53pSQU07Aio2nBba+pGp5y7KK88/55KcxAYDxmxz4eNrXJOKZaTiyklzVXhrjzAN0RASlPtuVU/EjXov9s0HFFEHbLmZjyLhKq7pjqHe9i/uUiHqD7LoxDLs2MEgxHC8nF1idoiLPr++5Yn5sOaDNCtl7HFXxajVHXxZoQKBgC/h1NohvGYVmC301fgAWIr70Fl2gqV3bqhhkI37OBxNw+dD/Yf2EMa7pwq9hmc9hg28t0sBFf1jiuQGKFiOmgaSLsjVFwZf1D7f5s1RUBdj4laQhGppBEj9I6Cc67PJ+yCYohoSeYtwgyU5JbAjQfd1N9IV4nJcxlBaT6zmm3RJ",

        //异步通知地址
        'notify_url' => "http://www.xcentiot.com/appAPI/alipaycallback/alipay_notify",

        //同步跳转
        'return_url' => "",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgB4vQFHR4cn2mplqA4yl/Cyiz/0hkscUqLEbfdfwY5vOi0P3eDWB9kW97kotsMy36dz+EPg2dVOQnSJoRCkK1bX6JGoMzsl4ngQaw03jAOm2Rm3dqKGT7q4dL/nc9cP/svIW0Yp2AmnqUgPNUGa3fXsYZ5Bi7i4cdn3S5lmX4epFzFfz5Awl6pViieEtksGA64bNMgRVUrFYkh1ZgqbD9IqwLIdL0PgnLdm3/86bp3pmzz5ADGtEWKATu5qox2r7vf6R2qkzGEhzsbcW8A4CJj1hFlaKfyb7n/HUJSbFp9GAwyVsmJ5rjw/QOuEUHCdv60ChYW8DwEhhXeCbZn9dKwIDAQAB",

    ),


);