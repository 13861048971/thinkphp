<?php
namespace Vendor\Qiniu;
use Vendor\Qiniu;
class GetToken{
	public $AK;
	public $SK;
	
	public $returnBody = '{
		  "name": $(fname),
		  "size": $(fsize),
		  "w": $(imageInfo.width),
		  "h": $(imageInfo.height),
		  "hash": $(etag)
	}';
	
	function __construct($AK,$SK){
		$this->AK = $AK;
		$this->SK = $SK;
	}
	
	/**
	*获取json格式的字符串
	*@param string $bucket 七牛存储空间
	*@param string $imgName 上传图片的名称
	*/
	
	function toJsonStr($bucket, $imgName){
		$arr['scope'] = $bucket.':'.$imgName;
		$arr['deadline'] = time()+30;
		$arr['returnBody'] = $this->returnBody;
		return json_encode($arr);
	}
	
	/**
	*base64转码字符串
	*@param string $jsonString 要转码的字符串
	*/
	function base64($jsonString){
		return base64_encode($jsonString);
	}
	
	/**
	*获取json格式的字符串
	*@param string $bucket 七牛存储空间
	*@param string $imgName 上传图片的名称
	*/
	function getUploadToken($bucket, $imgName){
		$jsonString = $this->toJsonStr($bucket, $imgName);
		$encodedPutPolicy = $this->base64($jsonString);
		$sign = hash_hmac('sha1', $encodedPutPolicy, $this->SK);
		$encodedSign  = $this->base64($sign);
		return $this->AK.':'.$encodedSign.':'.$encodedPutPolicy; 
	}
}
	