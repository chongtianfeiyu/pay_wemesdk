<?php
class c_user implements i_dispatch {

    function ini($_v_cmd) {
        switch ($_v_cmd) {
            case 100: {                     $r = $this->ini_user_login();                                 break;                } //登录
            case 101: {                     $r = $this->ini_user_register();                              break;                } //注册
            case 102: {                     $r = $this->ini_user_forget();                                break;                } //忘记密码
            case 103: {                     $r = $this->ini_user_change_password();                       break;                } //通过验证码找回/修改密码
            case 104: {                     $r = $this->ini_user_login_for_duoduo();                      break;                } //多多调用登录
            case 105: {                     $r = $this->ini_user_change_password_for_duoduo();            break;                } //多多调用修改密码
            case 106: {                     $r = $this->ini_user_exists_for_duoduo();                     break;                } //多多调用是否存在账号
            case 107: {                     $r = $this->ini_send_sms_for_forget();                        break;                } //发送验证码找回密码
            case 108: {                     $r = $this->ini_user_verify_phone_code();                     break;                } //验证验证码是否正确
            case 109: {                     $r = $this->ini_user_login_for_token();                       break;                } //token登录
            case 110: {                     $r = $this->ini_user_get_order();                             break;                } //获取用户充值记录

            case 111: {                     $r = $this->ini_user_forget_for_manage();                     break;                } //管理后台忘记密码
            case 112: {                     $r = $this->ini_verify_code_for_manage();                     break;                } //管理后台验证验证码
            case 113: {                     $r = $this->ini_user_change_password_for_manage();            break;                } //管理后台修改密码

            case 999: {                     $r = $this->ini_test();                                       break;                }

            default : {                   $r = $this->ini_error();                                      break;                  }
        }
        return $r;
    }

