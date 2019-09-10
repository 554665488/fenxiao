<?php
/**
 * 前台登录 退出操作
 *
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */


defined('In33hao') or exit('Access Invalid!');

class loginControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function indexOp()
    {
        if (empty($_POST['username']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('登录失败');
        }

        $model_member = Model('member');

        $login_info = array();
        $login_info['user_name'] = $_POST['username'];
        $login_info['password'] = $_POST['password'];
        $member_info = $model_member->login($login_info);
        if (isset($member_info['error'])) {
            output_error($member_info['error']);
        } else {

            //登录生成token V5.3 同步登录
            $model_seller = Model('seller');
            $seller_info = $model_seller->getSellerInfo(array('member_id' => $member_info['member_id']));
            $sellerinfo = array();
            if ($seller_info) {
                //读取店铺信息
                $model_store = Model('store');
                $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);
                //更新卖家登陆时间
                $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));

                //生成登录令牌
                $token = $this->_get_seller_token($seller_info['seller_id'], $seller_info['seller_name'], 'wap');
                $sellerinfo = array('seller_name' => $seller_info['seller_name'], 'store_name' => $store_info['store_name'], 'key' => $token);
            }
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token, 'sell' => $sellerinfo));
            } else {
                output_error('登录失败');
            }
        }
    }

    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client)
    {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if ($result) {
            return $token;
        } else {
            return null;
        }

    }

    /**
     * 登录生成token V5.3 同步登录
     */
    private function _get_seller_token($seller_id, $seller_name, $client)
    {
        $model_mb_seller_token = Model('mb_seller_token');

        //重新登录后以前的令牌失效
        $condition = array();
        $condition['seller_id'] = $seller_id;
        $model_mb_seller_token->delSellerToken($condition);

        //生成新的token
        $mb_seller_token_info = array();
        $token = md5($seller_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_seller_token_info['seller_id'] = $seller_id;
        $mb_seller_token_info['seller_name'] = $seller_name;
        $mb_seller_token_info['token'] = $token;
        $mb_seller_token_info['login_time'] = TIMESTAMP;
        $mb_seller_token_info['client_type'] = $client;

        $result = $model_mb_seller_token->addSellerToken($mb_seller_token_info);

        if ($result) {
            return $token;
        } else {
            return null;
        }
    }

    /**
     * 注册
     */
    public function registerOp()
    {
        $model_member = Model('member');
        $recmember_name = '';
        $invite_id = '';
        $invite_reward_id = '';
        $invite_reward_name = '';

        if(empty($_POST['code'])) {
            output_error('请输入验证码');
        }else{
            $code = $_POST['code'];
            $redisConfig = C('redis');
            $redis = new Redis();
            $redis->connect($redisConfig['host'], $redisConfig['port']);
            if(!$redis->exists('code_' . $_POST['phone']) or  $redis->get('code_' . $_POST['phone']) != $code){
                output_error('验证码错误');
            }
        }

        if (empty($_POST['nodemember'])) {
            output_error('请输入节点人');
        } else {
            $recmember_name = trim($_POST['nodemember']);
        }
        if (!empty($_POST['recmember'])) {
            $invite_reward_name = trim($_POST['recmember']);
        }
        if (!empty($recmember_name)) {
            $recmember = $model_member->getMemberInfo(array('member_name' => $recmember_name));
            if (empty($recmember)) {
                output_error('找不到节点人');
            }
            $invite_id = $recmember['member_id'];
            if (empty($recmember['inviter_ids'])) {
                $invite_ids = "," . $recmember['member_id'] . ",";
            } else {
                $invite_ids = $recmember['inviter_ids'] . $recmember['member_id'] . ",";
            }
        }

        if (!empty($invite_reward_name)) {
            $invite_reward_member = $model_member->getMemberInfo(array('member_name' => $invite_reward_name));
            if (empty($invite_reward_member)) {
                output_error('找不到推荐人');
            }
            $invite_reward_id = $invite_reward_member['member_id'];
        }

        if (!empty($invite_id)) {
            $member = $model_member->getMemberInfo(array('member_id' => $invite_id));
            $invite_one = $invite_id;
            $invite_two = $member['invite_one'];
            $invite_three = $member['invite_two'];
        } else {
            $invite_one = 0;
            $invite_two = 0;
            $invite_three = 0;
        }
        $register_info = array();
        $register_info['username'] = $_POST['username'];
        $register_info['member_truename'] = $_POST['username'];
        $register_info['password'] = $_POST['phone'];
        $register_info['password_confirm'] = $_POST['phone'];
        $register_info['paypassword'] = $_POST['phone'];
        $register_info['paypassword_confirm'] = $_POST['phone'];
        $register_info['mobile'] = $_POST['phone'];
        //添加奖励积分 v5.1.1
        $register_info['inviter_id'] = $invite_reward_id;
        //33Hao 5.2.1 分销
        $register_info['invite_one'] = $invite_one;
        $register_info['invite_two'] = $invite_two;
        $register_info['invite_three'] = $invite_three;
        $register_info['inviter_ids'] = $invite_ids;
        $member_info = $model_member->register($register_info);
        if (!isset($member_info['error'])) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('注册失败');
            }
        } else {
            output_error($member_info['error']);
        }

    }

    /**
     * 发送短信验证码
     */
    public function sendMobileCodeOp()
    {
        $randCode = mt_rand(9000, 9999);
        $redis = Cache::getInstance('redis');
//        $result = $redis->set('code', $randCode);
//        dump($result);
        $redisConfig = C('redis');
        $redis = new Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port']);
        $cacheResult = $redis->setex('code_' . $_POST['phone'], 120, $randCode);
        if(!$cacheResult)output_error('发送失败');
        output_data(array('mobileCode' => $randCode));
    }

    /**
     * 微信注册
     */
    public function wxregisterOp()
    {
        //txtName:txtName, txtPhone:txtPhone,userinfo:username,infocode:infocode
        $model_member = Model('member');
        $username = trim($_POST['username']);
        $userphone = trim($_POST['Phone']);
        $userinfo = trim($_POST['userinfo']);

        // $hasuserphone = $model_member->getMemberInfo(array('mobile'=>$userphone));
        // if (!empty($hasuserphone)) {
        //     output_error('该手机号已存在不允许绑定，请更换手机号！');
        // }

        $invite_id = '';
        if (!empty($_POST['recmember'])) {
            $recmember_name = trim($_POST['recmember']);
        }
        if (!empty($_POST['unionid'])) {
            $unionid = trim($_POST['unionid']);
        }
        if (!empty($userinfo)) {
            $user_info = json_decode(urldecode($userinfo), true);
            $unionid = $user_info['unionid'];
        }
        if (!empty($recmember_name)) {
            $recmember = $model_member->getMemberInfo(array('member_mobile' => $recmember_name));
            if (empty($recmember)) {
                //output_error('找不到推荐人');
            } else {
                $invite_id = $recmember['member_id'];
                if (empty($recmember['inviter_ids'])) {
                    $invite_ids = "," . $recmember['member_id'] . ",";
                } else {
                    $invite_ids = $recmember['inviter_ids'] . $recmember['member_id'] . ",";
                }
            }
        }

        if (!empty($invite_id)) {
            $member = $model_member->getMemberInfo(array('member_id' => $invite_id));
            $invite_one = $invite_id;
            $invite_two = $member['invite_one'];
            $invite_three = $member['invite_two'];
        } else {
            $invite_one = 0;
            $invite_two = 0;
            $invite_three = 0;
        }
        $register_info = array();
        if (!empty($unionid)) {
            $register_info['weixin_unionid'] = $unionid;
            $register_info['weixin_info'] = '';

        } else if (!empty($user_info)) {
            $register_info['weixin_unionid'] = $unionid;
            $register_info['weixin_info'] = $user_info['weixin_info'];
        }

        $register_info['username'] = $_POST['Phone'];
        $register_info['member_truename'] = $username;
        $register_info['password'] = md5($userphone);
        $register_info['password_confirm'] = md5($userphone);
        $register_info['paypassword'] = md5($userphone);
        $register_info['paypassword_confirm'] = md5($userphone);
        $register_info['mobile'] = $userphone;
        //添加奖励积分 v5.1.1
        $register_info['inviter_id'] = $invite_one;
        //33Hao 5.2.1 分销
        $register_info['invite_one'] = $invite_one;
        $register_info['invite_two'] = $invite_two;
        $register_info['invite_three'] = $invite_three;
        $register_info['inviter_ids'] = $invite_ids;
        $hasunionid = $model_member->getMemberInfo(array('weixin_unionid' => $unionid));
        $hasuserphone = $model_member->getMemberInfo(array('member_mobile' => $userphone));
        // if (!empty($hasuserphone)) {
        //     output_error('该手机号已存在不允许绑定，请更换手机号！');
        // }

        if (!empty($hasunionid) || !empty($hasuserphone)) {
            //绑定手机号
            $update_info = array(
                'member_truename' => $username,
                'member_mobile' => ($userphone),
                'member_passwd' => md5($userphone),
                'weixin_unionid' => $unionid,
                'member_paypwd' => md5($userphone),
                'member_mobile_bind' => 1
            );
            if (!empty($hasunionid)) {
                $model_member->editMember(array('weixin_unionid' => $unionid), $update_info);
                $member_info = $hasunionid;
            } else {
                $model_member->editMember(array('member_mobile' => $userphone), $update_info);
                $member_info = $hasuserphone;
            }

        } else {
            $member_info = $model_member->register($register_info);
        }
        if (!isset($member_info['error'])) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('绑定失败');
            }
        } else {
            output_error($member_info['error']);
        }
    }
}
