<?php

function gf_db_query_mx() {

    return (new c_swoole_sql_query());
}

function gf_mc_del($_str_key) {

    $mc = new c_memcache();
    return $mc->del($_str_key);
}

function gf_mc_get($_str_key) {

    $mc = new c_memcache();
    return $mc->get($_str_key);
}

function gf_mc_set($_str_key, $_value, $_n_time_out = 3600) {

    $mc = new c_memcache();
    return $mc->set($_str_key, $_value, $_n_time_out);
}

function gf_get_error($_n_error_id, $_str_desc) {
    return gf_json_encode(
            array(
                'status' => -1,
                'id' => $_n_error_id,
                'description' => $_str_desc,
                'content' => array(),
            )
    );
}

/**
 * @param integer $_n_success_id
 * @param array $ar_v
 * @param string $_str_desc
 * @return json
 */
function gf_get_success($_n_success_id, $ar_v, $_str_desc = '') {
    return gf_json_encode(
            array(
                'status' => 0,
                'id' => $_n_success_id,
                'description' => $_str_desc,
                'content' => $ar_v,
            )
    );
}

function gf_get_success_encrypt($_n_success_id, $ar_v, $_str_desc = '') {
    gf_log_return($_n_success_id,json_encode($ar_v));
    return gf_json_encode(
            array(
                'status' => 0,
                'id' => $_n_success_id,
                'description' => $_str_desc,
                'content' => encrypt(json_encode($ar_v)),
            )
    );
}

function gf_db_get_rs_by_array($ar) {

    $arx = array();
    foreach ($ar as $v) {
        $arx [] = array($v);
    }return new c_rs($arx);
}

function gf_db_get_rs_empty() {

    return new c_rs(array());
}

function gf_db_open() {

    /* page start hook
     *
     */
    $_g_db;
    $_g_db = gf_load_class_ex('c_db')->db_create(); /* create database object */
    return $_g_db;
}

function gf_db_close() {

}

function gf_db_exec($_str_sql, $dbname, $_debug = false) {

    $_g_db = gf_db_open();
    $r = $_g_db->db_exec($_str_sql, $dbname, $_debug);
    gf_db_close();
    return $r;
}

function gf_db_get_value($_str_sql, $dbname, $_debug = false) {
    $_g_db = gf_db_open();
    $r = $_g_db->db_get_value($_str_sql, $dbname, $_debug);
    gf_db_close();
    return $r;
}

function gf_db_query($_str_sql, $dbname, $_debug = false) {

    $_g_db = gf_db_open();
    $r = $_g_db->db_query($_str_sql, $dbname, $_debug);
    gf_db_close();
    return $r;
}

function gf_db_query_ex($_str_sql, $dbname, $_debug = false) {

    $_g_db = gf_db_open();
    $r = $_g_db->db_query_ex($_str_sql, $dbname, $_debug);
    gf_db_close();
    return $r;
}

function gf_file_dex($_file_path) {

    unlink($_file_path);
}

function gf_file_get_file_status($_file_path) {

    $fp = fopen($_file_path, "r");
    $fstat = fstat($fp);
    fclose($fp);
    return $fstat;
}

function gf_file_read_file_list_by_dir($_dir) {

    $ar = array();
    if ($handle = opendir($_dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {

                $_file_path = cst_web_dir_for_sitemap_a . $file;
                $ar_ex = gf_file_get_file_status($_file_path);
                $ar[] = array(
                    'file_path_a' => $_file_path,
                    'file_path_r' => cst_web_dir_for_sitemap_r . $file,
                    'file_path_date_modify' => $ar_ex['mtime'],
                    'file_path_file_size' => round($ar_ex['size'] / 1024, 2) . 'KB',
                );
            }
        }
        closedir($handle);
    }

    /* sort by file date
     *
     */

    function gf_file_read_file_list_by_dir_sort_by_date($a, $b) {
        if ($a['file_path_date_modify'] == $b['file_path_date_modify']) {
            return 0;
        } else {
            ($a['file_path_date_modify'] == $a['file_path_date_modify']) ? -1 : 1;
        }
    }

    usort($ar, 'gf_file_read_file_list_by_dir_sort_by_date');

    /*
     *
     */
    $c_rs = new c_rs($ar);
    return $c_rs;
}

function gf_sphinx_search_count($_str_key, $v_cmd = 0) {

    $ar = gf_sphinx_search($_str_key, $v_cmd);
    return $ar['total'];
}

function gf_sphinx_search($_str_key, $v_cmd = 0) {

    $sx = new SphinxClient();

    //$sx->SetServer('192.168.8.3', 9312);
    //$cl->SetArrayResult ( true );

    /*
      //ID的过滤
      $cl->SetIDRange(3,4);

      //sql_attr_uint等类型的属性字段，需要使用setFilter过滤，类似SQL的WHERE group_id=2
      $cl->setFilter('group_id',array(2));

      //sql_attr_uint等类型的属性字段，也可以设置过滤范围，类似SQL的WHERE group_id2>=6 AND group_id2<=8
      $cl->SetFilterRange('group_id2',6,8);
     */

    /* 取从头开始的前20条数据，0,20类似SQl语句的LIMIT 0,20
     *
     */
    $sx->SetLimits(0, 88888888);

    if ($v_cmd == 0) {

        /* for music
         *
         */
        $_str_index_name = 'index_hotel_and_place_info';
    }
    $arx = null;
    $rs = $sx->Query($_str_key, $_str_index_name);
    if (0 == $rs['total']) {
        $idx = '0';
    } else {
        $arx = array_keys($rs['matches']);
        $idx = implode(',', $arx);
    }
    return array(
        'total' => count($arx)/* $rs['total'] */,
        'idx' => $idx,
        'v_cmd' => $v_cmd
    );
}

function gf_picture_gd_load_picture($_str_file_path) {

    $_ar = getimagesize($_str_file_path);
    $_str_mime = $_ar['mime'];
    if ($_str_mime == 'image/jpeg') {

        $o = imagecreatefromjpeg($_str_file_path);
    } else if ($_str_mime == 'image/png') {

        $o = imagecreatefrompng($_str_file_path);
    }
    return $o;
}

