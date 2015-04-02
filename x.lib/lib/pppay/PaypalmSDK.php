<?php
require_once ('MerConfig.php');
require_once ('PPSecurity.php');
require_once ('XMLDocument.php');
?>
<?php

/**
 * 入口文件
 * 修改前请确认
 */
class PaypalmSDK {

	/**
	 *
	 *
	 * 订单入库
	 * 组织订单数据，加密、签名、生成支付请求
	 *
	 * @param unknown_type $merUserId
	 *        	商户用户ID，请保证在每个用户的merUserId是唯一的。
	 * @param unknown_type $merOrderNo
	 *        	商户订单号，订单通知结果以商户订单号进行匹配。
	 * @param unknown_type $userId
	 *        	手机号，用户的手机号
	 * @param unknown_type $payAmt
	 *        	金额，单位是分
	 * @param unknown_type $orderDesc
	 *        	订单描述
	 * @param unknown_type $returnUrl
	 *        	同步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $notifyUrl
	 *        	异步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $remark
	 *        	备注，商户自用字段，回调结果中原样返回。
	 */
	public static function orderSave($merUserId, $merOrderNo, $userId, $payAmt, $orderDesc, $returnUrl, $notifyUrl, $remark) {
		return PaypalmSDK::orderSaveByOpCode(MerConfig::ORDER_SAVE_OP_CODE, $merUserId, $merOrderNo, $userId, $payAmt, $orderDesc, $returnUrl, $notifyUrl, $remark, null,null,null);
	}
	
	/**
	 *
	 *
	 * 订单入库(指定交易码)
	 * 组织订单数据，加密、签名、生成支付请求
	 *
	 * @param unknown_type $opCode
	 *        	交易码
	 * @param unknown_type $merUserId
	 *        	商户用户ID，请保证在每个用户的merUserId是唯一的。
	 * @param unknown_type $merOrderNo
	 *        	商户订单号，订单通知结果以商户订单号进行匹配。
	 * @param unknown_type $userId
	 *        	手机号，用户的手机号
	 * @param unknown_type $payAmt
	 *        	金额，单位是分
	 * @param unknown_type $orderDesc
	 *        	订单描述
	 * @param unknown_type $returnUrl
	 *        	同步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $notifyUrl
	 *        	异步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $remark
	 *        	备注，商户自用字段，回调结果中原样返回。
	 * @param unknown_type $orderCardNum
	 *        	订单卡号
	 * @param unknown_type $orderIdCard
	 *        	订单身份证号
	 * @param unknown_type $orderAccName
	 *        	订单开卡姓名
	 */
	public static function orderSaveByOpCode($opCode, $merUserId, $merOrderNo, $userId, $payAmt, $orderDesc, $returnUrl, $notifyUrl, $remark, $orderCardNum, $orderIdCard, $orderAccName) {
		return PaypalmSDK::orderSaveWithMerUserInfo($opCode, $merUserId, $merOrderNo, $userId, $payAmt, $orderDesc, $returnUrl, $notifyUrl, $remark, null,null,null,null);
	}
	
