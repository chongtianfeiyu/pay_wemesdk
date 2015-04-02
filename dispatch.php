<?php
	/* dispatch => test url,http://xxx.xxx.xxx.xxx/dispatch.php?v_class=0&v_cmd=0
	 *
	 */
	include('comm.include.for.lib.start.php');
	function dispatch_is_ok($v_class,$v_cmd){

		return gf_load_class_ex('c_permission_check')->pc_ok($v_class,$v_cmd);
	}
	function dispatch_ex(){

		$r			= ''								;	/* response by web svr	*/
		$v_class	= gf_get_value_n('v_class')			;	/* class				*/
		$v_cmd		= gf_get_value_n('v_cmd')			;	/* method index			*/

		if(dispatch_is_ok($v_class,$v_cmd)){

			$r=dispatch_ini($v_class,$v_cmd);
		}
		else{
			$r=gf_get_error(1,'dispatch.error.login_token.permission');
		};
		echo $r;
	}
 	function dispatch_ini($v_class,$v_cmd){
		$r='';
		switch($v_class){
		   case		100	:{$r=gf_load_class_ex('c_user'					)->ini($v_cmd) ;break	;}		/* user						*/
		   case		200	:{$r=gf_load_class_ex('c_pay'			        )->ini($v_cmd) ;break	;}
		   case		300	:{$r=gf_load_class_ex('c_pay_notify'			)->ini($v_cmd) ;break	;}
		   case		400	:{$r=gf_load_class_ex('c_pay_return'			)->ini($v_cmd) ;break	;}
		   case		500	:{$r=gf_load_class_ex('c_weme_config'			)->ini($v_cmd) ;break	;}
		   default		:{$r=gf_get_error		(0,'dispatch_ini.error')						;}
		}
		return $r;
	}

	/*
	 *
	 */
	dispatch_ex();
?>