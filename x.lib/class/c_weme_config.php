<?php
class c_weme_config implements i_dispatch {

    function ini($_v_cmd) {
        switch ($_v_cmd) {
            case 500: {                     $r = $this->ini_get_common_problem();                                 break;                } //获取常见问题
            case 501: {                     $r = $this->ini_user_feedback();                                      break;                } //意见反馈

            default : {                   $r = $this->ini_error();                                      break;                  }
        }
        return $r;
    }

    function ini_error() {
        return gf_get_error(0, 'c_weme_config.error');
    }

    /**
     * 获取常见问题
     * @param int userid 用户ID
     * @param string login_token 登录token
     * @param string package_name 包名
     * @param int limit 每页显示的个数 不传值则不分页
     * @param int page 当前的页码 不传值则不分页
     * @return json
     * @author sky 2015-04-01
     */
    function ini_get_common_problem(){
        $param = gf_get_param(gf_get_value_s("param"));
        if(!$userid=$param["userid"]){
            return gf_get_error(500.1, "用户ID不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(500.2, "包名不能为空");
        }
        $game_id=gf_db_get_value('select id from relational_game where package_name="'.$package_name.'"', c_db_pool::$db_weme_pay);
        if(!$game_id){
            return gf_get_error(500.3, "包名错误");
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
            $sql = "select * from common_problem where game_id=".$game_id." order by id asc limit $start,$limit";
        }else{
            $sql = "select * from common_problem where game_id=".$game_id." order by id asc";
        }
        $problem=array();
        $results = gf_db_query($sql, c_db_pool::$db_weme_pay);
        foreach ($results as $key=>$val) {
            $problem[$key]['id']=$val->get("id");
            $problem[$key]['adate']=$val->get("adate");
            $problem[$key]['question']=$val->get("question");
            $problem[$key]['answer']=$val->get("answer");
        }
        return gf_get_success_encrypt(500, array("limit"=>$limit,"page"=>$page,"problem"=>array_values($problem)));
    }

    /**
     * 意见反馈
     * @param int userid 用户ID
     * @param string userid 用户ID
     * @param string login_token 登录token
     * @param string package_name 包名
     * @param string content 反馈问题详情
     * @param string phone 手机号码
     * @param string qq QQ号
     * @param string game_area 游戏分区
     * @param string game_role_name 游戏角色名
     * @return json
     * @author sky 2015-04-01
     */
    function ini_user_feedback(){
        $param = gf_get_param(gf_get_value_s("param"));
        if(!$userid=$param["userid"]){
            return gf_get_error(501.1, "用户ID不能为空");
        }
        if(!$package_name=$param["package_name"]){
            return gf_get_error(501.2, "包名不能为空");
        }
        $game_id=gf_db_get_value('select id from relational_game where package_name="'.$package_name.'"', c_db_pool::$db_weme_pay);
        if(!$game_id){
            return gf_get_error(501.3, "包名错误");
        }
        if(!$content=$param["content"]){
            return gf_get_error(501.4, "反馈内容不能为空");
        }
        if(!$phone=$param["phone"]){
            return gf_get_error(501.5, "手机号码不能为空");
        }
        if(!$qq=$param["qq"]){
            return gf_get_error(501.6, "QQ号不能为空");
        }
        if(!$game_area=$param["game_area"]){
            return gf_get_error(501.7, "游戏分区不能为空");
        }
        if(!$game_role_name=$param["game_role_name"]){
            return gf_get_error(501.8, "游戏角色名不能为空");
        }
        $sql='insert into user_feedback (`userid`,`game_id`,`content`,`phone`,`qq`,`game_area`,`game_role_name`) values ('.$userid.','.$game_id.',"'.$content.'","'.$phone.'","'.$qq.'","'.$game_area.'","'.$game_role_name.'")';
        $id=gf_db_exec($sql, c_db_pool::$db_weme_pay);
        if($id){
           return gf_get_success_encrypt(501, array("id"=>$id));
        }else{
           return gf_get_error(501.9, "反馈失败");
        }
    }

}