function gf_picture_gd_create($_str_file_path, $_n_size, $_str_show_type = 0) {

    /* default picture
     *
     */
    $_str_file_path = file_exists($_str_file_path) ? $_str_file_path : (cst_web_dir_for_default_logo_a);

    /* destination file
     *
     */
    $_str_file_name = basename($_str_file_path);
    $_str_cache_dir = '';
    $_str_cache_dir_ex = '';
    for ($i = 0; $i < 11; $i++) {
        $_str_cache_dir .= substr($_str_file_name, $i, 1) . '/';
    }
    $_str_file_name = $_n_size . '_' . $_str_show_type . '_' . $_str_file_name;
    $_str_cache_dir_ex = $_str_cache_dir;
    $_str_cache_dir = cst_web_dir_for_cache_a . $_str_cache_dir;
    gf_dir_create($_str_cache_dir);
    $_str_cache_file_name = $_str_cache_dir . $_str_file_name;

    /* destination cache file exist ?
     *
     */
    $_str_cache_dir_r = cst_web_dir_for_cache_r . $_str_cache_dir_ex . $_str_file_name;
    if (file_exists($_str_cache_file_name)) {

    } else {

        /* picture width div height
         *
         */
        $_ar = getimagesize($_str_file_path);
        $_n_image_width = $_ar['0'];
        $_n_image_height = $_ar['1'];
        $_f_w_div_h = $_n_image_width / $_n_image_height;
        if ($_n_image_width > $_n_image_height) {

            /* so width
             *
             */
            $_n_h = $_n_size;
            $_n_w = $_f_w_div_h * $_n_h;
            if ($_n_size > $_n_image_height) {
                $_x_start = ($_n_image_width - $_n_size) / 2;
                $_y_start = 0;
            } else {
                $_x_start = $_n_size / 2;
                $_y_start = 0;
            }
        } else {

            /* so height
             *
             */
            $_n_w = $_n_size;
            $_n_h = $_n_w / $_f_w_div_h;
            if ($_n_size > $_n_image_width) {
                $_x_start = 0;
                $_y_start = ($_n_image_height - $_n_size) / 2;
            } else {
                $_x_start = 0;
                $_y_start = $_n_size / 2;
            }
        }

        /*
         *
         */
        if ($_n_image_height == $_n_image_width || abs($_n_image_height - $_n_image_width) <= 100) {
            $_x_start = 0;
            $_y_start = 0;
        }

        /* resize
         *
         */
        if ($_str_show_type == 0) {
            $im_dst = imagecreatetruecolor($_n_size, $_n_size);
        }
        $im_src = gf_picture_gd_load_picture($_str_file_path);
        imagecopyresampled($im_dst, $im_src, 0, 0, $_x_start, $_y_start, $_n_w, $_n_h, $_n_image_width, $_n_image_height);

        /* save
         *
         */
        imagejpeg($im_dst, $_str_cache_file_name, 100);

        /* free
         *
         */
        imagedestroy($im_dst);
        imagedestroy($im_src);
    }
    return $_str_cache_dir_r;
}

function gf_date_get_current() {

    $ar_date = getdate(); // date('Y-m-d H:i:s');
    return $ar_date['year'] . '-' . $ar_date['mon'] . '-' . $ar_date['mday'] . ' ' . $ar_date['hours'] . ':' . $ar_date['minutes'] . ':' . $ar_date['seconds'];
}

function gf_session_destory() {

    foreach ($_SESSION as $k => $v) {
        $_SESSION[$k] = 0;
    }return;

    /* finally, destroy the session.
     *
     */
    session_unset();
    session_destroy();
}

function gf_session_get($key) {

    if (array_key_exists($key, $_SESSION)) {
        return $_SESSION[$key];
    } else {
        return '';
    }
}

function gf_session_set($key, $o) {

    $_SESSION[$key] = $o;
}

function gf_json_encode($ar) {

    return json_encode($ar);
}

function gf_get_web_root_dir() {

    return cst_web_root_dir;
}

function gf_load_class_by_dir($_str_class_comm_dir) {

    foreach (scandir($_str_class_comm_dir) as $file) {
        if (is_dir($file)) {

        } else {
            include($_str_class_comm_dir . '/' . $file);
        }
    }
}

function gf_load_class_ex($class_name, $method_name = '', $ar = array()) {

    $o = (new $class_name());
    if ($method_name == '') {
        return $o;
    } else {
        return gf_call_class_method($o, $method_name, $ar);
    }
}

function gf_load_class($class_name, $class_path = '', $method_name = '', $ar = array()) {

    include($class_path . $class_name . '.php');
    return gf_load_class_ex($class_name, $method_name, $ar);
}

function gf_call_class_method($o, $m, $p) {

    if (method_exists($o, $m)) {
        return call_user_func_array(array($o, $m), $p);
    } else {
        return '';
    }
}

function gf_is_number($s) {

    return preg_match('/^[-|0-9\.]+$/i', $s);
}

function gf_get_value_s_ex($s) {

    if (is_array($s)) {
        foreach ($s as $k => $v) {
            $ss = gf_get_value_s_ex($v);
            $s[$k] = $ss;
        }
    } else {
        $s = htmlspecialchars($s, ENT_QUOTES);
    }return $s;
}

function gf_get_value_n_ex($s) {

    if (is_array($s)) {
        foreach ($s as $ss) {
            if (is_array($ss)) {
                gf_get_value_n_ex($ss);
            } else {
                if (!gf_is_number($ss)) {
                    die();
                }
            }
        }
    } else {
        if (!gf_is_number($s)) {
            die();
        }
    }return $s;
}

function gf_get_value_base($v) {

    $s = isset($_REQUEST[$v]) ? ($_REQUEST[$v]) : ('');
    return $s;
}

function gf_get_value_n($v) {

    $s = gf_get_value_base($v);
    if ($s == '') {

    } else {
        gf_get_value_n_ex($s);
    }return $s;
}

function gf_get_value_s($v) {

    $s = gf_get_value_base($v);
    if ($s == '') {
        return $s;
    } else {
        return gf_get_value_s_ex($s);
    }
}

function gf_get_value_h($v) {

    $s = gf_get_value_base($v);
    return $s;
}

function gf_uuid_create() {

    return substr(md5(rand() . rand() . rand()), 0, 32);
}

function gf_url_go_to($_str_url) {

    header('Location: ' . $_str_url);
}

function gf_dir_rmdir($dir) {

    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    gf_dir_rmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function gf_dir_get_time_dir() {

    return str_replace(':', '/', str_replace(' ', '/', str_replace('-', '/', gf_date_get_current())));
}

function gf_dir_create($dir) {

    if (!is_dir($dir)) {
        if (!gf_dir_create(dirname($dir))) {
            return false;
        }if (!mkdir($dir, 0777)) {
            return false;
        }
    }return true;
}

function gf_dir_chmod($path, $filemode) {

    if (!is_dir($path))
        return chmod($path, $filemode);$dh = opendir($path);
    while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..') {

            $fullpath = $path . '/' . $file;
            if (is_link($fullpath))
                return FALSE;elseif (!is_dir($fullpath) && !chmod($fullpath, $filemode))
                return FALSE;elseif (!gf_chmod($fullpath, $filemode))
                return FALSE;
        }
    }
    closedir($dh);
    if (chmod($path, $filemode))
        return TRUE;
    else
        return FALSE;
}

function gf_set_head_content_encoding_2_utf_8() {

    header('Content-type: text/html; charset=utf-8');
}

function gf_set_head_content_encoding_2_utf_8_for_xml() {

    header('Content-type: text/xml; charset=utf-8');
}

function gf_sub_str($s, $n = 0) {

    return ($n == 0) ? gf_convert_html_2_txt($s) : (mb_substr(gf_convert_html_2_txt($s), 0, $n, 'utf-8') . ' ...');
}

/**
 * 通过生日获取星座、属相
 * @param:strinig(生日)
 * @return: array
 * @author: Rock
 * @date: 2013-5-25
 */
