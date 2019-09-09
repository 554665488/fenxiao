<?php
defined('In33hao') or exit('Access Invalid!');
class WXLoginControl extends mobileHomeControl {

    private $url= '';
    private $WLM= '';
    private $openId= '';
    private $access_token = '';
    private $unionid= ''; 
    private $code   = ''; 
    private $client = '';
    private $alllist= '';  
    public function __construct() {
        parent::__construct();
        $this->WLM = new WXLoginModel(); 
    } 
  
    /*** 微信web登录*/
     public function web_wx_loginOp(){  
         $this->WLM->index_get_code(); 
         } 
    //微信web登录 
    public function webLoginOp(){  
        $this->WLM->webLogin(); 
        } 
    /*** 微信小程序登录*/ 
    public function sm_wx_loginOp(){  
        $this->WLM->sm_wx_login();  
        $this->WLM->sm_login(); 
    } 
    /*** 微信app登录(*/ 
    public function wx_loginOp(){  
        //注册端 小程序1  APP 2 后台添加3  4WEB注册1是登录注册 2是绑定微信 
         $this->client =2; 
          $status = isset($_POST['status'])?$_POST['status']:1;  
          $this->app_get_access_token(); 
          $this->app_UnionID();    
          output_data($this->unionid);  
          }
    //APP获取code 
    public function app_get_access_token(){  
        $list= $this->WLM->app_get_access_token();  
        $this->access_token = $list->access_token;  
        $this->openId = $list->openid; 
        } 
    //APP获取调用（UnionID） 
    private function app_UnionID(){  
        $list= $this->WLM->app_UnionID($this->access_token, $this->openId);  
        $this->unionid = $list['unionid'];  
        $this->alllist = $list; 
    } 
    /** 绑定手机号码*/ 
    public function up_phone(){  
        $this->WLM->sm_wx_login();  
        $this->WLM->up_phone(); 
        }
   }
