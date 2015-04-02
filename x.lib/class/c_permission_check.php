<?php
class c_permission_check
{
	function pc_ok($v_class,$v_cmd){
        //记录访问日志
        gf_log_request(json_encode($_REQUEST));
	    $param=gf_get_param(gf_get_value_s("param"));
	    $userid=$token="";
	    if(isset($param["userid"])){
	        $userid=$param["userid"];
	    }
	    if(isset($param["login_token"])){
	        $token=$param["login_token"];
	    }
		$filter_list = array(
		    '100'	=>	array(
		        '100'	=>	1,
		        '101'	=>	1,
		        '102'	=>	1,
		        '103'	=>	1,
		        '104'	=>	1,
		        '105'	=>	1,
		        '106'	=>	1,
		        '107'	=>	1,
		        '108'	=>	1,
		        '109'	=>	1,
		        '111'	=>	1,
		        '112'	=>	1,
		        '113'	=>	1,
		        '999'	=>	1,
		    ),
		    //异步回调地址不需要验证
		    '300'	=>	array(
		        '300'	=>	1,
		        '301'	=>	1,
		        '302'	=>	1,
		        '303'	=>	1,
		        '304'	=>	1,
		    ),

		);
		if (isset($filter_list[$v_class][$v_cmd])) {
		    return true;
		}else{
		    if(!$token||!$userid){
		        return false;
		    }
		    $login_token=gf_db_get_value('select login_token from user_for_detail where userid='.$userid, c_db_pool::$db_weme_pay);
		    if($login_token!=$token){
		        return false;
		    }
		    return true;
		}
	}

}