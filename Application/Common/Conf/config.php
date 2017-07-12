<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin'),
    'DEFAULT_MODULE'       =>    'Home',  // 默认模块

    //URL地址不区分大小写
    'URL_CASE_INSENSITIVE' =>true,
    'URL_MODEL'=>0,
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

);