<?php
/**
 * 菜单管理
 **/
namespace Org\Weixin;

define(APPID, 	'wxaca64c9652dd4643');
define(APPTOKEN, 'stoneHeart');
define(APPSECRET, '42330d78e5876e87960172ceb4cd72a4');

Class ParseMsg{
	private $token;
	private $tokenFile;
	private $tempFile;
	private $logFile;
	
	function __construct(){
		$this->tokenFile = ROOT_PATH . '/public/weixin/access_token';
		$this->tempFile  = ROOT_PATH . '/public/weixin/getTokening';
		$this->logFile	 = ROOT_PATH . '/public/weixin/log';
		
		$this->log(json_encode($_REQUEST));
		$this->checkAccount();
		if(!$this->checkToken())
			$this->getToken();
		
		//消息处理
		if($GLOBALS["HTTP_RAW_POST_DATA"]){
			new Msg();
		}
	}
	
	function log($str){
		$handle = fopen($this->logFile, 'a+');
		fwrite($handle, $str . "\r\n");
		fclose($handle);
	}
	

}

class Msg{
	private $logFile;
	function __construct(){
		$this->logFile = ROOT_PATH . '/public/weixin/msglog';
		$str = $GLOBALS["HTTP_RAW_POST_DATA"];
		$xml = simplexml_load_string($str);
		$this->addLog($str);
		echo '<xml>
<ToUserName><![CDATA['. $xml->FromUserName .']]></ToUserName>
<FromUserName><![CDATA['. $xml->ToUserName .']]></FromUserName>
<CreateTime>'. time() .'</CreateTime>
<MsgType><![CDATA[news]]></MsgType>'. $this->news() .'</xml>';
		
	}
	
	//普通文本
	function text(){
		return '<Content><![CDATA[我听不懂]]></Content>';
	}
	
	//新闻的格式
	function news(){
		return '<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[title1]]></Title> 
<Description><![CDATA[description1]]></Description>
<PicUrl><![CDATA[picurl]]></PicUrl>
<Url><![CDATA[http://114.215.149.24/weixin/url.php]]></Url>
</item>
<item>
</Articles>';
	}
	
	//添加消息日志
	function addLog($str){
		$handle = fopen($this->file, 'a+');
		fwrite($handle, $str . "\r\n");
		fclose($handle);
	}
	
}