function gf_zodiac_animal_signs($date = '19840101') {
    $sign_array = array();
    //处理星座
    $year = intval(substr($date, 0, 4));    //截取年份
    $month = intval(substr($date, 4, 2));     //截取月份
    $day = intval(substr($date, 6, 2));      //截取日
    if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
        return $sign_array;
    }
    $signs = array(
        array('20' => '水瓶座'),
        array('19' => '双鱼座'),
        array('21' => '白羊座'),
        array('20' => '金牛座'),
        array('21' => '双子座'),
        array('22' => '巨蟹座'),
        array('23' => '狮子座'),
        array('23' => '处女座'),
        array('23' => '天秤座'),
        array('24' => '天蝎座'),
        array('22' => '射手座'),
        array('22' => '摩羯座')
    );
    list($start, $zodiac) = each($signs[$month - 1]);
    if ($day < $start) {
        list($start, $zodiac) = each($signs[($month - 2 < 0) ? 11 : $month - 2]);
    }
    //处理属相

    $borntag_array = array("属猴", "属鸡", "属狗", "属猪", "属鼠", "属牛", "属虎", "属兔", "属龙", "属蛇", "属马", "属羊");
    $index = $year % 12;
    $borntag = $borntag_array[$index];
    $sign_array = array('zodiac' => $zodiac, 'borntag' => $borntag);
    return $sign_array;
}

/**
 * 获取用户客户端IP地址
 * @param:
 * @return: String
 * @author: Rock
 * @date: 2013-5-29
 */
function gf_get_client_ip() {
    $ip = "";
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    /*
      处理多层代理的情况
      或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
     */
    if (false !== strpos($ip, ',')) {
        $ip = reset(explode(',', $ip));
    }
    return $ip;
}

/**
 * 判断指定字符串是否为空（NULL或者空字符串）
 * @param：$str 要判断的字符串变量
 * @return：boolean 如果字符串为空（null或者空串），返回TRUE，否则返回FALSE
 * @author: Rock
 * @date: 2013-5-30
 */
function gf_str_isNULL($str) {
    if (is_null($str)) {
        //为null
        return true;
    }
    if (is_string($str)) {
        //是否是字符串
        if (trim($str) == "") {
            //是否为空串
            return true;
        }
        return false;
    }
    return true;
}

/**
 * 给手机号发送内容信息
 * @param:mobile,conetnt
 * @return:
 * @author: Rock
 * @date: 2013-6-6
 */
function gf_post_message_to_user($mobile, $content) {
    //改demo的功能是群发短信和发单条短信。（传一个手机号就是发单条，多个手机号既是群发）
    //您把序列号和密码还有手机号，填上，直接运行就可以了
    //如果您的系统是utf-8,请转成GB2312 后，再提交、
    //请参考 'content'=>iconv( "UTF-8", "gb2312//IGNORE" ,'您好测试短信[XXX公司]'),//短信内容
    $flag = 0;
    $params = '';
    //要post的数据
    $argv = array(
        'sn' => sp_sn, ////替换成您自己的序列号
        'pwd' => strtoupper(md5(sp_sn . sp_pwd)), //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
        'mobile' => $mobile, //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
        'content' => $content, //短信内容
        'ext' => '',
        'stime' => '', //定时时间 格式为2011-6-29 11:09:21
        'msgfmt' => '',
        'rrid' => ''
    );
    //构造要post的字符串
    foreach ($argv as $key => $value) {
        if ($flag != 0) {
            $params .= "&";
            $flag = 1;
        }
        $params.= $key . "=";
        $params.= urlencode($value);
        $flag = 1;
    }
    $length = strlen($params);
    //创建socket连接
    $fp = fsockopen("sdk2.entinfo.cn", 8061, $errno, $errstr, 10) or exit($errstr . "--->" . $errno);
    //构造post请求的头
    $header = "POST /webservice.asmx/mdsmssend HTTP/1.1\r\n";
    $header .= "Host:sdk.entinfo.cn\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . $length . "\r\n";
    $header .= "Connection: Close\r\n\r\n";
    //添加post的字符串
    $header .= $params . "\r\n";
    //发送post的数据
    fputs($fp, $header);
    $inheader = 1;
    while (!feof($fp)) {
        $line = fgets($fp, 1024); //去除请求包的头只显示页面的返回数据
        if ($inheader && ($line == "\n" || $line == "\r\n")) {
            $inheader = 0;
        }
        if ($inheader == 0) {
            // echo $line;
        }
    }
    return $line;
}

/**
 * 模拟网址提交数据
 * @param:$url,$data
 * @author: Rock
 * @date: 2013-7-16
 */
function gf_vpost($url, $data = "", $curlopt_header = true) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $curlopt_header);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $re = curl_exec($ch);
    curl_close($ch);
    return $re;
}

/**
 * 发送消息给服务器
 * @param:$send_id,$receive,$message,$session_passage,$message_type,$passage_url
 * @author: Rock
 * @date: 2013-7-16
 */
function gf_message_to_server($send_id, $receive, $message, $session_passage, $message_type, $passage_url) {
    $re = 'message=' . $send_id . '____' . $receive . '____' . $message . '____' . $session_passage . '____' . $message_type . '____' . $passage_url;
    return $re;
}

function gf_kick_user($userid, $game_id, $login_machine_uuid) {
    //$sql="select chat_svr_dns,channel from game_group_chat where game_id in(select game_id from game_group_chat_user where userid= $userid) and game_id not in(15,32)";
    //$sql="select chat_svr_dns,channel from game_group_chat where game_id=$game_id and game_id not in(15,32)";
    $sql = "select chat_svr_dns,channel from game_group_chat where game_id=$game_id and game_id not in(32)";
    foreach (gf_db_query($sql, c_db_pool::$weme_db) as $v) {
        $dns = $v->get("chat_svr_dns");
        $channel = $v->get("channel");
        $url = $dns . cst_chart_server_pub . '?id=' . $channel;
        //$data=gf_message_to_server(0,$userid,'system_user_stop','0000000',7,$url);
        $data = gf_message_to_server(0, $userid, $login_machine_uuid, '0000000', 7, $url);
        gf_vpost($url, $data);
    }
}

function gf_get_chat_server() {

    return substr(md5(cst_dns), 0, 11);
}

/* get user message channel by user login id
 *
 */

function gf_get_message_notify_server_url_for_login_user($n_user_id) {

    $_ar_message_notify_svr = gf_get_message_notify_server_url($n_user_id);
    $current_message_notify_svr_for_pub = $_ar_message_notify_svr['svr'] . $_ar_message_notify_svr['pub'] . '_' . $n_user_id; /* pub url	 */
    $current_message_notify_svr_for_sub = $_ar_message_notify_svr['svr'] . $_ar_message_notify_svr['sub'] . '_' . $n_user_id; /* sub url	 */
    return array(
        'pub_url' => $current_message_notify_svr_for_pub,
        'sub_url' => $current_message_notify_svr_for_sub,
    );
}

