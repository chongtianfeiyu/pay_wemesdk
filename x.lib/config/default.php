<?php

/*
 * mysql read svr pool
 */

class c_db_pool {

    public static $db_weme_pay = "db_weme_pay";

}

//读数据库
global $g_mysql_read;
$g_mysql_read[c_db_pool::$db_weme_pay][] = array(
    'db_server_name' => 'a.db.p.wemesdk.com',
    'db_server_port' => '3306',
    'db_database_name' => 'db_weme_pay',
    'db_database_login_username' => 'db_weme_pay',
    'db_database_login_password' => 'es#@###)($sse452Xs',
);
//写数据库
global $g_mysql_write;
$g_mysql_write[c_db_pool::$db_weme_pay][] = array(
    'db_server_name' => 'a.db.p.wemesdk.com',
    'db_server_port' => '3306',
    'db_database_name' => 'db_weme_pay',
    'db_database_login_username' => 'db_weme_pay',
    'db_database_login_password' => 'es#@###)($sse452Xs',
);

class c_pay_pool {
    public static $alipay_pay = "alipay_pay";
    public static $tencent_pay = "tencent_pay";
    public static $unionpay_pay = "unionpay_pay";
    public static $yeepay_pay = "yeepay_pay";
    public static $pp_pay = "pp_pay";
   /* public $alipay_pay = array(
        'partner' => '2088111839722914',
        'seller' => '2088111839722914',
        'rsaPrivate' => 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMjBWzgnEYVjOgS5IqvElJ7N+S60Jrgu641i+qPIgHuewecXlF/mUzt5866HtIxGzVZV7gCUU4ox+dGambQZl0BBaK9b/oHj4h6vas0qJCb3kzRJtD4iiyp5JNjOqLjYDAYzhPLnR4TB4AlGHd1Djl7OggZkL7JVxfYKf3KnWUw1AgMBAAECgYAdS/pf63OnH5/r6IiiwIFf23ct1aSA5HgDnhotpKj7YWOyscI5bIdg+p8RLUSv4/U7UDb3Zq36UOjAKeucWM+1kqmYjx1mXe7rk8X1VHyNZYGKXDrprEOxU299jVZ6ZWrBt20J+EbP0oPmZU287y6RecC3I6vzG2KCSPCjjudUgQJBAOXjQAMMEb6tSURJN6cLRgJCmfh/GoIgEOiRlFbGE9S2kLZ9ShCToEocJsRf3ZtBcoxXmMDCOpM6oKnKwT9k0e0CQQDfjvwfgYxt3wgYwTFx6nfDINqeJ8/QdJYbvfYSfa+4/2Y57nYNm5XYIktmweZ4xojj/oGmV8QCooXJjevFPbppAkEAkqCs7oSfONh8N+LfbVtibwSeAoLHnKHpRv27+NDkSzOa/9rQB9yxSzPDglOHHITVFDC3DJOWGtw1J7dcJRwgTQJAZoNGkZvyLR+sss1EQxxlNpEGLqfK36fWfXoqMGh5/7b9xWrXr328xJNVSBV5/b/sXcAC66grNZoZB0eQ6a1YoQJBAJDSKN5VSoA45IQ4K2dRHXmW5NEpBqr0NZnRoMxp7UohtfTqYvAtGzwN+b7iB9fsCw5ayIHiKpVbI0qYZ38OqAM=',
        'rsaPublic' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIwVs4JxGFYzoEuSKrxJSezfkutCa4LuuNYvqjyIB7nsHnF5Rf5lM7efOuh7SMRs1WVe4AlFOKMfnRmpm0GZdAQWivW/6B4+Ier2rNKiQm95M0SbQ+IosqeSTYzqi42AwGM4Ty50eEweAJRh3dQ45ezoIGZC+yVcX2Cn9yp1lMNQIDAQAB',
        'notify_url' => 'https://p.wemesdk.com/1.0.0/dispatch.php?v_class=300&v_cmd=300',
    );
    public $tencent_pay = array(
        'weixin_pay11' => '111',
        'weixin_pay22' => '222',
        'weixin_pay33' => '333',
    );
    public $unionpay_pay = array(
        'unionpay_pay11' => '111',
        'unionpay_pay22' => '222',
        'unionpay_pay33' => '333',
    );
    public $yeepay_pay = array(
        'yeepay_pay11' => '111',
        'yeepay_pay22' => '222',
        'yeepay_pay33' => '333',
    );
    public $mo9_pay = array(
        'merchantId' => 'wkswind@gmail.com',
        'appId' => 'com.weme.pay',
        'privateKey' => '6468348f426f4993905e07b71d80645c',
        'notify_url' => 'https://p.wemesdk.com/1.0.0/dispatch.php?v_class=300&v_cmd=305',
        'request_url' => 'https://sandbox.mo9.com/gateway/mobile.shtml?m=mobile',
    );
    public $pp_pay = array(
        'merchantId' => 'wkswind@gmail.com',
        'notify_url' => 'https://p.wemesdk.com/1.0.0/dispatch.php?v_class=300&v_cmd=260',
    );
*/
}
c_pay_pool::$alipay_pay =array(
    'partner'                   => '2088911214258366',
    'seller'                    => '2088911214258366',
    'rsaPrivate'                => 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMjBWzgnEYVjOgS5IqvElJ7N+S60Jrgu641i+qPIgHuewecXlF/mUzt5866HtIxGzVZV7gCUU4ox+dGambQZl0BBaK9b/oHj4h6vas0qJCb3kzRJtD4iiyp5JNjOqLjYDAYzhPLnR4TB4AlGHd1Djl7OggZkL7JVxfYKf3KnWUw1AgMBAAECgYAdS/pf63OnH5/r6IiiwIFf23ct1aSA5HgDnhotpKj7YWOyscI5bIdg+p8RLUSv4/U7UDb3Zq36UOjAKeucWM+1kqmYjx1mXe7rk8X1VHyNZYGKXDrprEOxU299jVZ6ZWrBt20J+EbP0oPmZU287y6RecC3I6vzG2KCSPCjjudUgQJBAOXjQAMMEb6tSURJN6cLRgJCmfh/GoIgEOiRlFbGE9S2kLZ9ShCToEocJsRf3ZtBcoxXmMDCOpM6oKnKwT9k0e0CQQDfjvwfgYxt3wgYwTFx6nfDINqeJ8/QdJYbvfYSfa+4/2Y57nYNm5XYIktmweZ4xojj/oGmV8QCooXJjevFPbppAkEAkqCs7oSfONh8N+LfbVtibwSeAoLHnKHpRv27+NDkSzOa/9rQB9yxSzPDglOHHITVFDC3DJOWGtw1J7dcJRwgTQJAZoNGkZvyLR+sss1EQxxlNpEGLqfK36fWfXoqMGh5/7b9xWrXr328xJNVSBV5/b/sXcAC66grNZoZB0eQ6a1YoQJBAJDSKN5VSoA45IQ4K2dRHXmW5NEpBqr0NZnRoMxp7UohtfTqYvAtGzwN+b7iB9fsCw5ayIHiKpVbI0qYZ38OqAM=',
    'rsaPublic'                 => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIwVs4JxGFYzoEuSKrxJSezfkutCa4LuuNYvqjyIB7nsHnF5Rf5lM7efOuh7SMRs1WVe4AlFOKMfnRmpm0GZdAQWivW/6B4+Ier2rNKiQm95M0SbQ+IosqeSTYzqi42AwGM4Ty50eEweAJRh3dQ45ezoIGZC+yVcX2Cn9yp1lMNQIDAQAB',
    'notify_url'                => 'http://p.wemesdk.com/pay.x/1.0.0/300/300/pay.html',
    'private_key_path'	        => $_SERVER["DOCUMENT_ROOT"].'/1.0.0/x.lib/config/key/rsa_private_key.pem',
    'ali_public_key_path'	    => $_SERVER["DOCUMENT_ROOT"].'/1.0.0/x.lib/config/key/alipay_public_key.pem',
    'sign_type'                 => strtoupper('RSA'),
    'input_charset'             => strtolower('utf-8'),
    'cacert'                    => $_SERVER["DOCUMENT_ROOT"].'/1.0.0/x.lib/config/key/cacert.pem',
    'transport'                 => 'http',
);
c_pay_pool::$tencent_pay =array(
    'weixin_pay11' => '111',
    'weixin_pay22' => '222',
    'weixin_pay33' => '333',
);
c_pay_pool::$unionpay_pay =array(
    'unionpay_pay11' => '111',
    'unionpay_pay22' => '222',
    'unionpay_pay33' => '333',
);
c_pay_pool::$yeepay_pay =array(
    'p1_MerId'	            => "10001126856",
	'merchantKey'	        => "69cl522AV6q613Ii4W6u8K6XuW8vM1N6bFgyv769220IuYe9u37N4y7rI4Pl",
	'logName'	            => $_SERVER["DOCUMENT_ROOT"].'/1.0.0/log/yeepay/YeePay_CARD.log',
	'reqURL_SNDApro'		=> "https://www.yeepay.com/app-merchant-proxy/command.action",
	'notify_url'                => 'http://p.wemesdk.com/pay.x/1.0.0/300/303/pay.html',
);
c_pay_pool::$pp_pay = array(
    'merchantId' => '2015040114',
    'notify_url' => 'http://p.wemesdk.com/pay.x/1.0.0/300/304/pay.html',
    'private_key_path' => $_SERVER["DOCUMENT_ROOT"] . '/1.0.0/x.lib/config/key/PPFrontAPIKey.pem',
    'public_key_path' => $_SERVER["DOCUMENT_ROOT"] . '/1.0.0/x.lib/config/key/PPFrontAPI.cer',
);
?>