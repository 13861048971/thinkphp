<?php
namespace Org\Weixin;

// define(APPID, 	'wxaca64c9652dd4643');
// define(APPTOKEN, 'stoneHeart');
// define(APPSECRET, '42330d78e5876e87960172ceb4cd72a4');

class Weixin {
	private $token;
	private $tokenFile;
	private $tempFile;
	private $logFile;
	
	private $error;
	
	public $appId;
	public $appToken;
	public $appSecret;
	
	function __construct($appId, $appToken, $appSecret){
		$this->tokenFile = ROOT_PATH . '/public/weixin/access_token';
		$this->tempFile  = ROOT_PATH . '/public/weixin/getTokening';
		$this->logFile	 = ROOT_PATH . '/public/weixin/log';
		
		$this->appId 		= $appId;
		$this->appToken 	= $appToken;
		$this->appSecret 	= $appSecret;
		
		if(!$this->checkToken())
			$this->getToken();
	}
	
	//处理消息
	public function parseMsg(){
		$this->log(json_encode($_REQUEST));
		if(!$this->checkToken())
			$this->getToken();
		
		//消息处理
		if($GLOBALS["HTTP_RAW_POST_DATA"]){
			new ParseMsg($this->appToken);
		}
	}
	
	public function getUserInfo($openid){
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?'.
			'access_token='. $this->token .'&openid='. $openid;
		$user = file_get_contents($url);
		$user = json_decode($user, 1);
		return $user;
	}
	
	/** 
	 * 处理菜单
	 * 
	 * @param string $menu 
	 *  	{"button":[{ "type":"click/view","name":"今日歌曲", "key":"V1001_TODAY_MUSIC"},
	 *	 		{ "name":"菜单", "sub_button":[{"type":"view","name":"搜索", "url":"http://www.soso.com/"}]}
	 */
	public function menuAdd($data){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->token;
		// $url = 'http://127.0.0.1:83/test.php';
		$data = static::post($url, $data);
		$data = json_decode($data, true);
		if($data['errmsg'] != 'ok')
			return $this->setError($data['errmsg']);
		return true;
	}
	
	//删除 全部删除
	public function menuDel(){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN';
		$result = file_get_contents($url);
		$data  = json_decode($result);
		if($data['errmsg'] != 'ok')
			return $this->setError($data['errmsg']);
		return true;
	}
	
	public function menuView(){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $this->token ;
		return file_get_contents($url);
	}
	
	public function setError($str){
		$this->error = $str;
		return false;
	}
	
	public function getError(){
		return $this->error;
	}
	
	static function post($url, $data){
		$opts = [ 'http' => [
			'method'  => 'POST',
			'header'  => 'Content-Type:application/x-www-form-urlencoded',
			'content' => $data
		]];
		$context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
		return $result;
	}
	
	static function do_post_request($url, $data, $optional_headers = null)
	{
		$params = array('http' => array(
		  'method' => 'POST',
		  'content' => $data
	    ));
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
	
	static function post2($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	private function log($str){
		$handle = fopen($this->logFile, 'a+');
		fwrite($handle, $str . "\r\n");
		fclose($handle);
	}
	
	//检测令牌是否有效
	private function checkToken(){
		if(!s($this->tokenFile))
			return false;

		$data = json_decode(s($this->tokenFile), true);
		$this->token = $data['access_token'];
		$expires_time = $data['expires_time'];
		
		if(s($this->tempFile))
			return $this->token;
		
		if(time() > $expires_time - 30){
			s($this->tokenFile, null);
			return false;
		}
		return $this->token;
	}
	
	/**
	 * 取令牌
	 */
	private function getToken(){
		s($this->tempFile, 'ing...');
		$url = 'https://api.weixin.qq.com/cgi-bin/token?'.	
			'grant_type=client_credential&appid='.$this->appId.'&secret=' . $this->appSecret;
		$data = file_get_contents($url);
		$arr = json_decode($data, true);
		$arr['expires_time'] = time() + $arr['expires_in'];
		$arr['expires'] = date('Y-m-d H:i:s', $arr['expires_time']);
		$this->token = $arr['access_token'];
		s($this->tempFile, null);
		s($this->tokenFile, json_encode($arr), $arr['expires_in']);
		
		return $this->token;
	}
}


