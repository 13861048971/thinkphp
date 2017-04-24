<?php
/**
 * 云片短信平台 
 *
 */
 
Class YunpianMessage{
	private $apikey;
	private $ch;
	
	function __construct($apikey){
		$this->apikey = $apikey;
	}

	/**
	 * 用户信息
	 */
	function getUser(){
		$url = 'https://sms.yunpian.com/v1/user/get.json';
		return self::post($url, $data);
	}
	
	/**
	 * 普通短信发送
	 */
	function send($mobile, $text){
		$data = ['mobile' => $mobile, 'apikey'=>$this->apikey, 'text'=>$text ];
		$url = 'https://sms.yunpian.com/v1/sms/send.json';
		return self::post($url, $data);
	}
	
	/**
	 * 模板短信
	 * @param string $mobile
	 * @param int $tplId
	 * @param array $arr 参数数组
	 */
	function tplSend($mobile, $tplId, $arr){
		foreach($arr as $k=>$v){
			$value .= ('#'. $k .'#').'='.urlencode($v).'&';
		}
		$value = rtrim($value, '&');

		$data = [
			'tpl_id' => $tplId,
			'tpl_value' => $value,
			'apikey' => $this->apikey,
			'mobile' => $mobile
		];
		$url = 'https://sms.yunpian.com/v1/sms/tpl_send.json';
		return self::post($url, $data);
	}
	
	/**
	 * 语音短信
	 */
	function voiceSend($mobile, $code){
		$data=array( 'code'=>$code, 'apikey' => $this->apikey,'mobile' => $mobile);
		$url = 'http://voice.yunpian.com/v1/voice/send.json';
		return self::post($url, $data);
	}
	
	/**
	 * post 请求
	 * @param string $url
	 * @param array $data
	 */
	static function post($url, $data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));

		/* 设置返回结果为流 */
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/* 设置超时时间*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		/* 设置通信方式 */
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}
