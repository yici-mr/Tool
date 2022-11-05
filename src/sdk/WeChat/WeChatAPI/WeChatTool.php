<?php

namespace yctool\sdk\WeChat\WeChatAPI;
use yctool\tool\Request;

class WeChatTool
{

    public static $config ;
    public function __construct($config)
    {
        self::$config = $config;
    }
    /**
     * 可以获得sessionKey和openid
     * @param  string $code 获取的code参数
     **/
    public function GetOpenid(string $code):array
    {
        $config = self::$config;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$config['appid']}&secret={$config['AppSecret']}&js_code={$code}&grant_type=authorization_code";
        return Request::curl_get($url);
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $sessionKey string 加密的用户数据
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return int 返回0是获取成功，其他为错误码
     */
    public function GetUserInfo(string $sessionKey,string $encryptedData,string $iv,string &$data):int
    {
        $config = self::$config;
        if (strlen($sessionKey) != 24) {
            return -41001;
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        $result=openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode($result);
        if( $dataObj  == NULL )
        {
            return -41003;
        }
        if( $dataObj->watermark->appid !=  $config['appid'])
        {
            return -41004;
        }
        $data = (array)$dataObj;
        return 0;
    }


}