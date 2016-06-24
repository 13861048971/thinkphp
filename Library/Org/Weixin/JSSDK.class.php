<?php
namespace Org\Weixin;
class JSSDK {
	private $appId;
	private $appSecret;
	private $ticketFile;
	private $tokenFile;
	
	public function __construct($appId, $appSecret) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;

		$this->ticketFile = 'jsapi_ticket_' . $appId;
		$this->tokenFile  = 'access_token_' . $appId;
	}

	private static function cache($k, $v, $expire = 7200){
	  s($k, $v, $expire);
	}

	private static function getCache($k){
	  return s($k);
	}

	public function getSignPackage() {
		$jsapiTicket = $this->getJsApiTicket();
		// 注意 URL 一定要动态获取，不能 hardcode.
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$timestamp = time();
		$nonceStr = $this->createNonceStr();

		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);

		$signPackage = array(
		  "appId"     => $this->appId,
		  "nonceStr"  => $nonceStr,
		  "timestamp" => $timestamp,
		  "url"       => $url,
		  "signature" => $signature,
		  "rawString" => $string
		);
		return $signPackage; 
	}

	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
		  $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getJsApiTicket() {
		// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(self::getCache($this->ticketFile));
		if ($data->expire_time < time()) {
			$accessToken = $this->getAccessToken();
			// 如果是企业号用以下 URL 获取 ticket
			// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi".
				"&access_token=$accessToken";
			$str = file_get_contents($url);
			$res = json_decode($str);
			$ticket = $res->ticket;
			if ($ticket) {
				$data->expire_time = time() + 7000;
				$data->jsapi_ticket = $ticket;
				self::cache($this->ticketFile, json_encode($data));
			}
		} else {
			$ticket = $data->jsapi_ticket;
		}

		return $ticket;
	}

	private function getAccessToken() {
		// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(self::getCache($this->tokenFile));
		if (!$data || $data->expire_time < time()) {
			// 如果是企业号用以下URL获取access_token
			// $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential".
				"&appid=$this->appId&secret=$this->appSecret";
			$res = json_decode(file_get_contents($url));
			$access_token = $res->access_token;
			if ($access_token) {
				$data->expire_time = time() + 7000;
				$data->access_token = $access_token;
				$data->expires = date('Y-m-d H:i:s', $data->expire_time);
				self::cache($this->tokenFile, json_encode($data));
			}
		}
		return $data->access_token;
	}
}