function gf_message_get_user_message_notify_channel($_n_user_id, $b_refresh = false) {

    /* check userid
     *
     */
    $_n_user_id = $_n_user_id > 0 ? $_n_user_id : 0;

    /* get value from memcache
     *
     */
    $_key = 'user_message_notify_channel_for_pub_' . $_n_user_id;
    $r = gf_mc_get($_key);
    if ($b_refresh/* force refresh data in memory cache server */) {
        $r = false;
    }if (!$r) {

        /* save it to memcache
         *
         */
        $r = gf_db_get_value("select current_message_notify_svr_for_pub from user_login_status where userid=" . $_n_user_id, c_db_pool::$weme_db);
        gf_mc_set($_key, $r);
    }
    return $r;
}

/* get message notify server url,by breezer
 *
 */

function gf_get_message_notify_server_url($n_user_id) {

    /* channel id
     *
     */
    $_channel_id = gf_get_chat_server();

    /* message notify sever pool
     *
     */
    $ar = array(
        array(
            'svr' => 'http://mt.ggc.wemepi.com/', /* http push stream svr */
            'pub' => 'pub_ex?id=' . $_channel_id, /* pub channel			 */
            'sub' => 'sub_ex/' . $_channel_id, /* sub channel			 */
        )
        ,
        array(
            'svr' => 'http://a.mt.ggc.wemepi.com/',
            'pub' => 'pub_ex?id=' . $_channel_id,
            'sub' => 'sub_ex/' . $_channel_id,
        )
    );

    /* get one svr from pool
     *
      $count	= count($ar);$index=rand(0, $count-1);

     */

    /*
     *
     */
    $n_mod_index = $n_user_id % 2;
    return $ar[$n_mod_index];
}

/**
 * 产生二维码图片
 * @param:$zbar_data,$zbar_pic,$zbar_point,$rgb
 * @return: array
 * @author: Rock
 * @date: 2013-8-28
 */
function gf_get_zbar($zbar_url, $zbar_pic, $zbar_point = 5, $rgb) {
    //$zbar_url = 'http://s.bookphone.cn';      二维码数据
    //$zbar_pic = '1111.png'; 				   	    生成的文件名
    $errorCorrectionLevel = 'L';      //纠错级别：L、M、Q、H
    //$zbar_point = 5;点的大小：1到10
    QRcode::png($zbar_url, $zbar_pic, $errorCorrectionLevel, $zbar_point, 2, false, $rgb);
}

/**
 * 获取图片服务器URL
 * @param:$url
 * @return: String
 * @author: Rock
 * @date: 2013-8-28
 */
function gf_get_pic_server_url($url = 'http://weme.wemepi.com/1.0.0/dispatch.php?v_class=200&v_cmd=202') {
    $ch = curl_init();                                   //url初始化
    curl_setopt($ch, CURLOPT_URL, $url);     //url参数
    curl_setopt($ch, CURLOPT_POST, 1);                  //post方式
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);      //设置超时限制防止死循环
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         //将会以字符串的形式返回那个cURL句柄获取的内容。
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    $content = $result['content'];
    $file_svr_url = $content['file_svr_url'];
    return $file_svr_url;
}

/**
 * 上传图片到服务器
 * @param: $pic_server_url(图片服务器url),$pic_path(图片全路径)
 * @return: String
 * @author: Rock
 * @date: 2013-8-28
 */
function gf_upload_pic($pic_server_url, $pic_path) {
    $fields['name_uploaded_file'] = '@' . $pic_path;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pic_server_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);    // 设置超时限制防止死循环
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    $content = $result['content'];
    $url = $content['url'];
    return $url;
}

/**
 * 通过比较现在的时间与用户玩游戏的结束时间，显示不同的状态信息
 * @param:$date
 * @author: Rock
 * @date: 2013-10-14
 */
/*
  function gf_get_play_game_status_info($date)
  {
  $status_info = '';        //状态信息
  $first_time =  21600 ;    //6小时
  $second_time =  86400;    //24小时
  $third_time = 172800;     //48小时
  $four_time = 2592000;     //1个月
  $now_timestamp = time();
  $date_timestamp = strtotime($date);
  $timstamp = $now_timestamp - $date_timestamp;
  if($timstamp <= $first_time)
  {
  $status_info = '刚刚在玩';
  }
  else if($timstamp > $first_time && $timstamp <= $second_time)
  {
  $status_info = '今天在玩';
  $old_day  = substr(trim($date),0,10);
  $old_day = strtotime($old_day);
  $now_day = date('Y-m-d');
  $now_day = strtotime($now_day);
  $day = ($now_day-$old_day)/86400;
  if($day > 0)
  {
  $status_info = '昨天在玩';
  }
  }
  else if($timstamp > $second_time && $timstamp <= $third_time)
  {
  $status_info = '昨天在玩';
  $old_day  = substr(trim($date),0,10);
  $old_day = strtotime($old_day);
  $now_day = date('Y-m-d');
  $now_day = strtotime($now_day);
  $day = ($now_day-$old_day)/86400;
  if($day > 1)
  {
  $status_info = '最近在玩';
  }
  }
  else if($timstamp > $third_time && $timstamp <= $four_time )
  {
  $status_info = '最近在玩';
  }
  else
  {
  $status_info = '';
  }
  return $status_info;
  }
 */

/**
 * 通过比较现在的时间与用户玩游戏的结束时间，显示不同的状态信息
 * 显示分钟/小时/天/最近
 * 60分钟内，分钟
 * 超过60分钟不超过24小时，小时
 * 超过24小时，不超过72小时，昨天/前天
 * 超过三天，不超过30天，最近
 * 超过30天，不显示。
 * 2小时内跨天，显示分钟/小时
 * 超过2小时跨天，显示天
 * @param:$date
 * @author: Rock
 * @date: 2013-10-29
 */
