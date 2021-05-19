<?php

#框架全局助手函数

/**
 * 快捷调试打印
 * @param ...$args
 * @return mixed
 */
function dd(...$args)
{
    foreach ($args as $x) {
        var_dump($x);
    }
    exit;
}

/**
 * 获取环境配置
 * @param string $name        参数名
 * @param null   $default     错误默认返回值
 * 
 * @return mixed|null
 */
function env($name = NULL , $default = NULL)
{
    if($name){
        return $_ENV[$name] ?? $default;
    }else{
        return $_ENV?:[];
    }
}

/**
 * 获取应用配置参数
 * @param      $name        参数名 格式：文件名.参数名
 * @param null $default     错误默认返回值
 *
 * @return mixed|null
 */
function config($name , $default = NULL){
    return \Chazz\Config::getInstance()->get($name,$default);
} 

/**
 * UUID
 * @param  bool     base62
 * @return string   UUID
 */
function uuid($base62 = true)
{
    $str = uniqid('', true);
    $arr = explode('.', $str);
    $str = $arr[0] . base_convert($arr[1], 10, 16);
    $len = 32;
    while (strlen($str) <= $len) {
        $str .= bin2hex(random_bytes(4));
    }
    $str = substr($str, 0, $len);
    if ($base62) {
        $str = str_replace(['+', '/', '='], '', base64_encode(hex2bin($str)));
    }
    return $str;
}

/**
 * 加密
 * @param string $str    要加密的数据
 * @param string $aes_key AES秘钥
 * @return bool|string   加密后的数据
 */
function encrypt($str,$aes_key) {
    $data = openssl_encrypt($str, 'AES-128-ECB', $aes_key, OPENSSL_RAW_DATA);
    $data = base64_encode($data);
    return $data;
}

/**
 * 解密
 * @param string $str     要解密的数据
 * @param string $aes_key AES秘钥
 * @return string         解密后的数据
 */
function decrypt($str,$aes_key) {
    $decrypted = openssl_decrypt(base64_decode($str), 'AES-128-ECB', $aes_key, OPENSSL_RAW_DATA);
    return $decrypted;
}


if (function_exists('Http') === false) {
    /**
     * 构建Http请求
     * @param string $url     请求地址
     * @param string $data    请求数据
     * @param string $type    请求类型
     * @return string         请求结果
     */
    function Http($url,$data,$type="http"){
        $curl = curl_init();
        if ($type == "json"){
            $headers = array("Content-type: application/json;charset=UTF-8");
            $data=json_encode($data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}