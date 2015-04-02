<?php

class c_pay implements i_dispatch
{

    function ini($_v_cmd)
    {
        switch ($_v_cmd) {
            case 200:{                    $r = $this->ini_get_pay_config();                     break;                } // 获取支付所需配置参数
            case 201:{                    $r = $this->ini_creat_pay_order();                    break;                } // 创建订单
            case 202:{                    $r = $this->ini_user_get_unfinish_order();            break;                } // 获取用户未完成订单
            case 203:{                    $r = $this->ini_inquiry_order();                      break;                } // 查询订单交易状态
            case 204:{                    $r = $this->ini_confirm_order();                      break;                } // 确认订单状态
            case 205:{                    $r = $this->ini_two_confirm_order();                  break;                } // 二次确认订单状态
            case 206:{                    $r = $this->ini_client_notify_order();                break;                } // 客户端通知服务器订单状态
            default: {
                    $r = $this->ini_error();
                    break;
                }
        }
        return $r;
    }

    function ini_error()
    {
        return gf_get_error(0, 'c_pay.error');
    }

    /**
     * 获取支付所需配置参数
     *
     * @param  string userid 用户id
     * @param string login_token 登录token
     * @param    pay_type 支付类型（
     *            1 - 支付宝，
     *            2 - 财付通，
     *            3 - 银联，
     *            4 - 手机卡（易宝支付），
     *            5 - PP钱包）
     *            ）
     * @author sky 2015-03-28
     */
    function ini_get_pay_config()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        $pay_type = $param["pay_type"];
        if (! $pay_type) {
            return gf_get_error(200.1, "支付方式不能为空");
        }
        if ($pay_type > 5) {
            return gf_get_error(200.2, "没有该支付方式");
        }
        switch ($pay_type) {
            case 1:
                $config = c_pay_pool::$alipay_pay;
                break;
            case 2:
                $config = c_pay_pool::$tencent_pay;
                break;
            case 3:
                $config = c_pay_pool::$unionpay_pay;
                break;
            case 4:
                $config = c_pay_pool::$yeepay_pay;
                break;
            case 5:
                $config = c_pay_pool::$pp_pay;
                break;
        }
        return gf_get_success_encrypt(200, $config);
    }

    /**
     * 获取用户完成未通知客户端的订单
     *
     * @param int $userid     用户ID
     * @param string $login_token        登录token
     * @param string $package_name     游戏包名
     * @return json(订单列表)
     * @author sky 2015-03-30
     */
    function ini_user_get_unfinish_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (! $userid = $param["userid"]) {
            return gf_get_error(202.1, "用户ID不能为空");
        }
        if (! $package_name = $param["package_name"]) {
            return gf_get_error(202.2, "包名不能为空");
        }
        $game_id = gf_db_get_value('select id from relational_game where package_name="' . $package_name . '"', c_db_pool::$db_weme_pay);
        if (! $game_id) {
            return gf_get_error(202.3, "包名错误");
        }
        $sql = 'select adate,order_sn,game_order_sn from pay_order where userid=' . $userid . ' and confirm_status=0 and order_status=1 and game_id=' . $game_id;
        $order = array();
        foreach (gf_db_query($sql, c_db_pool::$db_weme_pay) as $key => $val) {
            $order[$key]["adate"] = $val->get("adate");
            $order[$key]["order_sn"] = $val->get("order_sn");
            $order[$key]["game_order_sn"] = $val->get("game_order_sn");
        }
        return gf_get_success_encrypt(202, array_values($order));
    }

    /**
     * 查询订单交易状态
     *
     * @param int $userid     用户ID
     * @param string $login_token     登录token
     * @param string $package_name        游戏包名
     * @param string $order_sn     订单号
     * @author sky 2015-03-30
     */
    function ini_inquiry_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (! $userid = $param["userid"]) {
            return gf_get_error(203.1, "用户ID不能为空");
        }
        if (! $package_name = $param["package_name"]) {
            return gf_get_error(203.2, "包名不能为空");
        }
        if (! $order_sn = $param["order_sn"]) {
            return gf_get_error(203.3, "订单号不能为空");
        }
        $game_id = gf_db_get_value('select id from relational_game where package_name="' . $package_name . '"', c_db_pool::$db_weme_pay);
        if (! $game_id) {
            return gf_get_error(203.4, "包名错误");
        }
        $order_info = array();
        if (gf_db_get_value('select order_status from pay_order where order_sn="' . $order_sn . '" and userid=' . $userid . ' and game_id=' . $game_id, c_db_pool::$db_weme_pay) == 1) {
            gf_db_exec('update pay_order set confirm_code=100 where order_sn="' . $order_sn . '" and userid=' . $userid . ' and game_id=' . $game_id, c_db_pool::$db_weme_pay);
        }
        $sql = 'select order_sn,order_status,confirm_code,game_order_sn from pay_order where userid=' . $userid . ' and game_id=' . $game_id . ' and order_sn="' . $order_sn . '"';
        $order = gf_db_query($sql, c_db_pool::$db_weme_pay);
        $order_info = $order->get_array();
        if (! empty($order_info)) {
            $order_info = $order_info[0];
            foreach ($order_info as $k => $v) {
                if ($order_info[$k] == null) {
                    $order_info[$k] = "";
                }
                if (is_numeric($k)) {
                    unset($order_info[$k]);
                }
            }
        }
        return gf_get_success_encrypt(203, $order_info);
    }

    /**
     * 确认订单状态
     *
     * @param int $userid      用户ID
     * @param string $login_token      登录token
     * @param string $order_sn      订单号
     * @param int $confirm_code  二次确认状态代号
     * @author sky 2015-03-30
     */
    function ini_confirm_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (! $userid = $param["userid"]) {
            return gf_get_error(204.1, "用户ID不能为空");
        }
        if (! $order_sn = $param["order_sn"]) {
            return gf_get_error(204.2, "订单号不能为空");
        }
        if (! $confirm_code = $param["confirm_code"]) {
            return gf_get_error(204.3, "二次确认状态码不能为空");
        }
        $db_confirm_code = gf_db_get_value('select confirm_code from pay_order where userid=' . $userid . ' and order_sn="' . $order_sn . '"', c_db_pool::$db_weme_pay);
        if (($db_confirm_code + 1) != $confirm_code) {
            return gf_get_error(204.4, "二次确认状态码错误");
        } else {
            gf_db_exec('update pay_order set confirm_code=101 where order_sn="' . $order_sn . '" and userid=' . $userid, c_db_pool::$db_weme_pay);
            $sql = 'select order_sn,order_status,confirm_code,game_order_sn from pay_order where userid=' . $userid . ' and order_sn="' . $order_sn . '"';
            $order = gf_db_query($sql, c_db_pool::$db_weme_pay);
            $order_info = $order->get_array();
            if (! empty($order_info)) {
                $order_info = $order_info[0];
                foreach ($order_info as $k => $v) {
                    if ($order_info[$k] == null) {
                        $order_info[$k] = "";
                    }
                    if (is_numeric($k)) {
                        unset($order_info[$k]);
                    }
                }
            }
            return gf_get_success_encrypt(204, $order_info);
        }
    }

    /**
     * 二次确认订单状态
     *
     * @param int $userid    用户ID
     * @param string $login_token       登录token
     * @param string $order_sn      订单号
     * @param int $confirm_code  二次确认状态代号
     * @author sky 2015-03-30
     */
    function ini_two_confirm_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (! $userid = $param["userid"]) {
            return gf_get_error(205.1, "用户ID不能为空");
        }
        if (! $order_sn = $param["order_sn"]) {
            return gf_get_error(205.2, "订单号不能为空");
        }
        if (! $confirm_code = $param["confirm_code"]) {
            return gf_get_error(205.3, "二次确认状态码不能为空");
        }
        $db_confirm_code = gf_db_get_value('select confirm_code from pay_order where userid=' . $userid . ' and order_sn="' . $order_sn . '"', c_db_pool::$db_weme_pay);
        if (($db_confirm_code + 1) != $confirm_code) {
            return gf_get_error(205.4, "二次确认状态码错误");
        } else {
            gf_db_exec('update pay_order set confirm_code=102,confirm_status=1 where order_sn="' . $order_sn . '" and userid=' . $userid, c_db_pool::$db_weme_pay);
            $sql = 'select order_sn,order_status,confirm_code,game_order_sn from pay_order where userid=' . $userid . ' and order_sn="' . $order_sn . '"';
            $order = gf_db_query($sql, c_db_pool::$db_weme_pay);
            $order_info = $order->get_array();
            if (! empty($order_info)) {
                $order_info = $order_info[0];
                foreach ($order_info as $k => $v) {
                    if ($order_info[$k] == null) {
                        $order_info[$k] = "";
                    }
                    if (is_numeric($k)) {
                        unset($order_info[$k]);
                    }
                }
            }
            return gf_get_success_encrypt(205, $order_info);
        }
    }

    /**
     * 创建订单
     *
     * @param string $userid     用户ID
     * @param string $login_token       登录token
     * @param string $package_name      游戏包名
     * @param string $game_order_sn     游戏订单号
     * @param string $order_price       商品价格
     * @param string $order_pay_type
     *            支付类型（
     *            1 - 支付宝，
     *            2 - 微信，
     *            3 - 银联，
     *            4 - 手机卡（易宝支付），
     *            5 - PP钱包）
     * @param string $order_name           商品名称
     * @param string $order_description    商品详情
     * @author sky 2015-03-28
     */
    function ini_creat_pay_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (!$userid = $param["userid"]) {
            return gf_get_error(201.1, "用户ID不能为空");
        }
        if (!$order_price = $param["order_price"]) {
            return gf_get_error(201.2, "订单价格不能为空");
        }
        if ($order_price <= 0) {
            return gf_get_error(201.8, "订单价格错误");
        }
        if (!$order_pay_type = $param["order_pay_type"]) {
            return gf_get_error(201.3, "支付类型不能为空");
        }
        if ($order_pay_type > 6) {
            return gf_get_error(201.7, "没有该支付方式");
        }
        if (! $order_name = $param["order_name"]) {
            return gf_get_error(201.4, "商品名称不能为空");
        }
        if (! $package_name = $param["package_name"]) {
            return gf_get_error(201.5, "包名不能为空");
        }
        $game_id = gf_db_get_value('select id from relational_game where package_name="' . $package_name . '"', c_db_pool::$db_weme_pay);
        if (!$game_id) {
            return gf_get_error(201.6, "包名错误");
        }
        $game_order_sn = "";
        if (isset($param["game_order_sn"])) {
            $game_order_sn = $param["game_order_sn"];
        }
        if (isset($param["order_description"])){
            $order_description = $param["order_description"];
        }
        $order_price = $order_price / 100;
        $order_id = gf_creat_order_id();
        $sql = 'insert into pay_order (`userid`,`order_sn`,`order_price`,`order_status`,`order_pay_type`,`game_id`,`game_order_sn`) values (' . $userid . ',"' . $order_id . '",' . $order_price . ',2,' . $order_pay_type . ',' . $game_id . ',"' . $game_order_sn . '")';
        gf_db_exec($sql, c_db_pool::$db_weme_pay);
        switch ($order_pay_type) {
            case 1:
                $r = $this->alipay_pay($userid, $order_id, $order_price, $game_order_sn, $order_name, $order_description);
                break;
            case 2:
                $r = $this->tencent_pay($userid, $order_id, $game_order_sn, $order_name, $order_description);
                break;
            case 3:
                $r = $this->unionpay_pay($userid, $order_id, $game_order_sn, $order_name, $order_description);
                break;
            case 4:
                $r = $this->yeepay_pay($userid, $order_id, $game_order_sn, $order_name, $order_description, $param);
                break;
            case 5:
                $r = $this->pp_pay($userid, $order_id, $order_name, $order_description);
                break;
        }
        return $r;
    }

    /**
     * 支付宝订单处理
     *
     * @param int $userid
     * @param string $order_id                订单号
     * @param string $order_price             订单价格
     * @param string $game_order_sn           游戏订单号
     * @param string $order_name              商品名称
     * @param string $order_description       商品描述
     * @author sky 2015-03-30
     */
    function alipay_pay($userid, $order_id, $order_price, $game_order_sn, $order_name, $order_description = "")
    {
        $sql = 'insert into pay_order_for_alipay (`userid`,`order_sn`,`order_name`,`order_description`) values (' . $userid . ',"' . $order_id . '","' . $order_name . '","' . $order_description . '")';
        gf_db_exec($sql, c_db_pool::$db_weme_pay);
        $config = c_pay_pool::$alipay_pay;
        $order = array();
        $order["userid"] = $userid;
        $order["order_sn"] = $order_id;
        $order["game_order_sn"] = $game_order_sn;
        $order["order_param"] = 'partner="' . $config["partner"] . '"&out_trade_no="' . $order_id . '"&subject="' . $order_name . '"&body="' . $order_description . '"&total_fee="' . $order_price . '"&service="mobile.securitypay.pay"&_input_charset="UTF-8"&notify_url="' . $config["notify_url"] . '"&payment_type="1"&seller_id="' . $config["seller"] . '"&it_b_pay="30m"';
        $order["partner"] = $config["partner"];
        $order["seller"] = $config["seller"];
        $order["rsaPrivate"] = $config["rsaPrivate"];
        $order["rsaPublic"] = $config["rsaPrivate"];
        return gf_get_success_encrypt(201, $order);
    }

    function pp_pay($userid, $order_id, $order_name, $order_description)
    {
        $date = date("Y-m-d H:i:s");
        $sql = 'insert into pay_order_for_pp (`user_id`,`order_name`,`order_description`,`order_sn` , `order_status`) values ("' . $userid . '","' . $order_name . '","' . $order_description . '","' . $order_id . '","0")';
        $r = gf_db_exec($sql, c_db_pool::$db_weme_pay);
        if ($r) {
            return (gf_get_success_encrypt(201, array(
                'merchantId' => c_pay_pool::$pp_pay['merchantId'],
                'notify_url' => c_pay_pool::$pp_pay['notify_url'],
                'order_sn' => $order_id,
                'adate' => $date
            )));
        }
        return gf_get_error(201.11, "创建订单失败");
    }

    /**
     * 易宝支付订单处理
     *
     * @param int $userid                 用户ID
     * @param string $order_id                 订单号
     * @param string $game_order_sn                 游戏订单号
     * @param string $order_name                 商品名称
     * @param string $order_description              商品描述
     * @param array $param
     * @return json
     */
    function yeepay_pay($userid, $order_id, $game_order_sn, $order_name, $order_description, $param)
    {
        if (! $card_no = $param["card_no"]) {
            return gf_get_error(201.7, "充值卡号不能为空");
        }
        if (! $card_password = $param["card_password"]) {
            return gf_get_error(201.8, "充值卡密不能为空");
        }
        if (! $card_amt = $param["card_amt"]) {
            return gf_get_error(201.9, "充值卡面额不能为空");
        }
        if (! $card_type = $param["card_type"]) {
            return gf_get_error(201.10, "充值卡类型不能为空");
        }
        $config = c_pay_pool::$yeepay_pay;
        // 商户订单号.提交的订单号必须在自身账户交易中唯一.
        $p2_Order = $order_id;
        // 支付卡面额
        $p3_Amt = ($param["order_price"] / 100);
        // 是否较验订单金额
        $p4_verifyAmt = 'true';
        // 产品名称
        $p5_Pid = $order_name;
        iconv("UTF-8", "GBK//TRANSLIT", $p5_Pid);
        // 产品类型
        $p6_Pcat = $order_name;
        iconv("UTF-8", "GBK//TRANSLIT", $p6_Pcat);
        // 产品描述
        $p7_Pdesc = $order_description;
        iconv("UTF-8", "GBK//TRANSLIT", $p7_Pdesc);
        // 商户接收交易结果通知的地址,易宝支付主动发送支付结果(服务器点对点通讯).通知会通过HTTP协议以GET方式到该地址上.
        $p8_Url = $config["notify_url"];
        // 临时信息
        $pa_MP = "";
        // iconv("UTF-8","GB2312//TRANSLIT",$_POST['pa_MP']);
        // 卡面额
        $pa7_cardAmt = $card_amt;
        // 支付卡序列号.
        $pa8_cardNo = $card_no;
        // 支付卡密码.
        $pa9_cardPwd = $card_password;
        // 支付通道编码
        switch ($card_type) {
            case 1:
                $pd_FrpId = 'SZX';
                break;
            case 2:
                $pd_FrpId = 'UNICOM';
                break;
            case 3:
                $pd_FrpId = 'TELECOM';
                break;
        }
        // 应答机制
        $pr_NeedResponse = "1";
        // 用户唯一标识
        $pz_userId = $userid;
        // 用户的注册时间
        $userRegTime = gf_db_get_value('select adate from user where id=' . $userid, c_db_pool::$db_weme_pay);
        $pz1_userRegTime = $userRegTime;

        // 记录订单详情
        $sql = 'insert into pay_order_for_yeepay (`userid`,`order_sn`,`order_name`,`order_description`,`card_no`,`card_password`,`card_amt`,`card_type`) values (' . $userid . ',"' . $order_id . '","' . $order_name . '","' . $order_description . '","' . $card_no . '","' . $card_password . '","' . $card_amt . '","' . $card_type . '")';
        gf_db_exec($sql, c_db_pool::$db_weme_pay);
        // 非银行卡支付专业版测试时调用的方法，在测试环境下调试通过后，请调用正式方法annulCard
        // 两个方法所需参数一样，所以只需要将方法名改为annulCard即可
        // 测试通过，正式上线时请调用该方法
        return $this->annulCard($p2_Order, $p3_Amt, $p4_verifyAmt, $p5_Pid, $p6_Pcat, $p7_Pdesc, $p8_Url, $pa_MP, $pa7_cardAmt, $pa8_cardNo, $pa9_cardPwd, $pd_FrpId, $pz_userId, $pz1_userRegTime);
    }

    /**
     * 客户端通知服务器订单状态
     *
     * @param int $userid
     * @param string $login_token     登录token
     * @param string $order_id        订单号
     * @param int $order_status       订单状态 0 失败 1成功 2 未完成
     */
    function ini_client_notify_order()
    {
        $param = gf_get_param(gf_get_value_s("param"));
        if (! $order_id = $param["order_id"]) {
            return gf_get_error(206.1, "订单号不能为空");
        }
        if (! isset($param["order_status"])) {
            return gf_get_error(206.2, "订单状态不能为空");
        }
        $order_status = $param["order_status"];
        gf_db_exec('update pay_order set order_status=' . $order_status . ' where order_sn="' . $order_id . '"', c_db_pool::$db_weme_pay);
        $sql = 'select order_sn,order_status,game_order_sn from pay_order where order_sn="' . $order_id . '"';
        $order = gf_db_query($sql, c_db_pool::$db_weme_pay);
        $order_info = array();
        $order_info = $order->get_array();
        if (! empty($order_info)) {
            $order_info = $order_info[0];
            foreach ($order_info as $k => $v) {
                if ($order_info[$k] == null) {
                    $order_info[$k] = "";
                }
                if (is_numeric($k)) {
                    unset($order_info[$k]);
                }
            }
        }
        return gf_get_success_encrypt(206, $order_info);
    }

    /**
     * 易宝支付订单支付提交
     *
     * @param string $p2_Order             商户订单号
     * @param float $p3_Amt                订单金额
     * @param boolean $p4_verifyAmt        是否较验订单金额
     * @param string $p5_Pid               产品名称
     * @param string $p6_Pcat              产品类型/商品名称
     * @param string $p7_Pdesc             产品描述
     * @param string $p8_Url               异步回调地址
     * @param string $pa_MP                临时信息
     * @param float $pa7_cardAmt           卡面额
     * @param string $pa8_cardNo           卡号
     * @param string $pa9_cardPwd          卡密
     * @param string $pd_FrpId             支付通道编码
     * @param int $pz_userId               用户ID
     * @param datetime $pz1_userRegTime    用户注册时间
     * @return string
     */
    function annulCard($p2_Order, $p3_Amt, $p4_verifyAmt, $p5_Pid, $p6_Pcat, $p7_Pdesc, $p8_Url, $pa_MP, $pa7_cardAmt, $pa8_cardNo, $pa9_cardPwd, $pd_FrpId, $pz_userId, $pz1_userRegTime)
    {
        $config = c_pay_pool::$yeepay_pay;
        // 非银行卡支付专业版支付请求，固定值 "ChargeCardDirect".
        $p0_Cmd = "ChargeCardDirect";

        // 应答机制.为"1": 需要应答机制;为"0": 不需要应答机制.
        $pr_NeedResponse = "1";

        // 调用签名函数生成签名串
        $hmac = getReqHmacString($p0_Cmd, $p2_Order, $p3_Amt, $p4_verifyAmt, $p5_Pid, $p6_Pcat, $p7_Pdesc, $p8_Url, $pa_MP, $pa7_cardAmt, $pa8_cardNo, $pa9_cardPwd, $pd_FrpId, $pr_NeedResponse, $pz_userId, $pz1_userRegTime);

        // 进行加密串处理，一定按照下列顺序进行
        $params = array(
            // 加入业务类型
            'p0_Cmd' => $p0_Cmd,
            // 加入商家ID
            'p1_MerId' => $config["p1_MerId"],
            // 加入商户订单号
            'p2_Order' => $p2_Order,
            // 加入支付卡面额
            'p3_Amt' => $p3_Amt,
            // 加入是否较验订单金额
            'p4_verifyAmt' => $p4_verifyAmt,
            // 加入产品名称
            'p5_Pid' => $p5_Pid,
            // 加入产品类型
            'p6_Pcat' => $p6_Pcat,
            // 加入产品描述
            'p7_Pdesc' => $p7_Pdesc,
            // 加入商户接收交易结果通知的地址
            'p8_Url' => $p8_Url,
            // 加入临时信息
            'pa_MP' => $pa_MP,
            // 加入卡面额组
            'pa7_cardAmt' => $pa7_cardAmt,
            // 加入卡号组
            'pa8_cardNo' => $pa8_cardNo,
            // 加入卡密组
            'pa9_cardPwd' => $pa9_cardPwd,
            // 加入支付通道编码
            'pd_FrpId' => $pd_FrpId,
            // 加入应答机制
            'pr_NeedResponse' => $pr_NeedResponse,
            // 加入校验码
            'hmac' => $hmac,
            // 用户唯一标识
            'pz_userId' => $pz_userId,
            // 用户的注册时间
            'pz1_userRegTime' => $pz1_userRegTime
        );
        $pageContents = HttpClient::quickPost($config["reqURL_SNDApro"], $params);
        // $pageContents = gf_vpost($config["reqURL_SNDApro"], $params,false);

        $result = explode("\n", $pageContents);
        $r0_Cmd = ""; // 业务类型
        $r1_Code = ""; // 支付结果
        $r2_TrxId = ""; // 易宝支付交易流水号
        $r6_Order = ""; // 商户订单号
        $rq_ReturnMsg = ""; // 返回信息
        $hmac = ""; // 签名数据
        $unkonw = ""; // 未知错误

        for ($index = 0; $index < count($result); $index ++) { // 数组循环
            $result[$index] = trim($result[$index]);
            if (strlen($result[$index]) == 0) {
                continue;
            }
            $aryReturn = explode("=", $result[$index]);
            $sKey = $aryReturn[0];
            $sValue = $aryReturn[1];
            if ($sKey == "r0_Cmd") { // 取得业务类型
                $r0_Cmd = $sValue;
            } elseif ($sKey == "r1_Code") { // 取得支付结果
                $r1_Code = $sValue;
            } elseif ($sKey == "r2_TrxId") { // 取得易宝支付交易流水号
                $r2_TrxId = $sValue;
            } elseif ($sKey == "r6_Order") { // 取得商户订单号
                $r6_Order = $sValue;
            } elseif ($sKey == "rq_ReturnMsg") { // 取得交易结果返回信息
                $rq_ReturnMsg = $sValue;
            } elseif ($sKey == "hmac") { // 取得签名数据
                $hmac = $sValue;
            } else {
                continue;
                // return $result[$index];
            }
        }

        // 进行校验码检查 取得加密前的字符串
        $sbOld = "";
        // 加入业务类型
        $sbOld = $sbOld . $r0_Cmd;
        // 加入支付结果
        $sbOld = $sbOld . $r1_Code;
        // 加入易宝支付交易流水号
        // $sbOld = $sbOld.$r2_TrxId;
        // 加入商户订单号
        $sbOld = $sbOld . $r6_Order;
        // 加入交易结果返回信息
        $sbOld = $sbOld . $rq_ReturnMsg;
        $sNewString = HmacMd5($sbOld, $config["merchantKey"]);
        logstr($r6_Order, $sbOld, HmacMd5($sbOld, $config["merchantKey"]), $config["merchantKey"]);
        // 校验码正确
        if ($sNewString == $hmac) {
            if ($r1_Code == "1") {
                echo "<br>提交成功!" . $rq_ReturnMsg;
                echo "<br>商户订单号:" . $r6_Order . "<br>";
                // echo generationTestCallback($p2_Order,$p3_Amt,$p8_Url,$pa7_cardNo,$pa8_cardPwd,$pz_userId,$pz1_userRegTime);
                return;
            } elseif ($r1_Code == "2") {
                echo "<br>提交失败" . $rq_ReturnMsg;
                echo "<br>支付卡密无效!";
                return;
            } elseif ($r1_Code == "7") {
                echo "<br>提交失败" . $rq_ReturnMsg;
                echo "<br>支付卡密无效!";
                return;
            } elseif ($r1_Code == "11") {
                echo "<br>提交失败" . $rq_ReturnMsg;
                echo "<br>订单号重复!";
                return;
           } else {
                echo "<br>提交失败" . $rq_ReturnMsg;
                echo "<br>请检查后重新测试支付";
                return;
           }
        } else {
            echo "<br>localhost:" . $sNewString;
            echo "<br>YeePay:" . $hmac;
            echo "<br>交易签名无效!";
            exit();
        }
    }
}
