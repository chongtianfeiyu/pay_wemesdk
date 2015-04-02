<?php
require_once ('MerConfig.php');
require_once ('PPSecurity.php');
require_once ('XMLDocument.php');
require_once ('HttpClient.class.php');
?>
<?php

/**
 * 
 *PaypalmAPISDK
 */
class PaypalmAPISDK {
	
	/**
	 * 生成动态秘钥
	 *
	 * @return string
	 */
	public static function genKey() {
		$length = 32;
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/';
		$randKey = '';
		for($i = 0; $i < $length; $i ++) {
			$randKey .= $chars [mt_rand ( 0, strlen ( $chars ) - 1 )];
		}
		return $randKey;
	}
	
	/**
	 *
	 * @param unknown_type $plainData        	
	 * @return unknown
	 */
	public static function request($channelId,$merId, $isPreKey, $plainData) {
	//	echo "转码前plainData:" . $plainData . "<br/>";
	//	$plainData = iconv("GBK", "UTF-8", $plainData);
	//	echo "转码后plainData:" . $plainData . "<br/>";
		$digitalEnvelope = "";
		if ($isPreKey) {
			$key = MerConfig::KEY;
		} else {
			
			$key = PaypalmAPISDK::genKey ();
			$fp = fopen ( "PPFrontAPI.cer", "r" );
			$pub_key = fread ( $fp, 8192 );
			fclose ( $fp );
			openssl_get_publickey ( $pub_key );
			openssl_public_encrypt ( $key, $crypttext, $pub_key, OPENSSL_PKCS1_PADDING );
			$digitalEnvelope = base64_encode ( $crypttext );
		}
		
		// echo "plainData:" . $plainData . "<br/>";
		// $plainData =
		// "<opCode>341007</opCode><merOrderNo>20121212090435</merOrderNo><orderDesc>鍟嗗煄璐墿</orderDesc><userId>13444444444</userId><payAmt>2001</payAmt><merUserId></merUserId><returnUrl>http://192.168.21.101/merReturn.php</returnUrl><notifyUrl>http://192.168.21.101/merNotify.php</notifyUrl>";
		$transData = PPSecurity::encData ( $plainData, $key, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		// $transData =
		// "vdvGRb4ibG+2dkiNbVd3xyQGJoyt2AlwBa+hcs2E+bmnUooi4OOeqjXKy4iP4Q4Vwx3T81clPBXG8sqWpq3LuNH1F7+hJx3MnkvWkFPUlWpkODcbg00tLXGNo+a07JbTGitnYYxAUf6ysqeDBSQjqmc4evCkq+xDrpEGKXm8shjw3GmqXMsefHnPxanclf6q1hnjpOWLxdwge7i2qPvw9AB9EOV1BdLlJt5Ll9cz+0VRksK5rKzXUDRcERnznZP1qLuDhBn8RJ7MKNOu/ljrNP6qqZZhgY+dGZi2PyTvqI3fgmyCJWlF+yt8kgAcva6sxbtD+PcnHeZf+Qsv4O3GwAyhSGcLjygrdsbnzvqGMKa477qdG1kCFivEOGN9I3/Sz0LjwF0bSh8=;ZREvrT/MkrV1u5Gvt7wD/w==";
		
		// echo "transData:".$transData."<br/>";
		$transData = urlencode ( $transData );
		if($isPreKey){
			if($channelId == "api"){
				$payUrl = MerConfig::PAY_API_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
			}
			else if($channelId == "app" || $channelId == "wap"){
				$payUrl = MerConfig::PAY_APP_WAP_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
			}
			else if($channelId == "app_credit" || $channelId == "wap_credit"){
				$payUrl = MerConfig::PAY_APP_WAP_CREDIT_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
			}
			
			else {
		//		echo "channelId为空或者输入错误";
			}
		}
		else {
			$payUrl = MerConfig::PAY_API_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
		}
		
		 echo "payUrl:" . $payUrl . "<br/>";
		$transDataRev = HttpClient::quickGet ( $payUrl );
		echo "transDataRev:" . $transDataRev . "<br/>";
		parse_str ( $transDataRev, $arr );
		if (! $isPreKey) {
			$digitalEnvelope = $arr ["de"];
		}
	//	echo "digitalEnvelope:" . $digitalEnvelope . "<br/>";
		$transData = $arr ["transData"];
		$encType = $arr ["encType"];
		$signType = $arr ["signType"];
		$zipType = $arr ["zipType"];
		if ($transData != null) {
			if (! $isPreKey) {
				$fp = fopen ( 'PPFrontAPIKey.pem', "r" );
				$priv_key = fread ( $fp, 8192 );
				fclose ( $fp );
				$pkeyid = openssl_get_privatekey ( $priv_key );
				$crypttext = base64_decode ( $digitalEnvelope );
				$key = '';
				$ret = openssl_private_decrypt ( $crypttext, $key, $pkeyid, OPENSSL_PKCS1_PADDING );
			//	echo "key:" . $key . "<br/>";
			}
			
			$returnData = PaypalmAPISDK::unpackData ( $key, $encType, $signType, $zipType, $encode, $transData );
		}
		
		return $returnData;
	}
	
	/**
	 * 生成 wap url
	 *
	 * @return string
	 */
	public static function getRequestUrl($channelId,$merId, $plainData) {
		$key = MerConfig::KEY;
		$transData = PPSecurity::encData ( $plainData, $key, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		if($channelId == "wap_credit"){
			$payUrl = MerConfig::PAY_APP_WAP_CREDIT_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
		}
		else {
			$payUrl = MerConfig::PAY_APP_WAP_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . $merId . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData . "&de=" . $digitalEnvelope;
		}
		return $payUrl;
	}
	
	/**
	 * 拆包
	 *
	 * @param unknown_type $encType        	
	 * @param unknown_type $signType        	
	 * @param unknown_type $zipType        	
	 * @param unknown_type $encode        	
	 * @param unknown_type $transData        	
	 * @return XMLDocument
	 */
	public static function unpackOrderResult($encType, $signType, $zipType, $encode, $transData) {
		return PaypalmAPISDK::unpackData ( MerConfig::KEY, $encType, $signType, $zipType, $encode, $transData );
	}
	
	/**
	 * 拆包
	 *
	 * @param unknown_type $key        	
	 * @param unknown_type $encType        	
	 * @param unknown_type $signType        	
	 * @param unknown_type $zipType        	
	 * @param unknown_type $encode        	
	 * @param unknown_type $transData        	
	 * @return XMLDocument
	 */
	public static function unpackData($key, $encType, $signType, $zipType, $encode, $transData) {
		$plainData = PPSecurity::decData ( $transData, $key, $encType, $signType, $zipType );
	//	echo "I love you,my wife!";
	//	echo $plainData;
		$orderResult = new XMLDocument ( $plainData );
		$orderResult = $orderResult->getValueAt ( "paypalm" );
		//echo  $orderResult;
		return $orderResult;
	}
	
	/**
	 * 加密生成 异步回调应答数据
	 *
	 * @return string
	 */
	public static function packNotifySuccess() {
		$transData = PPSecurity::encData ( "success", MerConfig::KEY, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		$transData = urlencode ( $transData );
		$merRepData = "version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . MerConfig::MER_ID . "&transData=" . $transData;
		return $merRepData;
	}
}
?>