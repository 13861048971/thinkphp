<?php
namespace Org\Weixin;

define(APPID, 	'wxaca64c9652dd4643');
define(APPTOKEN, 'stoneHeart');
define(APPSECRET, '42330d78e5876e87960172ceb4cd72a4');

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
	
	/** 
	 * 处理菜单
	 * 
	 * @param array $menu 
	 *  	{"button":[{ "type":"click/view","name":"今日歌曲", "key":"V1001_TODAY_MUSIC"},
	 *	 		{ "name":"菜单", "sub_button":[{"type":"view","name":"搜索", "url":"http://www.soso.com/"}]}
	 */
	public function menuAdd($menu){
		$data = json_encode($menu);
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->token;
		$data = json_decode(static::post($url, $menuData));
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
		$postData = http_build_query($data);
		$opts = [ 'http' => [
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		]];
		$context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
		return $result;
	}
	
	private function log($str){
		$handle = fopen($this->logFile, 'a+');
		fwrite($handle, $str . "\r\n");
		fclose($handle);
	}
	
	//检测令牌是否有效
	private function checkToken(){
		if(!is_file($this->tokenFile))
			return false;

		$data = json_decode(file_get_contents($this->tokenFile), true);
		$this->token = $data['token'];
		$expires_time = $data['expires_time'];
		
		if(is_file($this->tempFile))
			return $this->token;
		
		if(time() > $expires_time - 30){
			return false;
		}
		return $this->token;
	}
	
	/**
	 * 取令牌
	 */
	private function getToken(){
		file_put_contents($this->tempFile, 'ing...');
		$url = 'https://api.weixin.qq.com/cgi-bin/token?'.	
			'grant_type=client_credential&appid='.$this->appId.'&secret=' . $this->appSecret;
		$data = file_get_contents($url);
		$arr = json_decode($data, true);
		$arr['expires_time'] = time() + $arr['expires_in'];
		$arr['expires'] = date('Y-m-d H:i:s', $arr['expires_time']);
		$this->token = $arr['access_token'];
		unlink($this->tempFile);
		file_put_contents($this->tokenFile, json_encode($arr));
		
		return $this->token;
	}
}


