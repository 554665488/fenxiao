<?php
class WXLoginModel{
    //APP
    private $APPID= 'wx1e92f77d1e35c159';
    private $App_Secret= '5b3cf4d5a9026e0b438d3873ff8af5a8';
    //web
    private $webAppID= 'xxxxx';
    private $webAppSecret= 'xxxxx';
    //小程序
    private $wxsmAppID= 'xxxx';
    private $wxsmAppSecret = 'xxxxx';
    private $curl;
    private $openId= '';
    private $access_token= '';
    // private $code= '';
    private $LPM= '';
    private $user_id= '';
    private $redirect_uri = '';
    private $user_msg= '';
    private $union_id= '';
    private $nickname= '';
    private $headimgurl = '';
    private $token= '';
    private $unionid= '';
    private $session_key= '';
    private $url= '';
    public$phone= '';
    public function __construct(){
        $this->curl = new LibCurl(); 
    }
        //请求地址
    public function request_curl($url){
        $this->curl->setUrl($url);
        $list = $this->curl->execute(true);
        return $list;
    }
    ########## WEB登录 #########//WEB登录//第一步获取code
    public function index_get_code(){
        if ($_GET['id']) {
            unset($_SESSION['belong_refer']);
            $_SESSION['belong_refer'] = $_GET['id'];
            }
            if ($_GET['type']) {
                unset($_SESSION['type']);
                $_SESSION['type'] = $_GET['type'];
            }
            $this->redirect_uri = WAP_SITE_URL.'/index.php?act=connect&op=indexapi';
            $this->redirect_uri = urlencode($this->redirect_uri);
            $url= 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->webAppID . '&redirect_uri=' . $this->redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=jjds&connect_redirect=1#wechat_redirect';
            header("location:" . $url);
        }
    //第二步：通过code换取网页授权access_token
    private function get_openId(){
        $code = isset($_GET['code']) ? $_GET['code'] : $_POST['code'];
        $url= 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->webAppID . '&secret=' . $this->webAppSecret . '&code=' . $code . '&grant_type=authorization_code';
        $this->curl = new LibCurl();
        $this->curl->setUrl($url);
        $return= $this->curl->execute(true);
        $this->access_token = $return->access_token;
        $this->openId = $return->openid;}
    //第三步：拉取用户信息(需scope为 snsapi_userinfo)
    private function get_one_user_msg_code(){
        $url= 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $this->access_token . '&openid=' . $this->openId . '&lang=zh_CN';
        $this->curl = new LibCurl();
        $this->curl->setUrl($url);
        $this->user_msg = $this->curl->execute(true);
    }
    //推广页登录
    public function webLogin(){
        $this->web_wx_login_index();
        header("location:http://xxxxxxxx/wap/html/generalize.html?type=" . $_SESSION['type'] . "&a=" . $_SESSION['token']);}
    //微信公众号登陆
    private function web_wx_login_index(){
        $this->get_openId();
        //通过code换取网页授权access_token ，openId
        $this->get_one_user_msg_code(); 
        //获取用户信息.
        $this->union_id = $this->user_msg->unionid;
        $this->wx_login($this->union_id, 4, '', $this->user_msg);
        $_SESSION['token'] = empty($this->token) ? '' : $this->token;
    }
    ########## APP登录 #########//APP获取access_token
    public function app_get_access_token(){
        $code = isset($_GET['code']) ? $_GET['code'] : $_POST['code'];
        $url= 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->APPID . '&secret=' . $this->App_Secret . '&code=' . $code . '&grant_type=authorization_code';
        $list = $this->request_curl($url);
        return $list;
    }
    //APP获取调用（UnionID）
    public function app_UnionID($access_token, $openId){
        $url= 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openId;
        $list = $this->object_to_array(WXLoginModel::request_curl($url));
        return $list;
    }
    ########## 小程序登录 #########//获取code//微信小程序登陆
    public function sm_wx_login(){
        $code = isset($_GET['code']) ? $_GET['code'] : $_POST['code'];
        $this->url= 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->wxsmAppID . '&secret=' . $this->wxsmAppSecret . '&js_code=' . $code . '&grant_type=authorization_code';
        $this->curl = new LibCurl();$this->curl->setUrl($this->url);
        $return= $this->curl->execute(true);
        $this->user_msg= $return;
        $this->openId= $return->openid;
        $this->unionid= $return->unionid;
        $this->session_key = $return->session_key;
    }
    //获取code//微信小程序登陆
    public function sm_login(){
        $this->wx_login($this->unionid, 1);
        output_data(['token' => $this->token, 'phone' => $this->phone]);
    }
    //登陆(注册)
    public function wx_login($unionid, $clien_id, $status, $alllist){
        if (isset($_POST['phone']) && $_POST['phone'] != 'undefined') {
            //存在手机号码（绑定微信）
            if ($_POST['status'] == 2) {
                $user_id = $this->get_one_data('users', 'user_id', ['user_name' => $unionid])['user_id'];
                if ($user_id) {
                    //已经有微信账户的
                    $this->get_one_delete('users', ['phone' => $_POST['phone']]);
                    $this->get_update_data(['phone' => $_POST['phone']], 'users', ['user_name' => $unionid]);
                    $token = $this->LPM->_get_token($user_id, $unionid, 3);
                    return $token;
                } 
                else 
                {
                     //没有微信账户的
                     $user_ids = $this->get_one_data('users', 'user_id', ['phone' => $_POST['phone']])['user_id'];
                     $this->get_update_data(['user_name' => $unionid], 'users', ['phone' => $_POST['phone']]);
                     $this->user_info(['phone' => $_POST['phone']], $alllist);
                     $token = $this->LPM->_get_token($user_ids, $unionid, 3);
                     return $token;
                }
            }
            // //检测是否重复
            // $list=$this->get_one_data('users','role_type,user_B_id,belong_refer',['user_name' => $unionid]);
            // $user_b_id=$list['user_b_id'];
            // $role_type=$list['role_type'];
            // $belong_refer=$list['belong_refer'];
            // $this->get_one_delete('users',['user_name' => $unionid]);
            // if($list){
                //
                //绑定，更新信息
                //$this->get_update_data(['user_name' => $unionid,'user_b_id'=>$user_b_id,'role_type'=>$role_type,'belong_refer'=>$belong_refer], 'users', ['phone' => $_POST['phone']]);
                // }else{//
                    //绑定，更新信息
                    //$this->get_update_data(['user_name' => $unionid], 'users', ['phone' => $_POST['phone']]);
                    // }
                // $this->user_info(['phone' => $_POST['phone']], $alllist);
             } 
             else 
            {
                    //检测是否注册过
                    if ($unionid != "" && !empty($unionid)) {
                        $option = $this->option($unionid);
                    } 
                    else {
                         $option = $this->option($this->openId);
                        }
                        $rst = M()->selectOne($option);
                        if (!$rst) {
                            //注册
                            if ($unionid != "" && !empty($unionid)) {
                                $token = $this->regist($unionid, $clien_id);
                            } else {
                                $token = $this->regist($this->openId, $clien_id);
                            }
                            if ($alllist) {
                                //获取用户信息
                                $this->user_info(['user_id' => $this->user_id], $alllist);
                            }
                            $phone = $this->get_one_data('users', 'phone', ['user_id' => $this->user_id])['phone'];
                            //做C端归属处理
                            $this->user_belong();
                        } else {
                            //已注册
                            if ($status == 2) {
                                if ($alllist) {
                                    //获取用户信息
                                    $this->user_info(['user_id' => $this->user_id], $alllist);
                                }
                                $token = '';
                            } else {
                                //登录（更新密钥）
                                if ($unionid != "" && !empty($unionid)) {
                                    $token = $this->LPM->_get_token($rst['user_id'], $unionid, $clien_id);
                                } else {
                                    $token = $this->LPM->_get_token($rst['user_id'], $this->openId, $clien_id);
                                }
                                $this->token = $token;
                                if ($alllist) {
                                    //获取用户信息
                                    $this->user_info(['user_id' => $this->user_id], $alllist);
                                }
                            }
                        }
                        $this->phone = $this->get_one_data('users', 'phone', ['user_id' => $rst['user_id']])['phone'];
                        $this->curl->close();
                        return ['token' => $token, 'phone' => $this->phone];
                    }
            }
    //做C端归属处理
    public function user_belong(){
    if ($_SESSION['belong_refer']) {
        //有推荐人，做归属
        $user_B_id = $this->get_one_data('users', 'user_B_id', ['user_id' => $_SESSION['belong_refer']])['user_b_id'];
        $this->get_update_data(['belong_refer' => $_SESSION['belong_refer'], 'user_B_id' => $user_B_id], 'users', ['user_id' => $this->user_id]);
        }
    }
    //更新用户信息
    public function user_info($where, $alllist){
            $alllist = $this->object_to_array($alllist);
            $new= $this->get_one_data('users', 'nickname,heard_img,city', $where);
            if (empty($new['nickname']) || empty($new['heard_img']) || empty($new['city'])) {
                $list = $this->get_update_data(['nickname' => $this->encrypt($alllist['nickname']), 'heard_img' => $alllist['headimgurl'], 'city' => $alllist['city']], 'users', $where);
            }
        }
                //查询参数
    private function option($unionid){
        $option = [
            'field'=> 'user_id,user_name',
            'table'=> 'users',
            'where'=> ['user_name' => $unionid],
            'is_admin' => 0,
        ];
        return $option;
    }
    //注册参数
    private function regist($unionid, $clien_id){
        $data = ['user_name' => $unionid,'password'=> md5('000000'),'add_time'=> time(),'clien_id'=> $clien_id,];$option = ['table' => 'users',];$list = M()->insert($data, $option);if (!$list) {output_errors('增加用户失败！');}$this->user_id = M()->getLastInsID();$this->token = $this->LPM->_get_token($this->user_id, $unionid, $clien_id);
        return $this->token;}
        /** 微信更新手机号码*/
        public function up_phone(){
            $encryptedData = isset($_POST['encryptedData']) ? $_POST['encryptedData'] : output_errors('失败');
            $iv= isset($_POST['iv']) ? $_POST['iv'] : output_errors('失败');
            $pc= new WXBizDataCrypt($this->wxsmAppID, $this->session_key);
            $errCode = $pc->decryptData($encryptedData, $iv, $data);
            if ($errCode == 0) {
            } else {
                output_errors('解密失败');
            }
            $user_id = $this->is_index();
            $data= $this->new_str_change($data);
            $list= $this->get_update_data(['phone' => $data['phoneNumber']], 'users', ['user_id' => $user_id]);
            if (!$list) {
                output_errors('绑定手机失败，请重试');
            }
            output_data('绑定手机成功');
        }
    } 
