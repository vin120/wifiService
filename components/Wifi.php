<?php
namespace app\components;
use Yii;

class Wifi
{
	//获取wifi的名字，价格
	public static function getWifiItem($wifi_id, $iso = 'zh_cn' )
	{
		$sql = " SELECT a.wifi_id,a.sale_price ,a.wifi_flow,b.wifi_name 
			FROM wifi_item a ,wifi_item_language b 
			WHERE a.wifi_id = b.wifi_id 
			AND a.wifi_id ='$wifi_id' AND b.iso='$iso'";
		$wifi_item = Yii::$app->db->createCommand($sql)->queryOne();
		return $wifi_item;
	}
	
	
	//获取所有wifi套餐
	public static function getAllWifiItem($iso='zh_cn')
	{
		$sql = "SELECT a.wifi_id,a.sale_price,a.wifi_flow,b.wifi_name 
		FROM wifi_item a ,wifi_item_language b
		WHERE a.wifi_id = b.wifi_id
		AND a.status=0 AND b.iso='$iso' AND a.status=0";
	
		$wifi_item = Yii::$app->db->createCommand($sql)->queryAll();
		return $wifi_item;
	}
	
	//获取wifi的帐号，密码
	public static function getWifiInfo($wifi_info_id)
	{
		$sql = "SELECT wifi_info_id,wifi_id,wifi_code, wifi_password FROM wifi_info WHERE wifi_info_id='$wifi_info_id'";
		$wifi_info = Yii::$app->db->createCommand($sql)->queryOne();
		return $wifi_info;
	}
	
	
	//查询卡号是否存在，并把卡号设置成售出状态
	public static function CheckCardAndSetSold($wifi_id)
	{
		$sql = "SELECT wifi_info_id FROM wifi_info WHERE wifi_id='$wifi_id' AND status_sale=0 LIMIT 1";
		$wifi_info_id = Yii::$app->db->createCommand($sql)->queryOne()['wifi_info_id'];
		if($wifi_info_id){
			$sql = "UPDATE wifi_info SET status_sale='1' WHERE wifi_info_id='$wifi_info_id'";
			Yii::$app->db->createCommand($sql)->execute();
			return $wifi_info_id;
		}else {
			return false;
		}
	}
	
	
	//设置卡号为未售出
	public static function SetUnsold($wifi_info_id)
	{
		$sql = "UPDATE wifi_info SET status_sale='0' WHERE wifi_info_id='$wifi_info_id'";
		Yii::$app->db->createCommand($sql)->execute();
	}
	
	
	//把用户最近使用的id,卡号和密码写入数据库中
	public static function writeConnectWifiCardToDB($PassportNO,$wifi_info_id,$wifi_code,$wifi_password)
	{
		//先查询数据库是否存在记录
		$sql = " SELECT * FROM `wifi_last_connect` WHERE `passport_number`='$PassportNO'";
		$query = Yii::$app->db->createCommand($sql)->queryOne();
		if($query){
			//如果存在记录,update
			$sql = " UPDATE `wifi_last_connect` SET `wifi_info_id`='$wifi_info_id',`card_number`='$wifi_code',`card_password`='$wifi_password' WHERE `passport_number`='$PassportNO' ";
			Yii::$app->db->createCommand($sql)->execute();
		}else {
			//如果不存在记录,insert
			$sql = " INSERT INTO `wifi_last_connect` (`wifi_info_id`,`card_number`,`card_password`,`passport_number`) VALUES ('$wifi_info_id','$wifi_code','$wifi_password','$PassportNO')";
			Yii::$app->db->createCommand($sql)->execute();
		}
	}
	
	
	//查找数据库，获取最近使用的一张卡号的帐号和密码
	public static function getConnectWifiCardFromDB($PassportNO)
	{
		//通过passportNo查找数据库，获取card_number,card_passwd,wifi_info_id
		$sql = " SELECT * FROM `wifi_last_connect` WHERE `passport_number`='$PassportNO'";
		$card = Yii::$app->db->createCommand($sql)->queryOne();
		
// 		//下面的数据是假数据
// 		$card['card_number'] = "123456";
// 		$card['card_password'] = "123456";
// 		$card['wifi_info_id'] = "1";
		
		return $card;
	}
	
	
	//购买上网卡
	public static function wifiCardBuy($wifi_info_id,$passport,$pay_log_id)
	{
		$time = date('Y-m-d h:i:s',time());
		//设置wifi_info表中的开通时间
		self::setWifiInfoTime($wifi_info_id,$time);
		//把相关信息保存到wifi_item_status
		self::wifiItemSave($passport,$wifi_info_id,$pay_log_id);
	}
	
