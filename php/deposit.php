<?php
 
header("Content-type:text/html;charset=utf-8");

/**
 * openssl 实现的 DES 加密类
 */
class DES
{
	  /**
     * @var string $method 加解密方法
     */
    protected $method = 'DES-CBC';
    
    /**
     * @var string $key 加解密的密钥
     */
    protected $key;
        
    /**
     * @var string $output 输出格式
     */
    protected $output = 'hex';

    /**
     * @var string $iv 加解密的向量
     */
    protected $iv;

    /**
     * @var string $options
     */
    protected $options = 3;

    public function __construct($key)
    {
        $this->key = strtoupper(substr(md5($key), 0, 8));
        $this->iv = $this->key;
    }

    /**
     * 加密
     *
     * @param $str
     * @return string
     */
    public function encrypt($str)
    {	  
        $str = $this->pkcsPadding($str, 8);
        $sign = openssl_encrypt($str, $this->method, $this->key, $this->options, $this->iv);
				$sign = strtoupper(bin2hex($sign));
        return $sign;
    }

    /**
     * 解密
     *
     * @param $encrypted
     * @return string
     */
    public function decrypt($encrypted)
    {
        $encrypted = hex2bin($encrypted);
        $sign = @openssl_decrypt($encrypted, $this->method, $this->key, $this->options, $this->iv);
        $sign = $this->unPkcsPadding($sign);
        $sign = rtrim($sign);
        return $sign;
    }

    /**
     * 填充
     *
     * @param $str
     * @param $blocksize
     * @return string
     */
    private function pkcsPadding($str, $blocksize)
    {
        $pad = $blocksize - (strlen($str) % $blocksize);
        return $str . str_repeat(chr($pad), $pad);
    }

    /**
     * 去填充
     * 
     * @param $str
     * @return string
     */
    private function unPkcsPadding($str)
    {
        $pad = ord($str{strlen($str) - 1});
        if ($pad > strlen($str)) {
            return false;
        }
        return substr($str, 0, -1 * $pad);
    }

}

// Secret key
$key = '83A6AEDE-3B5A-4D3E-B789-DC780421C1A1';
// smac
$smac = 'C628C57D3F0FCB7652D9C5D64898DFFF';

/**
 * 入金数据加密过程
 */
// 接口参数次序如下
$data['realName'] = urlencode('实名02'); // 客户真实姓名，可空
$data['amount'] = '1000'; // 入金金额(美元)
$data['orderId'] = '2018071318275278924'; // 商家平台入金订单ID
$data['idCard'] = '2222222222'; // 2222222222 客户证件号码，可空
$data['notifyUrl'] = 'https://www.domain.com/notify/callback'; // 入金成功后，定时 同步Url
$data['returnUrl'] = 'https://www.domain.com/return/callback'; // 入金成功后，立即 调用Url
$data['sendTime'] = '2018-07-13 18:27:52'; // 入金时间

$ciphertext['data'] = $data;
$ciphertext['sign'] = strtoupper(md5(stripslashes(json_encode($data)) . $smac)); // 签名
$cipherStr = stripslashes(json_encode($ciphertext));

echo '入金数据加密：<br><br>';
echo '原文：<br>' . $cipherStr;
echo "<br><br>";

// DES CBC 加解密
$des = new DES($key);
echo '密文：<br>' . ($encStr = $des->encrypt($cipherStr));
echo "<br><br>";
echo '解密：<br>' . $des->decrypt($encStr);
echo "<br><br>";

?>
