<?php
return [
    //小程序配置
    'applet'=>[
        'appid'=>'',
        'original' => '',
        'AppSecret' => '',
    ],
    //公众号配置
    'accounts'=>[
        'appid'=>'',
        'original' => '',
        'AppSecret' => '',
    ],
    //商户配置
    'mch'=>[
        'mchid'=>'',//商户id
        'pay_token'=>'',//商户后台32位密钥
        'cert_id'=>'',//证书id
        "cert"=>"../apiclient_key.pem" //证书路径
    ],
];