	/**
	 *
	 *
	 * 订单入库(指定交易码)
	 * 组织订单数据，加密、签名、生成支付请求
	 *
	 * @param unknown_type $opCode
	 *        	交易码
	 * @param unknown_type $merUserId
	 *        	商户用户ID，请保证在每个用户的merUserId是唯一的。
	 * @param unknown_type $merOrderNo
	 *        	商户订单号，订单通知结果以商户订单号进行匹配。
	 * @param unknown_type $userId
	 *        	手机号，用户的手机号
	 * @param unknown_type $payAmt
	 *        	金额，单位是分
	 * @param unknown_type $orderDesc
	 *        	订单描述
	 * @param unknown_type $returnUrl
	 *        	同步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $notifyUrl
	 *        	异步回调地址，若每次请求都相同，请联系技术支持，维护在Paypalm，此处传null即可。
	 * @param unknown_type $remark
	 *        	备注，商户自用字段，回调结果中原样返回。
	 * @param unknown_type $orderCardNum
	 *        	订单卡号
	 * @param unknown_type $orderIdCard
	 *        	订单身份证号
	 * @param unknown_type $orderAccName
	 *        	订单开卡姓名
	 * @param unknown_type $merUserInfo
	 *        	商户用户信息（以键值对的方式存在）
	 */
	public static function orderSaveWithMerUserInfo($opCode, $merUserId, $merOrderNo, $userId, $payAmt, $orderDesc, $returnUrl, $notifyUrl, $remark, $orderCardNum, $orderIdCard, $orderAccName, $merUserInfo) {
		$timezone = "PRC";
		if (function_exists ( 'date_default_timezone_set' ))
			date_default_timezone_set ( $timezone );
		$payTime = date ( "YmdHis" );
		// echo $payTime;
		
		// 交易码
		// echo $opCode;
			
		// 组织交易数据 明文数据
		$plainData = "<opCode>" . $opCode . "</opCode><merOrderNo>" . $merOrderNo . "</merOrderNo><orderDesc>" . $orderDesc . "</orderDesc><userId>" . $userId . "</userId><payAmt>" . $payAmt . "</payAmt><merUserId>" . $merUserId . "</merUserId>";
	
		// 若返回地址非空，则进行设置
		if ($returnUrl !== null)
			$plainData = $plainData . "<returnUrl>" . $returnUrl . "</returnUrl>";
			
		// 若通知地址非空，则进行设置
		if ($notifyUrl !== null)
			$plainData = $plainData . "<notifyUrl>" . $notifyUrl . "</notifyUrl>";
	
		// 若通知地址非空，则进行设置
		if ($remark !== null)
			$plainData = $plainData . "<remark>" . $remark . "</remark>";
	
		// 若通知地址非空，则进行设置
		if ($orderCardNum !== null)
			$plainData = $plainData . "<orderCardNum>" . $orderCardNum . "</orderCardNum>";
	
		// 若通知地址非空，则进行设置
		if ($orderIdCard !== null)
			$plainData = $plainData . "<orderIdCard>" . $orderIdCard . "</orderIdCard>";
	
		// 若通知地址非空，则进行设置
		if ($orderAccName !== null)
			$plainData = $plainData . "<orderAccName>" . $orderAccName . "</orderAccName>";
		
		//商户用户信息
		if($merUserInfo!== null)
		{
			$merUserInfoCount = count($merUserInfo);
			if($merUserInfoCount>0)
			{
				$plainData = $plainData . "<us>";
				foreach($merUserInfo as $k=>$v){
					if($k!==null&&$v!=null){
						$plainData = $plainData . "<u><k>" . $k . "</k><v>" . $v . "</v></u>";
					}
				}
				$plainData = $plainData . "</us>";
			}
		}
	
			
		// echo "plainData:" . $plainData . "<br/>";
		// 签名、加密、压缩 交易数据，调用安全组提供的接口
		// $plainData =
		// "<opCode>341007</opCode><merOrderNo>20121212090435</merOrderNo><orderDesc>商城购物</orderDesc><userId>13444444444</userId><payAmt>2001</payAmt><merUserId></merUserId><returnUrl>http://192.168.21.101/merReturn.php</returnUrl><notifyUrl>http://192.168.21.101/merNotify.php</notifyUrl>";
		$transData = PPSecurity::encData ( $plainData, MerConfig::KEY, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		// $transData =
		// "vdvGRb4ibG+2dkiNbVd3xyQGJoyt2AlwBa+hcs2E+bmnUooi4OOeqjXKy4iP4Q4Vwx3T81clPBXG8sqWpq3LuNH1F7+hJx3MnkvWkFPUlWpkODcbg00tLXGNo+a07JbTGitnYYxAUf6ysqeDBSQjqmc4evCkq+xDrpEGKXm8shjw3GmqXMsefHnPxanclf6q1hnjpOWLxdwge7i2qPvw9AB9EOV1BdLlJt5Ll9cz+0VRksK5rKzXUDRcERnznZP1qLuDhBn8RJ7MKNOu/ljrNP6qqZZhgY+dGZi2PyTvqI3fgmyCJWlF+yt8kgAcva6sxbtD+PcnHeZf+Qsv4O3GwAyhSGcLjygrdsbnzvqGMKa477qdG1kCFivEOGN9I3/Sz0LjwF0bSh8=;ZREvrT/MkrV1u5Gvt7wD/w==";
	
		// echo "transData:".$transData."<br/>";
		// 准备支付提交数据
		$transData = urlencode ( $transData );
		$payUrl = MerConfig::PAY_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . MerConfig::MER_ID . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData;
		//echo "payUrl:" . $payUrl . "<br/>";
		$transDataRev = HttpClient::quickGet($payUrl);
		//echo "transDataRev:" . $transDataRev . "<br/>";
		parse_str($transDataRev,$arr);
		$transData = $arr["transData"];
		$encType = $arr["encType"];
		$signType = $arr["signType"];
		$zipType = $arr["zipType"];
		$returnData = PaypalmSDK::unpackData($encType, $signType, $zipType, $encode, $transData);
		//echo "tranResult:" . $tranResult . "<br/>";
		$tranResult = $returnData->getValueAt("tranResult");
		if($tranResult=="000000")
		{
			$orderNo = $returnData->getValueAt("orderNo");
			//echo "orderNo:" . $orderNo . "<br/>";
			return $orderNo;
		}
		$resultInfo = $returnData->getValueAt("resultInfo");
		//echo "tranResult:" . $tranResult . "<br/>";
		//echo "resultInfo:" . resultInfo . "<br/>";
		return null;
	}
	
	/**
	 * 解码、解压缩、解密、验签，调用安全组接口进行处理，得到明文交易结果数据
	 * 
	 * @param unknown_type $encType        	
	 * @param unknown_type $signType        	
	 * @param unknown_type $zipType        	
	 * @param unknown_type $encode        	
	 * @param unknown_type $transData        	
	 * @return XMLDocument
	 */
	public static function unpackOrderResult($encType, $signType, $zipType, $encode, $transData) {
		return PaypalmSDK::unpackData($encType, $signType, $zipType, $encode, $transData);
	}
	


	/**
	 * 解码、解压缩、解密、验签，调用安全组接口进行处理，得到明文交易结果数据
	 *
	 * @param unknown_type $encType
	 * @param unknown_type $signType
	 * @param unknown_type $zipType
	 * @param unknown_type $encode
	 * @param unknown_type $transData
	 * @return XMLDocument
	 */
	public static function unpackData($encType, $signType, $zipType, $encode, $transData) {
		// 解码、解压缩、解密、验签，调用安全组接口进行处理，得到明文交易结果数据
		$plainData = PPSecurity::decData ( $transData, MerConfig::KEY, $encType, $signType, $zipType );
		// 解析数据
		$orderResult = new XMLDocument ( $plainData );
		$orderResult = $orderResult->getValueAt ( "paypalm" );
		return $orderResult;
	}
	
	/**
	 * 异步回调成功
	 * 
	 * @return string
	 */
	public static function packNotifySuccess() {
		$transData = PPSecurity::encData ( "success", MerConfig::KEY, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		$transData = urlencode ( $transData );
		// 组织明文返回数据（带参数）
		$merRepData = "version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . MerConfig::MER_ID . "&transData=" . $transData;
		return $merRepData;
	}
	/**
	 * 异步回调失败
	 * 
	 * @return string
	 */
	public static function packNotifyFalse() {
		$transData = PPSecurity::encData ( "false", MerConfig::KEY, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		$transData = urlencode ( $transData );
		// 组织明文返回数据（带参数）
		$merRepData = "version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . MerConfig::MER_ID . "&transData=" . $transData;
		return $merRepData;
	}


	/**
	 *
	 *
	 * 以交易码、平台订单号进行WAP支付
	 * 组织订单数据，加密、签名、生成支付请求
	 *
	 * @param unknown_type $opCode
	 *        	交易码
	 * @param unknown_type $orderNo
	 *        	平台订单号
	 */
	public static function wapPayByOrderNo($opCode, $orderNo) {
		
		// 组织交易数据 明文数据
		$plainData = "<opCode>" . $opCode . "</opCode><orderNo>" . $orderNo . "</orderNo>";
	
			
		// echo "plainData:" . $plainData . "<br/>";
		// 签名、加密、压缩 交易数据，调用安全组提供的接口
		// $plainData =
		// "<opCode>341007</opCode><merOrderNo>20121212090435</merOrderNo><orderDesc>商城购物</orderDesc><userId>13444444444</userId><payAmt>2001</payAmt><merUserId></merUserId><returnUrl>http://192.168.21.101/merReturn.php</returnUrl><notifyUrl>http://192.168.21.101/merNotify.php</notifyUrl>";
		$transData = PPSecurity::encData ( $plainData, MerConfig::KEY, MerConfig::ENC_TYPE, MerConfig::SIGN_TYPE, MerConfig::ZIP_TYPE );
		// $transData =
		// "vdvGRb4ibG+2dkiNbVd3xyQGJoyt2AlwBa+hcs2E+bmnUooi4OOeqjXKy4iP4Q4Vwx3T81clPBXG8sqWpq3LuNH1F7+hJx3MnkvWkFPUlWpkODcbg00tLXGNo+a07JbTGitnYYxAUf6ysqeDBSQjqmc4evCkq+xDrpEGKXm8shjw3GmqXMsefHnPxanclf6q1hnjpOWLxdwge7i2qPvw9AB9EOV1BdLlJt5Ll9cz+0VRksK5rKzXUDRcERnznZP1qLuDhBn8RJ7MKNOu/ljrNP6qqZZhgY+dGZi2PyTvqI3fgmyCJWlF+yt8kgAcva6sxbtD+PcnHeZf+Qsv4O3GwAyhSGcLjygrdsbnzvqGMKa477qdG1kCFivEOGN9I3/Sz0LjwF0bSh8=;ZREvrT/MkrV1u5Gvt7wD/w==";
	
		// echo "transData:".$transData."<br/>";
		// 准备支付提交数据
		$transData = urlencode ( $transData );
		$payUrl = MerConfig::PAY_URL . "?version=" . MerConfig::VERSION . "&encode=" . MerConfig::EN_CODE . "&encType=" . MerConfig::ENC_TYPE . "&signType=" . MerConfig::SIGN_TYPE . "&zipType=" . MerConfig::ZIP_TYPE . "&keyIndex=" . MerConfig::KEY_INDEX . "&merId=" . MerConfig::MER_ID . "&subMerId=" . MerConfig::SUB_MER_ID . "&transData=" . $transData;
		// echo "payUrl:" . $payUrl . "<br/>";
		return $payUrl;
	}
}
?>