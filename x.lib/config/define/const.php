<?php
	/* database connection status switch
	 *
	 */
	define('cst_database_connection_status_for_persistent'									,false													 );

	/* sql debug mode
	 *
	 */
	define('cst_database_sql_debug'															,true													 );
	define('cst_database_sql_debug_for_swoole'												,true													 );
	define('cst_string_spliter'																,'__________________'									 );

        define('cst_version', '1.0.0');
    /**微米多多接口地址定义**/
	define('cst_weme_account_url', "http://duoduo.wemepi.com/1.0.0/dispatch.php");
	define('cst_weme_phone_verify_url', "http://duoduo.wemepi.com/1.0.0/dispatch.php?v_class=0&v_cmd=122");//短信接口
	define('cst_weme_user_exists_url', "http://duoduo.wemepi.com/1.0.0/dispatch.php?v_class=0&v_cmd=124");//通过标识检查手机号码是否存在
	define('cst_weme_user_login_url', "http://duoduo.wemepi.com/1.3.3/dispatch.php?v_class=0&v_cmd=115"); //账号登录多多服务器
	define('cst_weme_user_change_password_url', "http://duoduo.wemepi.com/1.3.3/dispatch.php?v_class=0&v_cmd=116"); //修改多多账号密码服务器

        //日志配置
    define('cst_log_dir', $_SERVER["DOCUMENT_ROOT"].'/'.cst_version.'/log/');
    define('cst_root_dir', $_SERVER["DOCUMENT_ROOT"].'/'.cst_version.'/');
	define('cst_log_request', 'request_response');
	define('cst_log_return', 'return_log');
	define('cst_log_yeepay', 'yeepay');
	define('cst_log_error', 'error');
	define('cst_log_behavior', 'behavior');
 ?>