function gf_get_play_game_status_info($date) {
    $status_info = '';                 //状态信息
    $one_minute = 60;                                       //1分钟
    $ten_minute = 600;                                      //10分钟
    $twenty_minute = 1200;                                  //20分钟
    $thirty_minute = 1800;                                  //30分钟
    $forty_minute = 2400;                                   //40分钟
    $fifty_minute = 3000;                                   //50分钟
    $one_hour = 3600;                 //1小时
    $two_hour = 7200;                  //2小时
    $twenty_four_hour = 86400;           //24小时
    $forty_eight_hour = 172800;           //48小时
    $seventy_two_hour = 259200;                             //72小时
    $one_month_hour = 2592000;            //1个月
    $now_timestamp = time();
    $date_timestamp = strtotime($date);
    $timstamp = $now_timestamp - $date_timestamp;
    if ($timstamp < $one_minute) {
        //小于1分钟、显示正在玩
        $status_info.='正在玩';
    } else if ($timstamp >= $one_minute && $timstamp < $ten_minute) {
        //大于等于1分钟并且小于10分钟，显示分钟数
        $status_info = floor($timstamp / 60);
        $status_info.='分钟前在玩';
    } else if ($timstamp >= $ten_minute && $timstamp < $twenty_minute) {
        $status_info = '10分钟前在玩';
    } else if ($timstamp >= $twenty_minute && $timstamp < $thirty_minute) {
        $status_info = '20分钟前在玩';
    } else if ($timstamp >= $thirty_minute && $timstamp < $forty_minute) {
        $status_info = '30分钟前在玩';
    } else if ($timstamp > $forty_minute && $timstamp < $fifty_minute) {
        $status_info = '40分钟前在玩';
    } else if ($timstamp >= $fifty_minute && $timstamp < $one_hour) {
        $status_info = '50分钟前在玩';
    } else if ($timstamp >= $one_hour && $timstamp < $two_hour) {
        //大于等于1个小时、小于2个小时
        $status_info = '1个小时前在玩';
    } else if ($timstamp >= $two_hour && $timstamp <= $twenty_four_hour) {
        //大于等于2个小时、小于等于24个小时，需判断是否跨天，如果是跨天只可能是昨天
        $old_day = substr(trim($date), 0, 10);
        $old_day = strtotime($old_day);
        $now_day = date('Y-m-d');
        $now_day = strtotime($now_day);
        $day = ($now_day - $old_day) / 86400;
        if ($day > 0) {
            //跨天了
            $status_info = '昨天在玩';
        } else {
            //没有跨天，显示小时
            $status_info = floor($timstamp / 3600);
            $status_info.='个小时前在玩';
        }
    } else if ($timstamp > $twenty_four_hour && $timstamp <= $one_month_hour) {
        //大于24小时、小于一个月，判断跨天情况：昨天、前天、最近
        $old_day = substr(trim($date), 0, 10);
        $old_day = strtotime($old_day);
        $now_day = date('Y-m-d');
        $now_day = strtotime($now_day);
        $day = ($now_day - $old_day) / 86400;
        if ($day <= 1) {
            $status_info = '昨天在玩';
        } else if ($day <= 2) {
            $status_info = '前天在玩';
        } else if ($day <= 30) {
            //最近在玩
            $status_info = '最近在玩';
        } else {
            //超过30天，不显示
            $status_info = '';
        }
    } else {
        //超过30天，不显示
        $status_info = '';
    }
    return $status_info;
}

/**
 * 获取图像的类型
 * @param:$image_pic
 * @return: array
 * @author: Rock
 * @date: 2014-2-24
 */
function gf_imageType($image_pic = 'http://a.pic.wemepi.com/data.x/2014/1/22/20/14/21/8/5/1/b/9/851b9f74b7e75f9a13cb10aa022dffb0.jpg') {
    //为图片的路径可以用d:/upload/11.jpg等绝对路径
    $flag = gf_check_url($image_pic);
    if ($flag == false) {
        return '';
    }
    $file = fopen($image_pic, "rb");
    $bin = fread($file, 2); //只读2字节
    fclose($file);
    $strInfo = @unpack("C2chars", $bin);
    $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
    $fileType = '';
    switch ($typeCode) {
        case 7790: $fileType = 'exe';
            break;
        case 7784: $fileType = 'midi';
            break;
        case 8297: $fileType = 'rar';
            break;
        case 255216: $fileType = 'jpg';
            break;
        case 7173: $fileType = 'gif';
            break;
        case 6677: $fileType = 'bmp';
            break;
        case 13780: $fileType = 'png';
            break;
        default: $fileType = 'unknown';
    }
    return $fileType;
}

/**
 * 检查URL的合法性
 * @param:$url
 * @return: array
 * @author: Rock
 * @date: 2014-4-30
 */
function gf_check_url($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);
    $found = false;
    // 如果请求没有发送失败
    if ($result !== false) {
        // 再检查http响应码是否为200
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode == 200) {
            $found = true;
        }
    }
    curl_close($curl);
    return $found;
}

/**
 * 创建图像
 * @param:$image_pic,$width,$height
 * @return: array
 * @author: Rock
 * @date: 2014-2-21
 */
function gf_createImage($image_pic, $width, $height) {
    header("Content-type:image/jpg");
    $img = ImageCreate($width, $height);
    $skyblue = ImageColorAllocate($img, 255, 255, 255);  //白色背景
    ImageFill($img, 0, 0, $skyblue);
    Imagejpeg($img, $image_pic);  //将图片$img保存到$image_pic
    ImageDestroy($img);
    return $image_pic;
    /*
      $image_pic_type = gf_imageType($image_pic);
      if($image_pic_type == 'jpg')
      {
      header("Content-type:image/jpg");
      }
      else
      {
      header("Content-type:image/png");
      }
      $img=ImageCreate($width,$height);
      $skyblue=ImageColorAllocate($img,255,255,255);
      ImageFill($img,0,0,$skyblue);
      if($image_pic_type == 'jpg')
      {
      Imagejpeg($img,$image_pic);
      }
      else
      {
      Imagepng($img,$image_pic);
      }
      ImageDestroy($img);
      return $image_pic;
     */
}

/**
 * 产生缩略图
 * @param:$url,$thumb_file,$width,$hight
 * @return: array
 * @author: Rock
 * @date: 2014-2-21
 */
function gf_createThumb($url, $thumb_file, $width, $hight) {
    /*
      header("Content-type:image/jpg");
      //$src_image = ImageCreateFromJPEG($uploadfile);         //读取JPEG文件并创建图像对象
      $src_image = ImageCreateFromPng($url);                   //读取JPEG文件并创建图像对象
      $srcW = ImageSX($src_image);                             //获得图像的宽
      $srcH = ImageSY($src_image);                             //获得图像的高
      $dstW = $width;                            				 //设定缩略图的宽度
      $dstH = $hight;                            				 //设定缩略图的高度
      $dst_image = ImageCreateTrueColor($dstW,$dstH);          //创建新的图像对象(真彩色)
      ImageCopyResized($dst_image,$src_image,0,0,0,0,$dstW,$dstH,$srcW,$srcH);        //将图像重定义大小后写入新的图像对象
      ImageJpeg($dst_image,$thumb_file);
      return $thumb_file;
     */
    $image_pic_type = gf_imageType($url);
    if ($image_pic_type == 'jpg') {
        header("Content-type:image/jpg");
    } else {
        header("Content-type:image/png");
    }
    //$src_image = ImageCreateFromJPEG($uploadfile);         //读取JPEG文件并创建图像对象
    if ($image_pic_type == 'jpg') {
        $src_image = imagecreatefromjpeg($url);                   //读取JPEG文件并创建图像对象
    } else {
        $src_image = ImageCreateFromPng($url);                   //读取JPEG文件并创建图像对象
    }
    $srcW = ImageSX($src_image);                             //获得图像的宽
    $srcH = ImageSY($src_image);                             //获得图像的高
    $dstW = $width;                                 //设定缩略图的宽度
    $dstH = $hight;                                 //设定缩略图的高度
    $dst_image = ImageCreateTrueColor($dstW, $dstH);          //创建新的图像对象(真彩色)
    ImageCopyResized($dst_image, $src_image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);        //将图像重定义大小后写入新的图像对象
    if ($image_pic_type == 'jpg') {
        ImageJpeg($dst_image, $thumb_file);
    } else {
        imagepng($dst_image, $thumb_file);
    }
    return $thumb_file;
}

