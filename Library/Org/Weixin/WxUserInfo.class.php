<?php
namespace Org\Weixin;
/**
 * 获取用户信息
 */
class WxUserInfo{
	private $error = '';
	private $host;
	
	private $redirect_uri;
	private $appId;
	private $appSerc;
	
	private $tokenCacheKey;
	
	
	function __construct($appId, $appSerc = ''){
		$this->appId   = $appId;
		$this->appSerc = $appSerc;
		$this->tokenCacheKey = 'token_cache_key' . $appId;
		$this->host = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
	}
	
	//跳转getCode
	function getCode($redirect_uri){
		$url = $this->host . 'appid=' . $this->appId.
			'&redirect_uri='. urlencode($redirect_uri) .
			'&response_type=code&scope=snsapi_userinfo'.
			'&state=STATE#wechat_redirect';
		header('location: '. $url);
		return;
	}
	
	/**
	 * 取用户信息
	 */
	function getInfo($code){
		$data = json_decode(s($this->tokenCacheKey), 1);
		if(!$data || $data['expire'] < time() || 
			!$this->checkToken($data['access_token'], $data['openid'])){
				
			if(!($data = $this->getToken($code) ))
				return $this->setError('获取token失败');
			
			$data['expire'] = time() + $data['expire_in'] - 30;
			
			s($this->tokenCacheKey, json_encode($data), $data['expire_in']/60);
		}
		
		if(!($user=$this->getUserInfo($data['access_token'], $data['openid'])))
			return $this->setError('取用户信息失败!');
		
		return $user;
	}

	function getError(){
		return $this->error;
	}
	
	private function setError($str){
		$this->error .= $str."\n";
		return false;
	}
	
	/**
	 * 获取token
	 */
	private function getToken($code){
		if(!$code || !$this->appSerc){
			throw new Exception('缺少code or secret!');
		}
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?'.
			'appid=' . $this->appId . 
			'&secret='. $this->appSerc .
			'&code='. $code .
			'&grant_type=authorization_code';
		$str = file_get_contents($url);
		\Think\Log::write($url . $str ,'Err');
		$data = json_decode($str, true);
		if(!$data['access_token'])
			return $this->setError('getToken:'.$data['errmsg'] . $str);
		return $data;
	}
	
	/**
	 *  获取用户信息
	 */
	private function getUserInfo($token, $openid ){
		$url = 'https://api.weixin.qq.com/sns/userinfo?' . 
			'access_token='. $token .
			'&openid='. $openid .
			'&lang=zh_CN';
		$user = file_get_contents($url);
		$user = json_decode($user, 1);
		return $user;
	}
	
	/**
	 * 校验toke 是否过期
	 */
	private function checkToken($token, $openid){
		$url = 'https://api.weixin.qq.com/sns/auth?access_token='. $token .
			'&openid=' . $openid;
		$data = json_decode(file_get_contents($url), true);
		if($data['errcode'] > 0)
			return $this->setError("checkToken:".$data['errmsg']);
		
		return true;
	}
}

