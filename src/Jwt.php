<?php

namespace ozings;

class Jwt
{
	protected $config;
	protected $check = ['time'];
	
	/**
     * @param array $config = [
	 * 		'pwdhash'=>'',
	 * 		'private_key'=>'',
	 * 		'public_key'=>'',
	 * 		'token_cache_time'=>'',
	 * ];
     */
    public function __construct($config = [])
	{
        $this->config = $config;
    }

	/**
     * 初始化配置参数
     * @access public
     * @param array $config 连接配置
     * @return void
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * 加密函数
     */
    public function encrypt($pwd = '', $randstr = '')
    {
        $return = md5($pwd . $randstr . $this->config['pwdhash']);
        return $return;
    }

    /**
     * 加密函数
     * @param $data  string 待加密的数据
     * @param $key   string 用于加密的key
     * @return string 加密后的字符串
     */
    public function apiEncrypt($data)
    {
        $pi_key = openssl_pkey_get_private($this->config['private_key']);
        openssl_private_encrypt($data, $encypted, $pi_key);
        $data = base64_encode($encypted);
        return $data;
    }

    /**
     * 解密函数
     * @param $data  string 待解密的数据
     * @param $key   string 加密的key
     * @return string 解密后的字符串
     */
    public function apiDecrypt($data)
    {
        $pu_key = openssl_pkey_get_public($this->config['public_key']);
        openssl_public_decrypt(base64_decode($data), $decrypted, $pu_key);//私钥加密的内容通过公钥可用解密出来
        return $decrypted;
    }

    /**
     * 公钥加密函数
     * @param $data   待解密的数据
     * @param $key    加密的key
     * @return string 加密后base64处理的字符串
     */
    public function publicEncrypt($data)
    {
        $pu_key = openssl_pkey_get_public($this->config['public_key']);
        openssl_public_encrypt($data, $encypted, $pu_key);
        $data = base64_encode($encypted);
        return $data;
    }

    /**
     * 私钥解密函数
     * @param $data   待解密的数据
     * @param $key    加密的key
     * @return string 解密后的字符串
     */
    public function privateDecrypt($data)
    {
        $pi_key = openssl_pkey_get_private($this->config['private_key']);
        openssl_private_decrypt(base64_decode($data), $decrypted, $pi_key);//公钥加密的内容通过私钥可用解密出来
        return $decrypted;
    }

    /**
     * 解析token 并验证有效性
     * @param $_SERVER['HTTP_TOKEN']  string  Header参数token
     * @return $data array
     */
    public function getLoginInfo($check = [],$cache_time = 0)
    {
        $token = $_SERVER['HTTP_TOKEN'];

        $data = self::decrypToken($token);
		
		if ($check) {
			$this->check = array_merge($this->check,$check);
			$this->check = array_unique($this->check);
		}
		//检校token解析参数
		foreach ($this->check as $key => $val) {
			if (!isset($data[$val])) {
				return false;
			}
		}
		//自定义过期时间
        if (!$cache_time) {
            $cache_time = $this->config['token_cache_time'];
		}
		//判断过期时间
        if ((time() - $data['time']) > $cache_time) {
			return false;
        }
        return $data;
    }


    /*
    * 解析token串
    * @param $token string Header参数 token
    * @return $tokenStr string 
    */
    public function decrypToken($token = '')
	{
        $token = (empty($token) && isset($_SERVER['HTTP_TOKEN'])) ? $_SERVER['HTTP_TOKEN'] : $token;

        //解密后字符串 id=1&time=12345678910
        $apiDecryptStr = self::apiDecrypt($token); 

        //解析字符串 
        parse_str($apiDecryptStr, $data);

        return $data;
    }

    /*
    * 将数组转URL字符串并加密成token
    * @param $data array  数组
    * @return $tokenStr string 
    */
    public function createToken($data = [])
	{
        $tokenStr = self::createTokenStr($data);

        //加密成token串
        $token = self::apiEncrypt($tokenStr); 

        return $token;
    }

    /*
    * 生成未加密token串
    * @param  $tokenData = [
	* 	'id'=>1,
	* 	'uid'=>1,
	* 	'time'=>time()
	* ]
    * @return $tokenStr string id=1&uid=1&time=12345678910
    */
    public function createTokenStr($tokenData = [])
	{
        // 1 按字段排序
        ksort($tokenData);
        // 2拼接字符串数据  id=1&time=12345678910
        $tokenStr = http_build_query($tokenData);
        return $tokenStr;
    }	
}