/**
 * 合并图片
 * @param:$dst,$src,$bja,$flag
 * @return: array
 * @author: Rock
 * @date: 2014-2-21
 */
function gf_mergeImage($dst, $src, $bja, $flag = true) {
    /*
      header("Content-type:image/jpg");
      //输出合并后水印图片
      $merge_img =$bja;
      //得到原始图片信息
      $dst_im = imagecreatefromjpeg($dst);
      $dst_info = getimagesize($dst);
      //水印图像
      $src_im = imagecreatefromjpeg($src);
      $src_info = getimagesize($src);
      //水印透明度
      $alpha = 100;
      //合并水印图片
      if($flag == true)
      {
      imagecopymerge($dst_im,$src_im,$dst_info[0]-$src_info[0],$dst_info[1]-$src_info[1],0,0,$src_info[0],$src_info[1],$alpha);  //右下角对齐
      }
      else
      {
      imagecopymerge($dst_im,$src_im,0,0,0,0,$src_info[0],$src_info[1],$alpha);  //左上角对齐
      }
      //输出合并后水印图片
      ImageJpeg($dst_im,$merge_img);                                               //创建缩略图文件
      imagedestroy($dst_im);
      imagedestroy($src_im);
      return $merge_img;
     */
    header("Content-type:image/jpg");
    //输出合并后水印图片
    $merge_img = $bja;
    //原始图像
    //$dst = "/tmp/3s.jpg";
    //得到原始图片信息
    $dst_image_pic_type = gf_imageType($dst);
    if ($dst_image_pic_type == 'jpg') {
        $dst_im = imagecreatefromjpeg($dst);
    } else {
        $dst_im = imagecreatefrompng($dst);
    }
    $dst_info = getimagesize($dst);
    //水印图像
    //$src = "//tmp/3.jpg";
    $src_image_pic_type = gf_imageType($src);
    if ($src_image_pic_type == 'jpg') {
        $src_im = imagecreatefromjpeg($src);
    } else {
        $src_im = imagecreatefromPng($src);
    }
    $src_info = getimagesize($src);
    //水印透明度
    $alpha = 100;
    //合并水印图片
    if ($flag == true) {
        imagecopymerge($dst_im, $src_im, $dst_info[0] - $src_info[0], $dst_info[1] - $src_info[1], 0, 0, $src_info[0], $src_info[1], $alpha);
    } else {
        imagecopymerge($dst_im, $src_im, 0, 0, 0, 0, $src_info[0], $src_info[1], $alpha);
    }
    //输出合并后水印图片
    if ($dst_image_pic_type == 'jpg') {
        ImageJpeg($dst_im, $merge_img);                                               //创建缩略图文件
    } else {
        ImagePng($dst_im, $merge_img);                                               //创建缩略图文件
    }
    imagedestroy($dst_im);
    imagedestroy($src_im);
    return $merge_img;
}

function gf_message_filter_xxx_2_string($s) {
    $ss = str_ireplace(array(chr(10), chr(13)), '', htmlspecialchars_decode($s));
    $ss_src = $ss;
    $r = json_decode($ss, true);
    if ($r == false) {
        preg_match('/message_text":"(.*?)","send_user_id/is', $ss, $match);
        $s = $match[1];

        /*
         *
         */
        $ss_old = $s;
        $ss_new = str_replace(array('"', '\\', '\'', '<', '>', '[', ']', ':', '{', '}'), '', $s);
        return str_replace($ss_old, $ss_new, $ss_src);
    } else {
        return $ss;
    }
}

function gf_message_filter_xxx_2_encoder($s) {
    //判断是否含有___UYT___IUU______UYT___IUU___,如果有循环分割
    $split = '___UYT___IUU______UYT___IUU___';
    $split1 = '___UYT___IUU___';
    for (;;) {
        if (strpos($s, $split) === false) {
            //不含有
            break;
        } else {
            //含有
            $s = str_replace($split, $split1, $s);
        }
    }
    return $s;
}

/**
 * 获取当前的时间戳(单位为秒)
 * @param:
 * @return: array
 * @author: Rock
 * @date: 2014-5-30
 */
function gf_microtime() {
    list($usec, $sec) = explode(' ', microtime());
    $time = ((float) $usec + (float) $sec);
    return $time;
}

/**
 * 昵称随机组合
 * @author 2014-9-24 by Carl
 */
function gf_get_random_nickname() {
    $nameArr1 = array("有能力的", "上面的", "害怕的", "担心的", "单独的", "生气的", "愤怒的", "严重的", "美丽的", "黑色的", "明亮的", "聪明的", "双倍的", "棕色的", "忙碌的", "小心的", "仔细的", "仙女的", "假的", "受伤的", "装出来的", "超市的", "殖装的", "三国的", "挖宝的", "坑爹的", "便宜的", "干净的", "清洁的", "靠近的", "多云的", "寒冷的", "凉快的", "危险的", "黑暗的", "深色的", "亲爱的", "贵的", "深的", "美味的", "可口的", "不同的", "差异的", "困难的", "艰难的", "干的", "干燥的", "东方的", "容易的", "空的", "足够的", "昂贵的", "著名的", "遥远的", "快速的", "喜欢的", "中意的", "少数的", "晴朗的", "外国的", "空闲的", "友好的", "前面的", "满足的", "吃饱了撑的", "高兴的", "伟大的", "重要的", "感兴趣的", "关心的", "有趣的", "友好的", "和善的", "大的", "巨大的", "最后的", "迟的", "晚的", "懒惰的", "左边的", "右边的", "轻的", "轻骑兵的", "少的", "长的", "远的", "大声的", "响亮的", "底部的", "矮的", "运气好的", "侥幸的", "许多的", "现代的", "许多的", "大量的", "新的", "新鲜的", "最近的", "紧挨的");
    $nameArr2 = array("行", "尸", "走", "肉", "金", "蝉", "脱", "壳", "百", "里", "挑", "一", "玉", "满", "堂", "背", "水", "战", "霸", "王", "别", "姬", "天", "上", "人", "间", "不", "吐", "快", "海", "阔", "天", "空", "情", "非", "得", "已", "腹", "经", "纶", "兵", "临", "城", "下", "春", "暖", "花", "开", "插", "翅", "难", "飞", "黄", "道", "吉", "日", "无", "双", "偷", "换", "两", "小", "猜", "卧", "虎", "藏", "龙", "珠", "光", "宝", "气", "簪", "缨", "世", "家", "公", "子", "绘", "声", "色", "国", "香", "相", "亲", "爱", "八", "仙", "过", "良", "缘", "掌", "皆", "欢", "喜", "逍", "遥", "法", "外", "生", "财");
    $nameArr3 = array("保存", "避难", "暗影", "智慧", "敏捷", "力量", "智力", "暴击", "贵族", "攻击", "防御", "厨师", "串串", "劣质", "军队", "自动", "仙女", "快递", "猪头", "方便面", "自恋", "招财", "速度", "守护", "能量", "能力", "闪避", "腐蚀", "毒液", "火焰", "闪电", "水晶", "英勇", "灵巧", "勇气", "勋章", "魔法", "护盾", "医疗", "救护", "兴奋", "黑暗", "沉默", "术士", "透明", "恢复", "无敌", "沙漏", "阿斯顿", "芯片", "时间", "齿轮", "剧毒", "药剂", "不死鸟", "羽毛", "潘多拉", "暮色", "月", "日", "星", "天", "地", "奥杜因", "逆风", "逆转", "致胜", "马戏团", "副本", "翻盘", "翻拍", "坑人", "埋人", "排位赛", "梦境", "玄铁", "牛皮哄哄", "三观不正", "宅", "富春江", "百合", "三次元", "变身", "朱雀石", "龙心", "松木", "天罡", "地煞", "白虎", "玄武", "炫舞门", "丢丢", "圣经", "河马", "荷马史诗", "祭祀", "腰鼓", "新罗", "天方夜谭", "达文西");
    $nameArr4 = array("权杖", "斗篷", "食人魔", "手套", "披风", "鞋", "靴", "饰品", "饰环", "爪", "指环", "挂饰", "护身符", "护符", "面罩", "面具", "面纱", "面包", "棉布", "绵羊", "腰带", "胸甲", "笛子", "长笛", "试剂", "狮子", "狮角", "球", "长笛", "短笛", "大魔王", "大魔鬼", "大蘑菇", "小蘑菇", "蘑菇力", "水晶", "钻石", "黄金", "宝石", "相声", "天才", "插座", "元宵", "元素", "恶意代码", "攻击指爪", "生命手册", "药水", "医疗剂", "卷轴", "石头", "炊烟小厨", "厨子", "死亡之书", "岩石", "印记", "红龙蛋", "尖刺", "项圈", "野性", "碎片", "魂石", "项链", "纹章", "三才阵", "生肖", "牙", "金三胖", "最后通牒", "三个代表", "情圣表白信", "皇上", "公式", "墓园", "老鼠", "舒克", "贝塔", "皮皮鲁", "鲁西西", "牛", "牛魔王", "牛人", "老虎", "兔子", "兔八哥", "兔女郎", "算盘", "诗经", "龙", "蛇", "人马", "马屁精", "喜羊羊", "美羊羊", "神兽砚", "幻术", "智力斗篷", "洛奇", "明日香", "零号机");
    $nickname = $nameArr1[rand(0, count($nameArr1) - 1)] . $nameArr2[rand(0, count($nameArr2) - 1)] . $nameArr3[rand(0, count($nameArr3) - 1)] . $nameArr4[rand(0, count($nameArr4) - 1)];
    return $nickname;
}

