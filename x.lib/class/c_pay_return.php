<?php
class c_pay_notify implements i_dispatch {

    function ini($_v_cmd) {
        switch ($_v_cmd) {
            case 200: {                     $r = $this->ini_get_pay_config();                                 break;                } //获取支付所需配置参数
            case 201: {                     $r = $this->ini_creat_pay_order();                                break;                } //创建订单
            default : {                   $r = $this->ini_error();                                      break;                  }
        }
        return $r;
    }

    function ini_error() {
        return gf_get_error(0, 'c_user.error');
    }
    /**
     * 获取支付所需配置参数
     * @param string userid 用户id
     * @param string login_token 登录token
     * @param pay_type 支付类型（
     *                          1 - 支付宝，
     *                          2 - 微信，
     *                          3 - 银联，
     *                          4 - 手机卡，
     *                          5 - mo9
     *                          ）
     */
    function ini_get_pay_config(){
        $pay_type=decrypt(gf_get_value_s("pay_type"));
        if(!$pay_type){
            return gf_get_error(200.1, "支付方式不能为空");
        }
        if($pay_type>5){
            return gf_get_error(200.2, "没有该支付方式");
        }
        switch ($pay_type){
            case 1;
                $config=c_pay_pool::$alipay_pay;
                break;
            case 2;
                $config=c_pay_pool::$weixin_pay;
                break;
            case 3;
                $config=c_pay_pool::$unionpay_pay;
                break;
            case 4;
                $config=c_pay_pool::$yeepay_pay;
                break;
            case 5;
                $config=c_pay_pool::$mo9_pay;
                break;
        }
        return gf_get_success_encrypt(200, $config);

    }

    /**
     * 创建订单
     *@param string $userid 用户ID
     *@param string $login_token 登录token
     *@param string $package_name 游戏包名
     *@param string $order_price 商品价格
     *@param string $order_pay_type 支付类型（
     *                          1 - 支付宝，
     *                          2 - 微信，
     *                          3 - 银联，
     *                          4 - 手机卡，
     *                          5 - mo9）
     *@param string $order_name  商品名称
     *@param string $order_description  商品详情
     */
    function ini_creat_pay_order(){
        $userid=decrypt(gf_get_value_s("userid"));
        $order_price=decrypt(gf_get_value_s("order_price"));
        $order_pay_type=decrypt(gf_get_value_s("order_pay_type"));
        $order_name=decrypt(gf_get_value_s("order_name"));
        $order_description=decrypt(gf_get_value_s("order_description"));
        $package_name=decrypt(gf_get_value_s("package_name"));
        if(!$userid){
            return gf_get_error(201.1, "用户ID不能为空");
        }
        if(!$order_price){
            return gf_get_error(201.2, "订单价格不能为空");
        }
        if(!$order_pay_type){
            return gf_get_error(201.3, "支付类型不能为空");
        }
        if($order_pay_type>5){
            return gf_get_error(201.7, "没有该支付方式");
        }
        if(!$order_name){
            return gf_get_error(201.4, "商品名称不能为空");
        }
        if(!$package_name){
            return gf_get_error(201.5, "包名不能为空");
        }
        $game_id=gf_db_get_value('select id from relational_game where package_name="'.$package_name.'"', c_db_pool::$db_weme_pay);
        if(!$game_id){
            return gf_get_error(201.6, "包名错误");
        }
        $order_id=gf_creat_order_id();
        $sql='insert into pay_order (`userid`,`order_sn`,`order_price`,`order_status`,`order_pay_type`,`game_id`) values ('.$userid.',"'.$order_id.'",'.$order_price.',2,'.$order_pay_type.','.$game_id.')';
        gf_db_exec($sql, c_db_pool::$db_weme_pay);
        switch ($order_pay_type){
                case 1;
                    $this->alipay_pay($userid,$order_id,$order_name,$order_description);
                    break;
                case 2;
                    $this->weixin_pay($userid,$order_id,$order_name,$order_description);
                    break;
                case 3;
                    $this->unionpay_pay($userid,$order_id,$order_name,$order_description);
                    break;
                case 4;
                    $this->yeepay_pay($userid,$order_id,$order_name,$order_description);
                    break;
                case 5;
                    $this->mo9_pay($userid,$order_id,$order_name,$order_description);
                    break;
            }
    }
    /**
     * mo9先玩后付  支付
     */
    function mo9_pay($userid,$order_id,$order_name,$order_description){
        //存入订单详情表
        $sql='insert into pay_order_for_mo9pay (`userid`,`order_sn`,`order_name`,`order_description`) values ('.$userid.',"'.$order_id.'","'.$order_name.'","'.$order_description.'")';
        gf_db_exec($sql, c_db_pool::$db_weme_pay);
        //获取配置参数
        $config=c_pay_pool::$mo9_pay;
        //https://sandbox.mo9.com/gateway/mobile.shtml?m=mobile&amount=2.00&app_id=100&currency=CNY&invoice=1348296041&item_name=Coins&lc=CN&notify_url=http://localhost/serverDemo/notifyHandler.jsp&pay_to_email=xmsbsm@163.com&payer_id=6807080265&version=2.1&sign=524d2e68b0075fcced921a602f7a5f0e

    }




}