	//设置wifi_info表中的开通时间
	private static function setWifiInfoTime($wifi_info_id,$time)
	{
		$sql = "UPDATE wifi_info SET time='$time'  WHERE wifi_info_id='$wifi_info_id'";
		Yii::$app->db->createCommand($sql)->execute();
	}
	
	
	//把相关信息保存到wifi_item_status
	private  static function wifiItemSave($passport,$wifi_info_id,$pay_log_id)
	{
		$sql = " INSERT INTO `wifi_item_status` (passport_num,wifi_info_id,pay_log_id) VALUES('$passport','$wifi_info_id','$pay_log_id')";
		Yii::$app->db->createCommand($sql)->execute();
	}
	
	// 获取游客购买的上网卡
	// 判断有效期
	public static function getWifiItemStatus($passport)
	{
		//1.查找 wifi_info 的开通时间
		//2.查找 wifi_item 的有效时间
		//3.对比当前时间和开通时间，如果小于有效时间，显示

		$time = date('Y-m-d H:i:s',time());
// 		$sql = " SELECT a.*,b.wifi_id FROM wifi_item_status a,wifi_info b,wifi_item c 
// 					WHERE a.wifi_info_id=b.wifi_info_id AND b.wifi_id = c.wifi_id 
// 					AND a.passport_num='$passport' AND a.status=0 AND '$time' < DATE_ADD(b.time,INTERVAL b.expiry_day day)";
		$sql = " SELECT a.*,b.wifi_id FROM wifi_item_status a
				LEFT JOIN wifi_info b ON a.wifi_info_id=b.wifi_info_id
				WHERE a.passport_num='$passport' AND a.status=0 AND '$time' < DATE_ADD(b.time,INTERVAL b.expiry_day day)";
		$wifi_item_status = Yii::$app->db->createCommand($sql)->queryAll();
		
		return $wifi_item_status;
	}
	
	
	//把Xml内容写入数据库中
	public static function writeXMLToDB($data,$type,$time,$identififer)
	{
		$params = [
			':type' => $type,
			':data' => $data,
			':time' => $time,
			':identififer'=>$identififer,
		];
		$sql = " INSERT INTO `ibsxml_log` (`type`,`content`,`time`,`identififer`) VALUES(:type,:data,:time,:identififer)";
		Yii::$app->db->createCommand($sql,$params)->execute();
	}
	
	//记录支付记录到数据库中
	public static function  writePayLogToDB($checknum,$passportNum,$name,$amount)
	{
		$pay_time = date('Y-m-d h:i:s',time());
		$sql = " INSERT INTO `wifi_pay_log` (`check_num`,`passport_num`,`name`,`amount`,`pay_time`)
		VALUES('$checknum','$passportNum','$name','$amount','$pay_time')" ;
		Yii::$app->db->createCommand($sql)->execute();
		
		$pay_log_id = Yii::$app->db->getLastInsertID();
		return $pay_log_id;
	}
	
	
	//解析xml内容
	public static function xmlUnparsed($data)
	{
		if(self::xml_parser($data)){
			$xmlObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			return $xmlObj;
		}else {
			return false;
		}
	}
	
	
	//通过curl发送http请求，发送内容可以为 xml 或者 json
	public static function httpsRequest($url,$data='')
	{
		$curl = curl_init();								// create curl resource
		curl_setopt($curl, CURLOPT_URL, $url);				// set url
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);		//set connect time out : 10's
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);		// return the transfer as a string
		$output = curl_exec($curl);							// $output contains the output string
		curl_close($curl);									// close curl resource to free up system resources
		return $output;
	}
	
	//通过查找数据库获得url地址
	public static function selectUrl($name)
	{
		$sql = "SELECT url FROM wifi_url_params WHERE name='$name'";
		$url = Yii::$app->db->createCommand($sql)->queryOne()['url'];
		return $url;
	}
	
	//通过数据库获取portal认证时所需要的参数
	public static function getParamsOfPortal()
	{
		$sql = " SELECT params_key,params_value FROM wifi_wlan_params ";
		$params = Yii::$app->db->createCommand($sql)->queryAll();
		return $params;
	}
	
	//判断是不是xml格式
	public static function xml_parser($str){
		$xml_parser = xml_parser_create();
		if(!xml_parse($xml_parser,$str,true)){
			xml_parser_free($xml_parser);
			return false;
		}else {
			return true;
		}
	}
	
}