    function ini_error() {
        return gf_get_error(0, 'c_user.error');
    }
    /**
     * 登录
     * @param string phone 手机号码
     * @param string password 密码
     * @param string device_uuid 设备码
     * @param string package_name 包名
     * @return json
     * @author sky 2015-03-26
     */
    function ini_user_login(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$phone=$param["phone"]){
            return gf_get_error(100.1, "手机号码不能为空");
        }
        if(!$password=$param["password"]){
            return gf_get_error(100.2, "密码不能为空");
        }
        if(!$device_uuid=$param["device_uuid"]){
            return gf_get_error(100.3, "设备码不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(100.6, "包名不能为空");
        }
        if(!$password_db=gf_db_get_value('select password from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号,存在则记录并返回用户信息，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if($userid){
                $user_info=$this->ini_user_login_in_duoduo($phone, md5($password), $device_uuid);
                if($user_info["status"]==0){
                    $user_info=$user_info["content"]["base_info"];
                }else{
                    return gf_get_error($user_info["id"], $user_info["description"]);
                }
                /**把用户信息记录在本地**/
                if($user_info["userid"]){
                    gf_db_exec('insert into user_for_detail set `userid`='.$user_info["userid"].',`mobile`="'.$phone.'",`device_uuid`="'.$device_uuid.'",`gender`='.$user_info["gender"].',`nickname`="'.$user_info["nickname"].'",`user_avatar`="'.$user_info["pic_for_user_avatar"].'",`user_avatar_big`="'.$user_info["pic_for_user_avatar_big"].'",`user_center_background`="'.$user_info["pic_for_user_center_background"].'"', c_db_pool::$db_weme_pay);
                }
            }else{
                return gf_get_error(100.4, "账号不存在");
            }
        }else{
            $userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay);
            if($password_db!=md5($password)){
                return gf_get_error(100.5, "密码错误");
            }
        }
        $login_token=$this->ini_creat_login_token();
        gf_db_exec('update user_for_detail set `device_uuid`="'.$device_uuid.'",`login_token`="'.$login_token.'" where userid='.$userid, c_db_pool::$db_weme_pay);
        $userinfo=$this->ini_get_user_info($userid);
        /**记录登录日志**/
        gf_save_user_login_log($userid,$package_name,1,$login_token);
        return gf_get_success_encrypt(100, $userinfo);
    }
    /**
     * 通过token登录
     *@param string userid 用户ID
     *@param string token 登录token
     *@param string package_name 包名
     *@author sky 2015-03-27
     */
    function ini_user_login_for_token(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$userid=$param["userid"]){
            return gf_get_error(109.1, "手机号码不能为空");
        }
        if(!$login_token=$param["token"]){
            return gf_get_error(109.2, "密码不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(109.3, "包名不能为空");
        }
        $game_id=gf_db_get_value('select id from relational_game where package_name="'.$package_name.'"', c_db_pool::$db_weme_pay);
        if(!$game_id){
            return gf_get_error(109.4, "包名错误");
        }
        if($login_token!=gf_db_get_value('select login_token from user_for_detail where userid='.$userid, c_db_pool::$db_weme_pay)){
            return gf_get_error(109.5, "login token 错误");
        }
        if(!gf_token_timeout($userid,$login_token,$package_name)){
            return gf_get_error(109.6, "login token 已过期");
        }
        $userinfo=$this->ini_get_user_info($userid);
        /**记录登录日志**/
        gf_save_user_login_log($userid,$package_name,0,$login_token);
        return gf_get_success_encrypt(109, $userinfo);
    }
    /**
     *用户注册
     *@param string phone 手机号码
     *@param string device_uuid 设备码
     *@param string package_name 包名
     *@return json
     *@author sky 2015-03-26
     */
    function ini_user_register(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$phone=$param["phone"]){
            return gf_get_error(101.1, "手机号码不能为空");
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            return gf_get_error(101.4, '错误的手机号码');
        }
        if(!$device_uuid=$param["device_uuid"]){
            return gf_get_error(101.2, "设备码不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(101.5, "包名不能为空");
        }
        if(gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            return gf_get_error(101.3, "该手机号已注册");
        }
        /*远程验证多多账号是否存在*/
        $userid=$this->ini_user_exists_in_duoduo($phone);
        if($userid){
            return gf_get_error(101.3, "该手机号已注册");
        }
        $password=$this->ini_creat_password();
        $sql='insert into user (`username`,`password`) values ("'.$phone.'","'.md5($password).'")';
        $userid=gf_db_exec($sql, c_db_pool::$db_weme_pay);
        if($userid){
            /**发送默认密码到注册用户的手机上**/
            $login_token=$this->ini_creat_login_token();
            gf_db_exec('insert into user_for_detail set `userid`='.$userid.',`nickname`="'.$phone.'",`mobile`="'.$phone.'",`device_uuid`="'.$device_uuid.'",`is_defalut_password`=1,`defalut_password`="'.$password.'",`login_token`="'.$login_token.'"', c_db_pool::$db_weme_pay);
            $this->ini_send_sms_for_register($userid, $phone, $password);
            /**记录登录日志**/
            gf_save_user_login_log($userid,$package_name,1,$login_token);
            return gf_get_success_encrypt(101, $this->ini_get_user_info($userid));
        }else{
            return gf_get_error(101.5, "注册失败");
        }
    }
     /**
     *忘记密码找回密码
     *@param string phone 手机号码
     *@return json
     *@author sky 2015-03-27
     */
    function ini_user_forget(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$phone=$param["phone"]){
            return gf_get_error(102.1, "手机号码不能为空");
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            return gf_get_error(102.2, '错误的手机号码');
        }
        if(!$password_db=gf_db_get_value('select password from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(101.3, "账号不存在");
            }
        }else{
            $userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay);
        }
        $verify_code = mt_rand(1000, 9999);
        $verify=gf_send_sms($userid,$phone,$verify_code,1001);
        if($verify==true){
            return gf_get_success_encrypt(102, array("userid"=>$userid,"phone"=>$phone,"code"=>$verify_code));
        }else{
            return gf_get_error(102.4, $verify);
        }
    }
    /**
     * 验证验证码是否填写正确
     * @param string $phone 手机号码
     * @param string $code 验证码
     * @author sky 2015-03-27
     */
    function ini_user_verify_phone_code(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$code = $param["code"]){
            return gf_get_error(108.3, "验证码不能为空");
        }
        if(!$phone=$param["phone"]){
            return gf_get_error(108.4, "手机号码不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(108.5, "账号不存在");
            }
        }else{
            $userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay);
        }
        if ($code > 9999 || $code < 1000) {
            return gf_get_error(108.1, "验证码错误");
        }
        if (!$phone = gf_db_get_value('select phone from user_tmp_verify_phone where phone=\'' . $phone . '\' and code=\'' . $code . '\' and status=0 and type=2', c_db_pool::$db_weme_pay)) {
            return gf_get_error(108.2, "验证码错误");
        }else{
            gf_db_exec('update user_tmp_verify_phone  set status=1 where phone=' . $phone.' and type=2', c_db_pool::$db_weme_pay);
        }
        return gf_get_success_encrypt(108, array("userid"=>$userid,"phone"=>$phone,"code"=>$code));
    }


    /**
     *发送验证码找回密码
     *@param string phone 手机号码
     *@param int userid 用户id
     *@return json
     *@author sky 2015-03-27
     */
    function ini_send_sms_for_forget(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$phone=$param["phone"]){
            return gf_get_error(107.2, "手机号码不能为空");
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            return gf_get_error(107.3, '错误的手机号码');
        }
        /* 时间校验 (每次发送手机验证码间隔时间至少为30秒) */
        $betweem = 30 - 10; //seconds, 10 seconds torelance
        if ($last_time = gf_db_get_value('select adate from user_tmp_verify_phone where phone=\'' . $phone . '\' and status=0 and type=2', c_db_pool::$db_weme_pay)) {
            if (strtotime($last_time) + $betweem > time()) {
                return gf_get_error(107.4, "验证码发送太频繁了，亲~~");
            }
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(107.6, "账号不存在");
            }
        }else{
            $userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay);
        }
        $verify_code = mt_rand(1000, 9999);
        $verify=gf_send_sms($userid,$phone,$verify_code,1001);
        if($verify==true){
            return gf_get_success_encrypt(107, array("userid"=>$userid,"phone"=>$phone,"code"=>$verify_code));
        }else{
            return gf_get_error(107.5, $verify);
        }
    }
    /**
     * 修改密码
     * @param int $userid 用户ID
     * @param string phone 手机号码
     * @param string password 新密码
     * @return json
     * @author sky 2015-03-27
     */
    function ini_user_change_password(){
        $param=gf_get_param(gf_get_value_s("param"));
        if(!$userid=$param["userid"]){
            return gf_get_error(103.1, "userid不能为空");
        }
        if(!$phone=$param["phone"]){
            return gf_get_error(103.2, "手机号码不能为空");
        }
        if(!$password=$param["password"]){
            return gf_get_error(103.3, "密码不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(103.4, "账号不存在");
            }else{
                $result=$this->ini_user_changpassword_in_duoduo($userid, $phone, $password);
                if($result["status"]==0){
                    $result["content"]["base_info"]["phone"]=$phone;
                    unset($result["content"]["base_info"]["mobile"]);
                    return gf_get_success_encrypt(103, $result["content"]["base_info"]);
                }else{
                    return gf_get_error($result["id"], $result["description"]);
                }
            }
        }else{
            /**修改本地账户密码**/
            gf_db_exec('update user set password="'.md5($password).'" where username=' . $phone.' and id='.$userid, c_db_pool::$db_weme_pay);
            gf_db_exec('update user_for_detail set is_defalut_password=0,defalut_password="" where userid='.$userid, c_db_pool::$db_weme_pay);
            return gf_get_success_encrypt(103, array("phone"=>$phone,"userid"=>$userid,"password"=>$password));
        }
    }

    /**
     *多多调用登录
     *@param string phone 手机号码
     *@param string password 密码
     *@return json
     *@author sky 2015-03-26
     */
    function ini_user_login_for_duoduo(){
        $phone=gf_get_value_s("phone");
        $password=gf_get_value_s("password");
        if(!$phone){
            return gf_get_error(104.1, "账号不能为空");
        }
        if(!$password){
            return gf_get_error(104.2, "密码不能为空");
        }
        if(!$password_db=gf_db_get_value('select password from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            return gf_get_error(104.3, "账号不存在");
        }
        if($password!=$password_db){
            return gf_get_error(104.4, "密码错误");
        }
        $userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay);
        $userinfo=$this->ini_get_user_info($userid);
        return gf_get_success(104, $userinfo);

    }

    /**
     * 多多调用修改密码
     * @param string phone 手机号码
     * @param string password 新密码
     * @author sky 2015-03-26
     */

    function ini_user_change_password_for_duoduo(){
        $phone=gf_get_value_s("phone");
        $password=gf_get_value_s("password");
        if(!$phone){
            return gf_get_error(105.1, "账号不能为空");
        }
        if(!$password){
            return gf_get_error(105.2, "密码不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            return gf_get_error(105.3, "账号不存在");
        }
        gf_db_exec('update user set password="'.$password.'" where id='.$userid, c_db_pool::$db_weme_pay);
        return gf_get_success(105, array('mobile'=>$phone,'userid'=>$userid,'password'=>$password));
    }

    /**
     * 多多调用是否存在账号
     *@param string phone 手机号码
     *@return json
     *@author sky 2015-03-26
     */
    function ini_user_exists_for_duoduo(){
        $phone=gf_get_value_s("phone");
        if(!$phone){
            return gf_get_error(106.1, "账号不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            return gf_get_error(106.2, "账号不存在");
        }
        return gf_get_success(104, array('userid'=>$userid));
    }


    /**
     * 获取用户充值记录
     * @param int userid 用户ID
     * @param string login_token 登录token
     * @param string package_name 包名
     * @param int limit 每页显示的个数 不传值则不分页
     * @param int page 当前的页码 不传值则不分页
     * @return json
     * @author sky 2015-04-01
     */
    function ini_user_get_order(){
        $param = gf_get_param(gf_get_value_s("param"));
        if(!$userid=$param["userid"]){
            return gf_get_error(110.1, "用户ID不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(110.2, "包名不能为空");
        }
        $game_id=gf_db_get_value('select id from relational_game where package_name="'.$package_name.'"', c_db_pool::$db_weme_pay);
        if(!$game_id){
            return gf_get_error(110.3, "包名错误");
        }
        $limit =0;
        if (isset($param['limit'])) {
            $limit=$param['limit'];
        }
        if (!isset($param['page'])) {
            $page=0;
        } else {
            $page=$param['page'];
            $start = ($page - 1) * $limit;
        }
        if(isset($param['limit'])&&isset($param['page'])){
            $sql = "select * from pay_order where game_id=".$game_id." and userid=".$userid." order by id desc limit $start,$limit";
        }else{
            $sql = "select * from pay_order where game_id=".$game_id." and userid=".$userid." order by id desc";
        }
        $order=array();
        $results = gf_db_query($sql, c_db_pool::$db_weme_pay);
        foreach ($results as $key=>$val) {
            $order[$key]['order_sn']=$val->get("order_sn");
            $order[$key]['adate']=$val->get("adate");
            $order[$key]['order_price']=$val->get("order_price");
            $order[$key]['order_status']=$val->get("order_status");
            $order[$key]['game_order_sn']=$val->get("game_order_sn");
            $order[$key]['order_pay_type']=$val->get("order_pay_type");
            $result=$this->get_order_name($val->get("order_sn"),$val->get("order_pay_type"));
            $order[$key]['order_name']=$result["order_name"];
            $order[$key]['order_description']=$result["order_description"];
        }
        return gf_get_success_encrypt(110, array("limit"=>$limit,"page"=>$page,"order"=>array_values($order)));
    }

    /**
     * 获取订单的商品名称
     * @param $order_sn 订单号
     * @param $order_pay_type 支付类型
     */
    function get_order_name($order_sn,$order_pay_type){
        switch ($order_pay_type) {
            case 1;
                $sql='select order_name,order_description from pay_order_for_alipay where order_sn="'.$order_sn.'"';
                break;
            case 2;
                $sql='select order_name,order_description from pay_order_for_tencent where order_sn="'.$order_sn.'"';
                break;
            case 3;
                $sql='select order_name,order_description from pay_order_for_unionpay where order_sn="'.$order_sn.'"';
                break;
            case 4;
                $sql='select order_name,order_description from pay_order_for_yeepay where order_sn="'.$order_sn.'"';
                break;
            case 5;
                $sql='select order_name,order_description from pay_order_for_pp where order_sn="'.$order_sn.'"';
                break;
        }
        $result=gf_db_query($sql, c_db_pool::$db_weme_pay);
        return array("order_name"=>$result->get("order_name"),"order_description"=>$result->get("order_description"));
    }

    /**
     *获取用户基本信息
     *@param int userid 用户ID
     *@author sky 2015-03-26
     */
    function ini_get_user_info($userid){
        $userinfo=array();
        $user_info=gf_db_query('select userid,mobile,device_uuid,gender,nickname,user_avatar,user_avatar_big,user_center_background,login_token,is_defalut_password,defalut_password from user_for_detail where userid='.$userid, c_db_pool::$db_weme_pay);
        $userinfo = $user_info->get_array();
        if (!empty($userinfo)) {
            $userinfo = $userinfo[0];
            foreach ($userinfo as $k => $v) {
                if($userinfo[$k]==null){$userinfo[$k]="";}
                if (is_numeric($k)) {unset($userinfo[$k]);}
            }
        }
        $userinfo['microtime']=gf_microtime();
        return $userinfo;
    }

    /**
     * 生成新的login_token
     *@author sky 2015-03-26
     */
    function ini_creat_login_token(){
        return uniqid();
    }

    /**
     * 生成随机密码
     * @param int len 长度 默认为8
     * @author sky 2015-03-26
     */
    function ini_creat_password($len=8){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$len;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
    /**
     * 发送默认密码到新注册手机
     * @param int userid 用户ID
     * @param string phone 用户手机号
     * @param string password 用户密码
     */
    function ini_send_sms_for_register($userid,$phone,$password){
        return gf_send_sms($userid,$phone,$password,1003);
    }

    /**
     * 通过标识检查手机号码是否存在多多服务器
     * @param string phone 手机号码
     * @return false 不存在 存在返回userid
     * @author sky 2015-03-27
     */
    function ini_user_exists_in_duoduo($phone){
        if(!$phone||!preg_match('/^\d{11}$/', $phone)){
            return false;
         }
        $post = gf_vpost(cst_weme_user_exists_url, http_build_query(array("account" => $phone)), false);
        $verify = json_decode($post, true);
        if(!$verify) {
            return false;
        }
        if ($verify['status'] != 0) {
            return false;
        }
        return $verify['content']["userid"];
    }

    /**
     * 登录多多账号服务器
     * @param string phone 手机号码
     * @param string password 密码
     * http://duoduo.wemepi.com/1.3.3/dispatch.php?v_class=0&v_cmd=115&account=sdkrocktest1&password=e10adc3949ba59abbe56e057f20f883e&device_uuid=861561011255917&e_market_signature=88888888&cur_version=3.0.0
     * @return false 不存在 存在返回userid
     * @author sky 2015-03-27
     */
    function ini_user_login_in_duoduo($phone,$password,$device_uuid,$e_market_signature="88888888",$cur_version="3.0.0"){
        $post = gf_vpost(cst_weme_user_login_url, http_build_query(array("account" => $phone,"password"=>$password,"device_uuid"=>$device_uuid,"e_market_signature"=>$e_market_signature,"cur_version"=>$cur_version)), false);
        $result = json_decode($post, true);
        if(!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 修改多多账号密码
     * @author sky 2015-03-27
     */
    function ini_user_changpassword_in_duoduo($userid,$phone,$password){
        $post = gf_vpost(cst_weme_user_change_password_url, http_build_query(array("userid" => $userid,"mobile"=>$phone,"password"=>md5($password))), false);
        $result = json_decode($post, true);
        if(!$result) {
            return false;
        }
        return $result;
    }



    /**
     *管理后台忘记密码找回密码
     *@param string phone 手机号码
     *@return json
     *@author sky 2015-04-01
     */
    function ini_user_forget_for_manage(){
        if(!$phone=gf_get_value_s("phone")){
            return gf_get_error(111.1, "手机号码不能为空");
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            return gf_get_error(111.2, '错误的手机号码');
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(111.3, "账号不存在");
            }
        }
        $verify_code = mt_rand(1000, 9999);
        $verify=gf_send_sms($userid,$phone,$verify_code,1001);
        if($verify==true){
            return gf_get_success(111, array("userid"=>$userid,"phone"=>$phone,"code"=>$verify_code));
        }else{
            return gf_get_error(111.4, $verify);
        }
    }

    /**
     * 管理后台验证验证码是否填写正确
     * @param string $phone 手机号码
     * @param string $code 验证码
     * @author sky 2015-04-01
     */
    function ini_verify_code_for_manage(){
        if(!$code =gf_get_value_s("code")){
            return gf_get_error(112.3, "验证码不能为空");
        }
        if(!$phone=gf_get_value_s("phone")){
            return gf_get_error(112.4, "手机号码不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(112.4, "账号不存在");
            }
        }
        if ($code > 9999 || $code < 1000) {
            return gf_get_error(112.1, "验证码错误");
        }
        if (!$phone = gf_db_get_value('select phone from user_tmp_verify_phone where phone=\'' . $phone . '\' and code=\'' . $code . '\' and status=0 and type=2', c_db_pool::$db_weme_pay)) {
            return gf_get_error(112.2, "验证码错误");
        }else{
            gf_db_exec('update user_tmp_verify_phone  set status=1 where phone=' . $phone.' and type=2', c_db_pool::$db_weme_pay);
        }
        return gf_get_success(112, "验证成功");
    }


    /**
     * 管理后台调用修改密码
     * @param string phone 手机号码
     * @param string password 新密码
     * @return json
     * @author sky 2015-04-01
     */
    function ini_user_change_password_for_manage(){
        if(!$phone=gf_get_value_s("phone")){
            return gf_get_error(113.1, "手机号码不能为空");
        }
        if(!$password=gf_get_value_s("password")){
            return gf_get_error(113.2, "密码不能为空");
        }
        if(!$userid=gf_db_get_value('select id from user where username="'.$phone.'"', c_db_pool::$db_weme_pay)){
            /**去多多验证账号，不存在则返回失败**/
            $userid=$this->ini_user_exists_in_duoduo($phone);
            if(!$userid){
                return gf_get_error(113.3, "账号不存在");
            }else{
                $result=$this->ini_user_changpassword_in_duoduo($userid, $phone, $password);
                if($result["status"]==0){
                    $result["content"]["base_info"]["phone"]=$phone;
                    unset($result["content"]["base_info"]["mobile"]);
                    return gf_get_success(113, $result["content"]["base_info"]);
                }else{
                    return gf_get_error($result["id"], $result["description"]);
                }
            }
        }else{
            /**修改本地账户密码**/
            gf_db_exec('update user set password="'.md5($password).'" where username=' . $phone.' and id='.$userid, c_db_pool::$db_weme_pay);
            gf_db_exec('update user_for_detail set is_defalut_password=0,defalut_password="" where userid='.$userid, c_db_pool::$db_weme_pay);
            return gf_get_success(113, array("phone"=>$phone,"userid"=>$userid,"password"=>MD5($password)));
        }
    }


    function ini_test(){
        //$config=c_pay_pool::$alipay_pay;
        //print_r($config);
        //echo $this->ini_user_exists_in_duoduo("15180115868");
        //echo gf_microtime();
        //print_r(encrypt('{"phone":"15180115868","password":"123456","device_uuid":"4d1b6260-3f58-42a2-abb0-8344ba170eb7","package_name":"com.weme.pay"}'));

        //echo encrypt("hijklmn");
       // echo "<hr>";
       //print_r(gf_get_param('YtNXy8BpDNmzt/kbJs9YSbZNftNnDoWLnHG1V8/l9h7V/qenzrSXZys7uSk1565wcCN1DYTjsC0nnU4bPHnjNPBLJmvnXJPwOpjq22IGKbTq6YxcdJORvM/quKDJmC/UT7fIihGqENFp1kvHfod795RvGLBygbPnNoZ+ifvPHto='));
        //echo (new CryptAES())->decrypt("tUNe/ANLWgEINs+y279RLodZP+bDZxVIt4wHpdWzdjURg4oIGfLreAgpEYnEfrEu4DguQApBRC1er1nWmXzH5uY6wiCB+m4FI+T6NOJxnRJ1TIBzPnQMy/jejGLXXSzoorrU0CC61g5m7JATnAxE48c3Ba8E3qlVi3fXI+1wHyrRGjkUHZAZQmxFEr0fpEKCMYp1CfgLtkMaa4wsLG1JZ2RJ4HnBtQiuCDs75heJJu04VE5Coy9vP4RbVzM2YKRuxI7PhsoAFHDKULNwf5R1oa3BlhI+ByVgEeOMQ9DNHuJPTudtUlCJPekz5NCVGn/tZrMClTctgnCT+yFmPzW8O+8IDOTCq/1JsWN+WjgXExDq9uXXldS3calVgjK6ubALzpvdp5NaSYzHjnjXDnFxsBdHSCCq+E4wp18uOMB6lOgNP9eGkJkjz4m/kNC3EWndXhq8WuyFfwIgK1Gnw1yUMJ1rT+9PqMrIm8uPw9Iz6pUV/kwXuXmi9BfaFjRGBkbVwwWhAs5Mge5GbnhYOZKhL7ZhDv7Zxqy9uPBuXAJd4zD5NuYc9SnM0KQcA4J5AUKgUE9vu/vK51cHirWTsmxf0sPiyTi5Aw701SbmzEe2HLeAY1IEab9kSb3LzqUYQx1FctwLnnZod9zXkYOd4qgvQ1Oamx7HdH2Aoq63lpyZoxfNQ/IShV1AjqiDLahTQfWDO1r4V7RJnlGWPg0z0QbyxJgteupOU2icYFttpWlHOO4mrJP85PbkNeRd/bZWR1cLDj4+iXMQ+KqFM4Ydu0cyPDGD66aBAYf5Rw2D8EGQkNkwhW60JYx74V9DPM2Mgjy9dZkLTqt143S8gN4UGz3jfoSRKPsP1vVJR+g0XEH1vCs8BrKB9EMeaTMhnyKff4oqd2BUEFxJK13G/Jc0hnFeVGSrZbbq4J78/R4WSQTpuRGnhJ4ll+vLfVII7UHtqdvYDjxkLxkKhnOpKs3q17gSjY10ximgZCO0KwrWI83EmFZev2LrWtwhFRCvb1enNOAem5sDzsJegUs45/j66cTZ2MKsbAxxP7aYN9CfzyMjLNPls4G/f38JOvYGTIXcB8ccdtpsZoHcPHQI5KyQ36rlbJvPX+5RSnA35z0F9Z7w8NGeBdYKoDb/YWtAV+wACZj+HIwsP630acMvS5PT4GICVdSLlD9xfyBaSkc6zIpsrbPUz7T11m2iPOvGrigo5SpcawnpsfH6YqQWSdvgEhNzfJI2kHZS0nsdBIolA4NLGSXJ951UgTpgVh1mgYRgfKeFisAoG6DU7qzDx2aFtTMMkiNxJS6k52vbQFpOy/T3JIm1Er8EN4mWRXejXJfg0KrtbylIQQq3NOOzAI67Lg5DFAa7uWPd58QY5asWITglYwcYd62iE1Mk8IYsA2Skhp9T+Duh5hQRvpuS066aIzW7fgCJl8ocZ6NYPuEa3dBGXZIueiqLkJ63d6BIvbuSV282QChbZWH/XoZtMxp/BhMTTs1GEgnAXR1+lIR1uiP/OemZpZZNemZtnyDDt+APN6n3w8gP1a6H9if3DCbjUcQ2cw==");
        //echo "<hr>";
     echo (new CryptAES())->decrypt("A52OovqMzax759wHefFBTSYYzqO6ipR98Pfi3wxHSpzSHSPoD+0S2ByMNHFvFAarNQfGFfCxHMVtM9WJv3+zmyGmyG4X6EBmyspsdu\/qdZf2laIz9poQi+Ge5odzUpFBPRcHArxJ362h7\/cCY1zoZhIlQ1JgcgMXqoavfI6anAkriiDEtMPTO7M2iZg6l0s+GzheF8um8PkyscYxjOxMQtn1JV4Qoc1ByUSXZngb460M4apRwrNaVmX0dNwFmIfWnrvJJwfFLuwl019\/WhNZ9QbGBrVQ+cCpwD3av59q9Yg=");

    }

}

