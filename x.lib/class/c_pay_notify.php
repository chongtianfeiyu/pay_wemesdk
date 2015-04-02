<?php

class c_pay_notify implements i_dispatch {

    function ini($_v_cmd) {
        switch ($_v_cmd) {
            case 300: {                    $r = $this->ini_alipay_notify();                    break;                } //支付宝异步通知处理
            case 303: {                    $r = $this->ini_yeepay_notify();                    break;                } //易宝支付异步通知回调
            case 304: {                    $r = $this->ini_pp_pay();                           break;                } //pp订单状态异步回传
            case 361: {                    $r = $this->ini_pp_pay_status();                    break;                } //pp订单状态轮循
            default : {                    $r = $this->ini_error();                    break;                }
        }
        return $r;
    }

    function ini_error() {
        return gf_get_error(0, 'c_pay_notify.error');
    }

    /**
     * 支付宝异步通知返回
     * @author sky 2015-03-30
     */
    function ini_alipay_notify() {
        $alipay_config = c_pay_pool::$alipay_pay;
        require_once("x.lib/lib/alipay/alipay_notify.class.php");
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            //卖家支付用户号
            $seller_id = $_POST['seller_id'];
            //卖家支付宝账号
            $seller_email = $_POST['seller_email'];
            //买家支付宝用户名
            $buyer_id = $_POST['buyer_id'];
            //买家支付宝账号
            $buyer_email = $_POST['buyer_email'];
            //通知时间
            $notify_time = $_POST['notify_time'];
            //通知类型
            $notify_type = $_POST['notify_type'];
            //通知校验ID
            $notify_id = $_POST['notify_id'];
            //购买数量
            $quantity = $_POST['quantity'];
            //交易创建时间
            $gmt_create = $_POST['gmt_create'];
            //交易付款时间
            $gmt_payment = $_POST['gmt_payment'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                $this->ini_change_alipay_order($out_trade_no, $trade_no, $trade_status, $seller_id, $seller_email, $buyer_id, $buyer_email, $notify_time, $notify_type, $notify_id, $quantity, $gmt_create, $gmt_payment);
                //注意：
                //该种交易状态只在两种情况下出现
                //1、开通了普通即时到账，买家付款成功后。
                //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                $this->ini_change_alipay_order($out_trade_no, $trade_no, $trade_status, $seller_id, $seller_email, $buyer_id, $buyer_email, $notify_time, $notify_type, $notify_id, $quantity, $gmt_create, $gmt_payment);
                //注意：
                //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";  //请不要修改或删除
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            echo "fail";
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }
    /**
     * 支付宝订单处理
     */
    function ini_change_alipay_order($out_trade_no,$trade_no,$trade_status,$seller_id,$seller_email,$buyer_id,$buyer_email,$notify_time,$notify_type,$notify_id,$quantity,$gmt_create,$gmt_payment){
        $order_status = gf_db_get_value('select order_status from pay_order where order_sn="' . $out_trade_no . '"', c_db_pool::$db_weme_pay);
        if ($order_status != 1) {
            gf_db_exec('update pay_order set order_status=1 where order_sn="' . $out_trade_no . '"', c_db_pool::$db_weme_pay);
            $alipay_sql = 'update pay_order_for_alipay set `trade_no`="' . $trade_no . '",`trade_status`="' . $trade_status . '",`seller_id`="' . $seller_id . '",`seller_email`="' . $seller_email . '",`buyer_id`="' . $buyer_id . '",`buyer_email`="' . $buyer_email . '",`notify_time`="' . $notify_time . '",`notify_type`="' . $notify_type . '",`notify_id`="' . $notify_id . '",`quantity`="' . $quantity . '",`gmt_create`="' . $gmt_create . '",`gmt_payment`="' . $gmt_payment . '" where order_sn="' . $out_trade_no . '"';
            gf_db_exec($alipay_sql, c_db_pool::$db_weme_pay);
        }
    }

    /**
     * 易宝支付异步通知回调
     */
    function ini_yeepay_notify(){
        #	解析返回参数.
        /*  $r0_Cmd = $_REQUEST['r0_Cmd'];
            $r1_Code = $_REQUEST['r1_Code'];
            $p1_MerId = $_REQUEST['p1_MerId'];
            $p2_Order = $_REQUEST['p2_Order'];
            $p3_Amt = $_REQUEST['p3_Amt'];
            $p4_FrpId = $_REQUEST['p4_FrpId'];
            $p5_CardNo = $_REQUEST['p5_CardNo'];
            $p6_confirmAmount = $_REQUEST['p6_confirmAmount'];
            $p7_realAmount = $_REQUEST['p7_realAmount'];
            $p8_cardStatus = $_REQUEST['p8_cardStatus'];
            $p9_MP = $_REQUEST['p9_MP'];
            $pb_BalanceAmt = $_REQUEST['pb_BalanceAmt'];
            $pc_BalanceAct = $_REQUEST['pc_BalanceAct'];
            $r2_TrxId=$_REQUEST['r2_TrxId'];
            $hmac = $_REQUEST['hmac'];
        */
        $return = getCallBackValue($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,
        $p9_MP,$r2_TrxId,$pb_BalanceAmt,$pc_BalanceAct,$hmac);
        #	判断返回签名是否正确（True/False）
        $bRet = CheckHmac($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,
        $p9_MP,$pb_BalanceAmt,$pc_BalanceAct,$hmac);
        #	以上代码和变量不需要修改.

        #	校验码正确.
        if($bRet){
            echo "success";
            #在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理
            if($r1_Code=="1"){
                $order_status = gf_db_get_value('select order_status from pay_order where order_sn="' . $p2_Order . '"', c_db_pool::$db_weme_pay);
                if ($order_status != 1) {
                    gf_db_exec('update pay_order set order_status=1 where order_sn="' . $p2_Order . '"', c_db_pool::$db_weme_pay);
                    $alipay_sql = 'update pay_order_for_yeepay set `r2_TrxId`="' . $r2_TrxId . '",`pb_BalanceAmt`="' . $pb_BalanceAmt . '",`pc_BalanceAct`="' . $pc_BalanceAct . '" where order_sn="' . $p2_Order . '"';
                    gf_db_exec($alipay_sql, c_db_pool::$db_weme_pay);
                }
                /*
                echo "<br>支付成功!";
                echo "<br>商户订单号:".$p2_Order;
                echo "<br>支付金额:".$p3_Amt;*/
                exit;
    	   } else if($r1_Code=="2"){
    	       $order_status = gf_db_get_value('select order_status from pay_order where order_sn="' . $p2_Order . '"', c_db_pool::$db_weme_pay);
    	       if ($order_status != 0) {
    	           gf_db_exec('update pay_order set order_status=0 where order_sn="' . $p2_Order . '"', c_db_pool::$db_weme_pay);
    	           $alipay_sql = 'update pay_order_for_yeepay set `r2_TrxId`="' . $r2_TrxId . '" where order_sn="' . $p2_Order . '"';
    	           gf_db_exec($alipay_sql, c_db_pool::$db_weme_pay);
    	       }
    	       /*
            	echo "<br>支付失败!";
            	echo "<br>商户订单号:".$p2_Order;*/
            	exit;
           }
		}else{
        	$sNewString = getCallbackHmacString($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,
        	$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct);
        	echo "<br>localhost:".$sNewString;
        	echo "<br>YeePay:".$hmac;
            echo "<br>交易签名无效!";
		    exit;
       }

    }


    /**
     * PP异步通知返回
     * @author channing 2015-03-30
     */
    function ini_pp_pay() {
        error_reporting(~E_NOTICE);
        require_once("x.lib/lib/pppay/PaypalmSDK.php");
        /**
         * 获取信息
         */

//        $testStr = '{"v_class":"300","v_cmd":"304","merId":"2015040114","version":"v1.0","encode":"UTF-8","encType":"1","signType":"1","zipType":"0","transData":"Xm\/z8wk\/u0TAPsOG4Mzggefp5bfwclrwX0NcBalD3tNMZHIB0+wX33iytyotAAt75crlATAogj4dyosgsUiT5sJjOia6Gqarqdby7kWxBY2B+42G0U+0KfJRmaMRuLHw\/5xCSYeQ1i6XPs20iXbZXhQ0P2p1gYbEdKFi3phQ1qZCKIu8bzMHDbZe7ITnokgtJ6+2JKB12fph2VTIorQPP+fE\/fROzzPxwWi3kH8AQ5Dhj3VXGat4XgLqpFJOEAsTxlCyYJ+kpgqakkXFbxmp3xRHZK9k3zkybkmsmLPUk5BoqZXnnxG23h3KiyCxSJPmp+j\/k5uBwleVSh+sfTfnWLNBe8fKShSLIJngs7o4lCwzRnDwoWdncyeP3a7OS77afV1Ig+XXWmDDc7BebN4KuLDepQzhwQ8ApNC0LsAYQMzRIsRlBjEsCOtm3b9PXxDcL3OwwU23OAsvCrOwvqtX4u2Jl1iSRfjlFAd7GQuhbWFeUP1yzWaavJqM\/cHxu3MjZHbKNSuEA19Xy7L5Lx8\/LzTN4sYMdbdb6Db8iPmKmeryD2oLDKozbSEVRkrFa3mnS1cM2PXPieyziHqOorY4hzuw5fzdcpHqTFRjZ9473Rly9GGwpX27XvWbCopdPU9HeLK3Ki0AC3ukaTA9Yf3Djw==;oitp5yA6xb\/L9PEyDOZ64Q=="}';
//        $_REQUEST = json_decode($testStr,true);
        $encType = $_REQUEST ["encType"];
        $signType = $_REQUEST ["signType"];
        $zipType = $_REQUEST ["zipType"];
        $encode = $_REQUEST ["encode"];
        $transData = $_REQUEST ["transData"];
        $de = $_REQUEST ["de"]; //预留解签动态秘钥
//        //测试参数
//        $encType = MerConfig::ENC_TYPE;
//        $signType = MerConfig::SIGN_TYPE;
//        $zipType = MerConfig::ZIP_TYPE;
//        $encode = MerConfig::EN_CODE;
//        $key = 'x/ggNAnfrAiTg3UmJYde2p=rfcIDKiPB';
//        $de = "";
//        // $de = "CFT5aPBajGNFWzMKzhZgyvYgrv1RXRnFRS5Q5AJ+tNPWuj94lAPNlLIdYlOBIKIAuWupLtCynS1ntTfflEE1c9I87+T51xcvHvDKmmXFEPqFk59/Y2CTr7Xp9VjMaClGZWiF3i0xY1C+NbYKZdnhcFy4om9OsIblpSMBw6jr05E=";
//        $data = "<paypalm><merOrderNo>20150401042041959121</merOrderNo>"
//                . "<errorCode>111</errorCode>"
//                . "<errorMsg>111</errorMsg>"
//                . "<orderStatus>111</orderStatus>"
//                . "</paypalm></opCode>";
//        // $transData = PPSecurity::encData($data, $key, $encType, $signType, $zipType);
//        $transData = PPSecurity::encData($data, MerConfig::KEY, $encType, $signType, $zipType);
        if ($de) {
            //解签动态秘钥
            $fp = fopen(c_pay_pool::$pp_pay['private_key_path'], "r");
            $priv_key = fread($fp, 8192);
            fclose($fp);
            $pkeyid = openssl_get_privatekey($priv_key);
            $crypttext = base64_decode($de);
            $key = '';
            $ret = openssl_private_decrypt($crypttext, $key, $pkeyid, OPENSSL_PKCS1_PADDING);

            //拆包获取结果信息
            $plainData = PPSecurity::decData($transData, $key, $encType, $signType, $zipType);
            $orderResult = new XMLDocument($plainData);
            $orderResult = $orderResult->getValueAt("paypalm");
        } else {
            //拆包获取结果信息
            $orderResult = PaypalmSDK::unpackOrderResult($encType, $signType, $zipType, $encode, $transData);
        }
        if ($orderResult) {
            $merOrderNo = $orderResult->getValueAt("merOrderNo");
            $errorCode = $orderResult->getValueAt("errorCode");
            $errorMsg = $orderResult->getValueAt("errorMsg");
            $orderStatus = $orderResult->getValueAt("orderStatus");
            //服务器存储支付状态
            $field_arr = array("merId", "merOrderNo", "orderNo", "payAmt", "remark", "userId", "transTime", "bankName", "orderStatus", "errorMsg", "errorCode", "merUserId", "bindId");
            foreach ($field_arr as $v) {
                $$v = $orderResult->getValueAt($v);
            }
            if ($orderStatus == "1") {
                gf_db_exec('update pay_order set order_status=1 where order_sn="' . $merOrderNo . '"', c_db_pool::$db_weme_pay);
            } else {
                gf_db_exec('update pay_order set order_status=0 where order_sn="' . $merOrderNo . '"', c_db_pool::$db_weme_pay);
            }
            $pp_sql = 'update pay_order_for_pp set `order_no`="' . $orderNo . '",`pay_amt`="' . $payAmt . '",`remark`="' . $remark . '",`pay_id`="' . $userId . '",`trans_time`="' . $transTime . '",`order_status`="' . $orderStatus . '" ,`error_code` = "' . $errorCode . '", `error_msg` = "' . $errorMsg . '" where `order_sn` = "' . $merOrderNo . '"';

            //echo $pp_sql;
            gf_db_exec($pp_sql, c_db_pool::$db_weme_pay);
            // 异步回调应答数据
            $merRepData = PaypalmSDK::packNotifySuccess();
            echo $merRepData;
            exit();
        } else {
            echo PaypalmSDK::packNotifyFalse();
            exit();
        }
    }

}
