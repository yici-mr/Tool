<?php

namespace yctool\sdk\WeChat\WeChatPayV3;

class Pay
{
    public static $config ;
    public static $mch;
    public static $sign = "";
    public static $nonce_str = "";
    public static $post_data = "";
    public static $signBody = "";
    /**
     * @param  string $name  小程序填入applet，公众号填入accounts
     **/
    public static function config(string $name):self
    {
        $config = include_once (__DIR__."/../config.php");
        self::$config = $config[$name];
        self::$mch = $config['mch'];
        return new self();
    }
    /**构造小程序订单体
     * @param  string $openid  openid
     * @param  string $total 金额
     * @param  string $out_trade_no 32位自定义订单号
     * @param  string $description  商品描述
     * @param  string $notify_url 自定义回调地址
     **/
    public function applet_order(string $openid,string $total,string $out_trade_no,string $description,string $notify_url):self
    {
        self::$nonce_str = $out_trade_no;
        $post_data =  [
            'notify_url'=>$notify_url,
            'appid'=>self::$config['appid'],
            'mchid'=>self::$mch['mchid'],
            "description" => $description,
            "out_trade_no" => $out_trade_no,
            "amount" => [
                "total" => $total,
                "currency"=> "CNY"
            ],
            "payer" => [
                "openid" => $openid,
            ],

        ];

        self::$post_data = json_encode($post_data);
        return $this;
    }
    /**小程序支付签名
     * @param string $prepay_id 接口获得的值
     **/
    public static function signPay(string $prepay_id):string
    {

        $data = array(
            self::$config['appid'], time(), self::$nonce_str, "prepay_id=$prepay_id", ''
        );
        $str =  join("\n", $data);
        $private = file_get_contents(self::$mch['cert']);
        $key = openssl_pkey_get_private($private);
        openssl_sign($str, $signature, $key, 'sha256WithRSAEncryption');
        return base64_encode($signature);
    }
    /**构造小程序订单head参数体
     **/
    public function signBody(): Pay
    {
        $disposeUrl = '/v3/pay/transactions/jsapi';
        $time = time();
        $nonce_str = self::$nonce_str;
        $requestBody = self::$post_data;
        $data = array(
            'POST', $disposeUrl, $time, $nonce_str, $requestBody, ''
        );
        self::$signBody = join("\n", $data);
        return $this;
    }
    /**构造请求头
     * @return array 返回一个请求头数组
     **/
    public function head():array
    {
        $token = sprintf('mchid="%s",serial_no="%s",nonce_str="%s",timestamp="%s",signature="%s"', self::$mch['mchid'],self::$mch['cert_id'],  self::$nonce_str, time(), $this->getSignature(self::$signBody));
        $head  = ['Accept:application/json','User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.134 Safari/537.36 Edg/103.0.1264.71','Content-Type: application/json; charset=utf-8','Authorization: WECHATPAY2-SHA256-RSA2048 '.$token];
        return $head;
    }

    public function getSignature($data):string
    {
        $private = file_get_contents(self::$mch['cert']);
        $key = openssl_pkey_get_private($private);
        openssl_sign($data, $signature, $key, 'sha256WithRSAEncryption');
        return base64_encode($signature);
    }





    /******************下面是回调***********************/
    /**构造证书的head
     * @return self 返回一个请求头数组
     **/
    public static function cert():self
    {
        $post_type = 'GET';
        $disposeUrl = '/v3/certificates';
        $Time = time();
        $sone = md5($Time);
        $body = '';
        $data = array(
            $post_type, $disposeUrl, $Time, $sone,$body,''

        );
        self::$nonce_str = $sone;
        self::$signBody = join("\n", $data);
        return new self;
    }
    /**解密resource 敏感信息
     * @param  string $ciphertext 回调的密文
     * @param  string $nonce 回调中的nonce
     * @param  string $associated_data 回调中的associated_data
     * @return string 返回json数据
     **/
    public static function decode(string $ciphertext,string $nonce,string $associated_data):string
    {
        $ciphertext = base64_decode($ciphertext);
        $is = sodium_crypto_aead_aes256gcm_decrypt($ciphertext,$associated_data,$nonce,self::$mch['pay_token']);
        return $is;
    }



    /**验证签名
     * @param  string $data 处理过的数据
     * @param  string $sign 返回给我们的签名
     * @param  string $cert 证书平台证书
     * @return string 返回bool值
     **/
    public static function signature(string $data,string $sign,string $cert):bool
    {
        $key = openssl_pkey_get_public($cert);
        $ok = openssl_verify($data,base64_decode($sign),$key,OPENSSL_ALGO_SHA256);
        return $ok;
    }

}