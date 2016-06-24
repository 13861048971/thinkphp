<?php

namespace Org\Weixin;
// 接受消息并返回
class ParseMsg{
	private $logFile;
	private $appToken;
	
	function __construct($appToken){
		$this->logFile = ROOT_PATH . '/public/weixin/msglog';
		$str = $GLOBALS["HTTP_RAW_POST_DATA"];
		
		if(!$this->checkAccount() || !$this->checkSignature($appToken)){
			return $this->addLog( $str . " ,验证失败!");
		}else{
			$this->addLog($str);
		}
		
		$xml = simplexml_load_string($str);
		echo '<xml>
<ToUserName><![CDATA['. $xml->FromUserName .']]></ToUserName>
<FromUserName><![CDATA['. $xml->ToUserName .']]></FromUserName>
<CreateTime>'. time() .'</CreateTime>
<MsgType><![CDATA[news]]></MsgType>'. $this->news() .'</xml>';
		
	}
	
		//检测消息真实性
	function checkAccount(){
		$echostr = $_GET['echostr'];
		if($echostr && $this->checkSignature()){
			return false;
		}
		return true;
	}
	
	/**
	 * 检测消息真实性
	 */
	private function checkSignature($appToken){
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];	
					
		$tmpArr = array(APPTOKEN, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature )
			return true;

		return false;
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