/**
 * 加密
 */
function encrypt($str) {
    return (new CryptAES())->encrypt($str);
}

/**
 * 解密
 */
function decrypt($str) {
    $Crypt = new CryptAES();
    return $Crypt->decrypt($str);
}

function gf_get_param($str) {
    if ($str) {
        return json_decode(decrypt($str), true);
    }
    //return json_decode(htmlspecialchars_decode($str),true);
}

/**
 *
 * @param int $userid 用户名
 * @param string $phone
 * @param string $message
 * @param int $flag  短信类型（ 1000  该验证码用于验证此手机为安全手机
 * 		                        1001  该验证码用于更改你weme帐号的密码
 * 								1002  该验证码用于更改你weme帐号的安全手机
 *                              1003  用于发送注册的初始密码）
 */
function gf_send_sms($userid, $phone, $message, $flag) {
    $post = gf_vpost(cst_weme_phone_verify_url, http_build_query(array("mobile" => $phone, "message" => $message, "flag" => $flag)), false);
    $verify = json_decode($post, true);
    if (!$verify) {
        return false;
    }
    if ($verify['status'] != 0) {
        return $verify['description'];
    }
    $type = 2;
    if ($flag == 1003) {
        $type = 1;
    }
    gf_db_exec('replace into user_tmp_verify_phone (`phone`, `code`, `adate`,`type`) values ("' . $phone . '", "' . $message . '", "' . date("Y-m-d H:i:s", time()) . '",' . $type . ')', c_db_pool::$db_weme_pay);
    return true;
}

/**
 * 记录登录日志
 * @param int userid 用户ID
 * @param string token 登录token
 * @param string package_name 游戏包名
 * @param int is_use_password  token登录还是密码登录 1为密码登录
 */
function gf_save_user_login_log($userid, $package_name, $is_use_password = 1, $token = "") {
    $game_id = gf_db_get_value('select id from relational_game where package_name="' . $package_name . '"', c_db_pool::$db_weme_pay);
    $sql = 'replace into user_login_token (`userid`,`login_token`,`game_id`) values (' . $userid . ',"' . $token . '",' . $game_id . ')';
    gf_db_exec($sql, c_db_pool::$db_weme_pay);
    gf_db_exec('insert into user_login_log (`userid`,`game_id`,`login_token`,`is_use_password`) values (' . $userid . ',' . $game_id . ',"' . $token . '",' . $is_use_password . ')', c_db_pool::$db_weme_pay);
}

/**
 * 判断登录token是否失效
 * @param int $userid 用户ID
 * @param string $token 登录token
 * @param string $package_name 登录包名
 */
function gf_token_timeout($userid, $token, $package_name) {
    $game_id = gf_db_get_value('select id from relational_game where package_name="' . $package_name . '"', c_db_pool::$db_weme_pay);
    $last_login_time = gf_db_get_value('select last_login_time from user_login_token where userid=' . $userid . ' and game_id=' . $game_id, c_db_pool::$db_weme_pay);
    if (strtotime($last_login_time) <= (time() - 7 * 24 * 60 * 60)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 生成唯一的订单号
 * @return int orderid 订单号
 */
function gf_creat_order_id() {
    $orderid = date("YmdHis") . mt_rand(100000, 999999);
    if (gf_db_get_value('select id from pay_order where order_sn="' . $orderid . '"', c_db_pool::$db_weme_pay)) {
        $orderid = gf_creat_order_id();
    }
    return $orderid;
}

function gf_log_error($message) {
    $time = time();
    $flag = '[ERROR]';
    $line = array(
        $flag,
        @uniqid(),
        date("Y-m-d H:i:s", $time),
        $time,
        @$message['v_class'],
        @$message['v_cmd'],
        @$param['userid'],
        $message,
        @json_encode($param)
    );
    if (!is_dir(cst_log_dir)) {
        @mkdir(cst_log_dir, '0777');
        if (!is_dir(cst_log_dir . "/" . cst_log_error))
            @mkdir(cst_log_dir . "/" . cst_log_error, '0777');
    }
    gf_log_line($line, cst_log_dir . "/" . cst_log_error . "/" . date("Ymd", $time) . ".log");
}

function gf_log_request($message) {
    $time = time();
    $flag = '[REQUEST]';
    $param=array();
    if(isset($message["param"])){
        $param=gf_get_param($message["param"]);
    }
    $line = array(
        $flag,
        @uniqid(),
        date("Y-m-d H:i:s", $time),
        $time,
        @$message['v_class'],
        @$message['v_cmd'],
        @$param['userid'],
        $message,
        @json_encode($param)
    );
    if (!is_dir(cst_log_dir)) {
        @mkdir(cst_log_dir, '0777');
        if (!is_dir(cst_log_dir . "/" . cst_log_request))
            @mkdir(cst_log_dir . "/" . cst_log_request, '0777');
    }
    gf_log_line($line, cst_log_dir . "/" . cst_log_request . "/" . date("Ymd", $time) . ".log");
}


function gf_log_return($id,$message){
    $time = time();
    $flag = '[RETURN]';
    $line = array(
        $flag,
        @uniqid(),
        date("Y-m-d H:i:s", $time),
        $time,
        @$id,
        $message
    );
    if (!is_dir(cst_log_dir)) {
        @mkdir(cst_log_dir, '0777');
        if (!is_dir(cst_log_dir . "/" . cst_log_return))
            @mkdir(cst_log_dir . "/" . cst_log_return, '0777');
    }
    gf_log_line($line, cst_log_dir . "/" . cst_log_return . "/" . date("Ymd", $time) . ".log");
}


function gf_log_line(array $line, $file_path) {
    if ($fp = fopen($file_path, "aw")) {
        fwrite($fp, implode("|", $line) . "\n");
        fclose($fp);
    }
}

//易宝支付签名
function getReqHmacString($p0_Cmd,$p2_Order,$p3_Amt,$p4_verifyAmt,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pa7_cardAmt,$pa8_cardNo,$pa9_cardPwd,$pd_FrpId,$pr_NeedResponse,$pz_userId,$pz1_userRegTime)
{

    $config=c_pay_pool::$yeepay_pay;

    #进行加密串处理，一定按照下列顺序进行
    $sbOld		=	"";
    #加入业务类型
    $sbOld		=	$sbOld.$p0_Cmd;
    #加入商户代码
    $sbOld		=	$sbOld.$config["p1_MerId"];
    #加入商户订单号
    $sbOld		=	$sbOld.$p2_Order;
    #加入支付卡面额
    $sbOld		=	$sbOld.$p3_Amt;
    #是否较验订单金额
    $sbOld		=	$sbOld.$p4_verifyAmt;
    #产品名称
    $sbOld		=	$sbOld.$p5_Pid;
    #产品类型
    $sbOld		=	$sbOld.$p6_Pcat;
    #产品描述
    $sbOld		=	$sbOld.$p7_Pdesc;
    #加入商户接收交易结果通知的地址
    $sbOld		=	$sbOld.$p8_Url;
    #加入临时信息
    $sbOld 		= $sbOld.$pa_MP;
    #加入卡面额组
    $sbOld 		= $sbOld.$pa7_cardAmt;
    #加入卡号组
    $sbOld		=	$sbOld.$pa8_cardNo;
    #加入卡密组
    $sbOld		=	$sbOld.$pa9_cardPwd;
    #加入支付通道编码
    $sbOld		=	$sbOld.$pd_FrpId;
    #加入应答机制
    $sbOld		=	$sbOld.$pr_NeedResponse;
    #加入用户ID
    $sbOld		=	$sbOld.$pz_userId;
    #加入用户注册时间
    $sbOld		=	$sbOld.$pz1_userRegTime;
    #echo "localhost:".$sbOld;

    logstr($p2_Order,$sbOld,HmacMd5($sbOld,$config["merchantKey"]),$config["merchantKey"]);
    return HmacMd5($sbOld,$config["merchantKey"]);
}
//易宝支付签名函数
function HmacMd5($data,$key)
{
    # RFC 2104 HMAC implementation for php.
    # Creates an md5 HMAC.
    # Eliminates the need to install mhash to compute a HMAC
    # Hacked by Lance Rushing(NOTE: Hacked means written)

    #需要配置环境支持iconv，否则中文参数不能正常处理
    $key = @iconv("GBK","UTF-8",$key);
    $data = @iconv("GBK","UTF-8",$data);

    $b = 64; # byte length for md5
    if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
    }
    $key = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack("H*",md5($k_ipad . $data)));

}
//记录易宝支付订单签名记录
function logstr($orderid,$str,$hmac,$keyValue)
{
    $config=c_pay_pool::$yeepay_pay;
    $james=fopen($config["logName"],"a+");
    fwrite($james,"\r\n".date("Y-m-d H:i:s")."|orderid[".$orderid."]|str[".$str."]|hmac[".$hmac."]|keyValue[".$keyValue."]");
    fclose($james);
}


#易宝支付取得返回串中的所有参数.
function getCallBackValue(&$r0_Cmd,&$r1_Code,&$p1_MerId,&$p2_Order,&$p3_Amt,&$p4_FrpId,&$p5_CardNo,&$p6_confirmAmount,&$p7_realAmount,
&$p8_cardStatus,&$p9_MP,&$pb_BalanceAmt,&$pc_BalanceAct,&$hmac)
{

    $r0_Cmd = $_REQUEST['r0_Cmd'];
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
    $r2_TrxId=$_REQUEST['r2_TrxId'];
    $pb_BalanceAmt = $_REQUEST['pb_BalanceAmt'];
    $pc_BalanceAct = $_REQUEST['pc_BalanceAct'];
    $hmac = $_REQUEST['hmac'];

    return null;
}

#易宝支付验证返回参数中的hmac与商户端生成的hmac是否一致.
function CheckHmac($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,
$pc_BalanceAct,$hmac)
{
    if($hmac==getCallbackHmacString($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,
        $p4_FrpId,$p5_CardNo,$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct))
            return true;
        else
            return false;

}


#易宝支付调用签名函数生成签名串.
function getCallbackHmacString($r0_Cmd,$r1_Code,$p1_MerId,$p2_Order,$p3_Amt,$p4_FrpId,$p5_CardNo,
$p6_confirmAmount,$p7_realAmount,$p8_cardStatus,$p9_MP,$pb_BalanceAmt,$pc_BalanceAct)
{

    $config=c_pay_pool::$yeepay_pay;
    #进行校验码检查 取得加密前的字符串
    $sbOld="";
    #加入业务类型
    $sbOld = $sbOld.$r0_Cmd;
    $sbOld = $sbOld.$r1_Code;
    $sbOld = $sbOld.$config['p1_MerId'];
    $sbOld = $sbOld.$p2_Order;
    $sbOld = $sbOld.$p3_Amt;
    $sbOld = $sbOld.$p4_FrpId;
    $sbOld = $sbOld.$p5_CardNo;
    $sbOld = $sbOld.$p6_confirmAmount;
    $sbOld = $sbOld.$p7_realAmount;
    $sbOld = $sbOld.$p8_cardStatus;
    $sbOld = $sbOld.$p9_MP;
    $sbOld = $sbOld.$pb_BalanceAmt;
    $sbOld = $sbOld.$pc_BalanceAct;

    #echo "[".$sbOld."]";
    logstr($p2_Order,$sbOld,HmacMd5($sbOld,$config['merchantKey'],$config['merchantKey']));
    return HmacMd5($sbOld,$config['merchantKey']);

}
?>