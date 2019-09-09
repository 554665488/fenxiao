<?php
/**
 * 预存款
 * * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */
defined('In33hao') or exit('Access Invalid!');
class predepositModel extends Model {
    /**
     * 生成充值编号
     * @return string
     */
    public function makeSn() {
       return mt_rand(10,99)
              . sprintf('%010d',time() - 946656000)
              . sprintf('%03d', (float) microtime() * 1000)
              . sprintf('%03d', (int) $_SESSION['member_id'] % 1000);
    }

    public function addRechargeCard($sn, array $session)
    {
        $memberId = (int) $session['member_id'];
        $memberName = $session['member_name'];

        if ($memberId < 1 || !$memberName) {
            throw new Exception("当前登录状态为未登录，不能使用充值卡");
        }

        $rechargecard_model = Model('rechargecard');

        $card = $rechargecard_model->getRechargeCardBySN($sn);

        if (empty($card) || $card['state'] != 0 || $card['member_id'] != 0) {
            throw new Exception("充值卡不存在或已被使用");
        }

        $card['member_id'] = $memberId;
        $card['member_name'] = $memberName;

        try {
            $this->beginTransaction();

            $rechargecard_model->setRechargeCardUsedById($card['id'], $memberId, $memberName);

            $card['amount'] = $card['denomination'];
            $this->changeRcb('recharge', $card);

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * 取得充值列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_recharge')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加充值记录
     * @param array $data
     */
    public function addPdRecharge($data) {
        return $this->table('pd_recharge')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return $this->table('pd_recharge')->where($condition)->update($data);
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*',$lock = false) {
        return $this->table('pd_recharge')->where($condition)->field($fields)->lock($lock)->find();
    }

    /**
     * 取充值信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return $this->table('pd_recharge')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return $this->table('pd_cash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return $this->table('pd_log')->where($condition)->count();
    }

    /**
     * 取日志金额
     * @param unknown $condition
     */
    public function getPdLogAmount($condition = array()) {
        return $this->table('pd_log')->where($condition)->sum('lg_av_amount');
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_log')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 变更充值卡余额
     *
     * @param string $type
     * @param array  $data
     *
     * @return mixed
     * @throws Exception
     */
    public function changeRcb($type, $data = array())
    {
        $amount = (float) $data['amount'];
        if ($amount < .01) {
            throw new Exception('参数错误');
        }

        $available = $freeze = 0;
        $desc = null;

        switch ($type) {
        case 'order_pay':
            $available = -$amount;
            $desc = '下单，使用充值卡余额，订单号: ' . $data['order_sn'];
            break;

        case 'order_freeze':
            $available = -$amount;
            $freeze = $amount;
            $desc = '下单，冻结充值卡余额，订单号: ' . $data['order_sn'];
            break;

        case 'order_cancel':
            $available = $amount;
            $freeze = -$amount;
            $desc = '取消订单，解冻充值卡余额，订单号: ' . $data['order_sn'];
            break;

        case 'order_comb_pay':
            $freeze = -$amount;
            $desc = '下单，扣除被冻结的充值卡余额，订单号: ' . $data['order_sn'];
            break;

        case 'recharge':
            $available = $amount;
            $desc = '平台充值卡充值，充值卡号: ' . $data['sn'];
            break;

        case 'refund':
            $available = $amount;
            $desc = '确认退款，订单号: ' . $data['order_sn'];
            break;

        case 'vr_refund':
            $available = $amount;
            $desc = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
            break;

        case 'order_book_cancel':
            $available = $amount;
            $desc = '取消预定订单，退还充值卡余额，订单号: ' . $data['order_sn'];
            break;

        default:
            throw new Exception('参数错误');
        }

        $update = array();
        if ($available) {
            $update['available_rc_balance'] = array('exp', "available_rc_balance + ({$available})");
        }
        if ($freeze) {
            $update['freeze_rc_balance'] = array('exp', "freeze_rc_balance + ({$freeze})");
        }

        if (!$update) {
            throw new Exception('参数错误');
        }

        // 更新会员
        $updateSuccess = Model('member')->editMember(array(
            'member_id' => $data['member_id'],
        ), $update);

        if (!$updateSuccess) {
            throw new Exception('操作失败');
        }

        // 添加日志
        $log = array(
            'member_id' => $data['member_id'],
            'member_name' => $data['member_name'],
            'type' => $type,
            'add_time' => TIMESTAMP,
            'available_amount' => $available,
            'freeze_amount' => $freeze,
            'description' => $desc,
        );

        $insertSuccess = $this->table('rcb_log')->insert($log);
        if (!$insertSuccess) {
            throw new Exception('操作失败');
        }

        $msg = array(
            'code' => 'recharge_card_balance_change',
            'member_id' => $data['member_id'],
            'param' => array(
                'time' => date('Y-m-d H:i:s', TIMESTAMP),
                'url' => urlMember('predeposit', 'rcb_log_list'),
                'available_amount' => ncPriceFormat($available),
                'freeze_amount' => ncPriceFormat($freeze),
                'description' => $desc,
            ),
        );

        QueueClient::push('addConsume', array('member_id'=>$data['member_id'],'member_name'=>$data['member_name'],
                'consume_amount'=>$amount,'consume_time'=>time(),'consume_remark'=>$desc));
        // 发送买家消息
        QueueClient::push('sendMemberMsg', $msg);

        return $insertSuccess;
    }

    /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type,$data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_msg = array();

        $data_log['lg_invite_member_id'] = $data['invite_member_id'];
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name'];
        $data_log['lg_add_time'] = TIMESTAMP;
        $data_log['lg_type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        $data_msg['pd_url'] = urlMember('predeposit', 'pd_log_list');
        switch ($change_type){
		case 'sale_register':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = $data['lg_desc'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
          case 'pay_zengzhi':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '购买增值券，支付预存款';
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
		
           case 'sale_zengzhi':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '卖出增值券';
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'order_invite':
                $data_log['lg_av_amount'] = +$data['amount'];
                $data_log['lg_desc'] = '分销，获得推广佣金，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = +$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'recharge':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '充值，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            case 'refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '确认退款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'vr_refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '虚拟兑码退款成功，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_apply':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '提现成功，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_del':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_book_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '取消预定订单，退还预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
                case 'sys_add_money_reward':
                    $data_log['lg_av_amount'] = $data['amount'];
                    $data_log['lg_desc'] = $data['lg_desc'];
                    $data_log['lg_admin_name'] = $data['admin_name'];
                    $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                    $data_pd['fenhong'] = array('exp','fenhong+'.$data['amount']);
                    if(isset($data['lg_invite_member_id'])) {
                      $data_log['lg_invite_member_id'] = $data['lg_invite_member_id'];
                    }
                    $data_msg['av_amount'] = $data['amount'];
                    $data_msg['freeze_amount'] = 0;
                    $data_msg['desc'] = $data_log['lg_desc'];
                    break;
			//好商城新增
			case 'sys_add_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【增加】，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_del_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【减少】，充值单号: '.$data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_freeze_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = +$data['amount'];
				$data_log['lg_desc'] = '管理员调节预存款【冻结】，充值单号: '.$data['pdr_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = +$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'sys_unfreeze_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【解冻】，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'seller_money':
				$msg=$data['msg'];
                $data_log['lg_freeze_amount'] = +$data['amount'];
                $data_log['lg_desc'] = '卖出商品收入,扣除拥金'.$msg.',订单号: '.$data['pdr_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = +$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
			case 'seller_refund':
				$msg=$data['msg'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '商家退款支出,扣除预存款'.$msg.',订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
     case 'predepoit_transfer_from':
                $data_log['lg_av_amount'] = "-" . $data['amount'];

                if (empty($data['lg_desc'])) {
                  $data_log['lg_desc'] = '转账,转入';
                } else {
                  $data_log['lg_desc'] = $data['lg_desc'];
                }
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit-' . $data['amount']);

                $data_msg['av_amount'] = "-" . $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

    case 'predepoit_transfer_to':
                $data_log['lg_av_amount'] = $data['receive_amount'];

                if (empty($data['lg_desc'])) {
                  $data_log['lg_desc'] = '转账,转出';
                } else {
                  $data_log['lg_desc'] = $data['lg_desc'];
                }
                $data_pd['available_predeposit'] = array('exp', 'available_predeposit+' . $data['receive_amount']);
                //$data_pd['available_rc_balance'] = array('exp', 'available_rc_balance+' . $data['receive_amount_rc']);
                //$this->changeRcb('predepoit_transfer_to',
                //array('lg_desc' => $data['lg_desc'], 'amount' => $data['receive_amount_rc'], 'member_id' => $data['member_id'], 'member_name' => $data['member_name']));
                $data_msg['av_amount'] = $data['receive_amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            default:
                throw new Exception('参数错误3');
                break;
        }

        $update = Model('member')->editMember(array('member_id'=>$data['member_id']),$data_pd);

        if (!$update) {
            throw new Exception('操作失败');
        }
        $insert = $this->table('pd_log')->insert($data_log);
        if (!$insert) {
            throw new Exception('操作失败');
        }

        // 支付成功发送买家消息
        $param = array();
        $param['code'] = 'predeposit_change';
        $param['member_id'] = $data['member_id'];
        $data_msg['av_amount'] = ncPriceFormat($data_msg['av_amount']);
        $data_msg['freeze_amount'] = ncPriceFormat($data_msg['freeze_amount']);
        $param['param'] = $data_msg;
        QueueClient::push('addConsume', array('member_id'=>$data['member_id'],'member_name'=>$data['member_name'],
        'consume_amount'=>$data['amount'],'consume_time'=>time(),'consume_remark'=>$data_log['lg_desc']));
        QueueClient::push('sendMemberMsg', $param);
        return $insert;
    }

    /**
     * 删除充值记录
     * @param unknown $condition
     */
    public function delPdRecharge($condition) {
        return $this->table('pd_recharge')->where($condition)->delete();
    }

    /**
     * 取得提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_cash')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加提现记录
     * @param array $data
     */
    public function addPdCash($data) {
        return $this->table('pd_cash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdCash($data,$condition = array()) {
        return $this->table('pd_cash')->where($condition)->update($data);
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdCashInfo($condition = array(), $fields = '*') {
        return $this->table('pd_cash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delPdCash($condition) {
        return $this->table('pd_cash')->where($condition)->delete();
    }

    public function setOrderApointsPrice($order_id) {
      $price = Model('points')->getLastPrice();
      $order_model = Model('order');
      $order_model->editOrder(array('apoints_get_price' => $price), array('order_id' => $order_id));
    }
	
	public function settleSaleOrder($order_ids) {
      if(empty($order_ids)) {
        return false;
      }
      $model_order = Model('order');
      $model_member = Model('member');
      $model_setting = Model('setting');
      $list_setting = $model_setting->getRewardSetting();
      $rate = 0.3;
      if(floatval($list_setting['sale_fee']) > 0) {
        $rate = floatval($list_setting['sale_fee']);
      }
      $startdate = strtotime("2019-02-27");
      foreach ($order_ids as $order_id) {
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id, 'is_wholesale' => 1, 'sale_state' => 1, 'order_state' => array('egt',20)),array('order_goods'));
        if(empty($order_info)) {
			continue;
		}

		//$salecount = $model_order->getOrderCount(array('buyer_id' => $order_info['buyer_id'], 'order_state' => array('egt',20), 'is_wholesale' => 1));

		$order_total = 0;
		//var_dump($order_info);exit();
        foreach ($order_info['extend_order_goods'] as $value) {
            $discount = floatval($order_info['discounttype']);
            //$price = $value['goods_price'] * $value['goods_num'];
			$price = $value['return_point'];
            if($discount <= 0.01) {
              $discount = 1;
            }
            $total_price = floatval($price) * 10 / $discount;
            $order_total += $total_price;
        }
        $member_info = $model_member->getMemberInfo(array('member_id' => $order_info['buyer_id']));

        $fee = $order_total * $rate;
        $amount = $order_total - $fee;
        $this->genReward4('交易成功', '', $member_info, $order_info['order_sn'], $order_info['pay_sn'], $amount, "交易商品，获得总额"
          . ncPriceFormat($order_total) . ", 扣除管理费" . ncPriceFormat($fee));
        $model_order->editOrder(array('sale_state' => 20, 'sale_finish_date' => time(),'sale_fee' => $fee, 'sale_amount' => $amount),array('order_id' => $order_info['order_id']));
      }
      return true;
    }
	
    /*
   * 结算订单
   */
   public function settleOrder() {
     $rs = array();
     $model_member = Model('member');
     $model_setting = Model('setting');
     $list_setting = $model_setting->getRewardSetting();
     $startdate = strtotime(date('Y-m-d'));
     //$startdate = strtotime("2017-01-01");
     $fp = fopen('../lock', 'w+');
     flock($fp, LOCK_EX);
     /*订单相关*/
     $orders = $this->table('orders')->where(array('is_settle' => 0, 'order_state' => array('egt',20), 'payment_time' => array('gt',$startdate)))->limit(200)->field("*")->select();
	
     if (!empty($orders)) {
       $order_model = Model('order');
       foreach ($orders as $order_info) {
         $find = FALSE;
         $total = $order_info['order_amount'];

         if($total > 0) {
           $member_info = $model_member->getMemberInfo(array('member_id' => $order_info['buyer_id']));
           if (empty($member_info)) {
             $order_model->editOrder(array('is_settle' => 1), array('order_id' => $order_info['order_id']));
             continue;
           }

           $order_goods_list_all = $order_model->getOrderGoodsList(array('order_id'=> $order_info['order_id'],'buyer_id'=>$member_info['member_id']));
           $order_goods_list = array();
           $i = 0;
           foreach ($order_goods_list_all as $good_info) {
             $z_goods_type = intval($good_info['z_goods_type']);
              if($z_goods_type == 0){
               $order_goods_list[$i ++] = $good_info;
             }
           }
		   if(empty($order_goods_list)) {
			   continue;
		   }
		   try{
			   $model_member->beginTransaction();
			   $sno = $this->makeSn();
			   $orderNo = $order_info['order_sn'];
			   $this->selfReward($member_info, $total, $order_goods_list, $order_info, $list_setting);

			   if($total < 0.01) {
				 $order_model->editOrder(array('is_settle' => 1), array('order_id' => $order_info['order_id']));
				 continue;
			   }
			   $this->inviterReward($member_info, $total, $order_info['order_id'], $orderNo, $list_setting);
			   $this->mercharReward($member_info, $total, $order_info['order_id'], $orderNo, $list_setting);
			   //$this->companyReward($member_info, $total, $order_info['order_id'], $orderNo, $list_setting);
			   $order_model->editOrder(array('is_settle' => 1), array('order_id' => $order_info['order_id']));
			   $model_member->commit();
		   } catch (Exception $e) {
				$model_member->rollback();
			}
           
         }
       }

     }
     flock($fp, LOCK_UN);
     fclose($fp);
     return $rs;
   }

/**
* 处理生成优惠券及挂卖
*
*/
   function selfReward($member, $total_amount, $order_goods_list, $order_info, $list_setting) {
     $ym = 0;//优惠券
	 $model_member = Model('member');
	 $achievement = floatval($member['achievement']) + $total_amount;
	 $updatearr = array('achievement' => array('exp','achievement+'.$total_amount), 'vip' => 1);
	 $model_member->editMember(array('member_id' => $member['member_id']), $updatearr);
	 $pids = trim($member['inviter_ids'], ",");
         if(!empty($pids)) {
           $ids = explode(",", $pids);
           $model_member->editMember(array('member_id' => array("IN", $ids)), array('total_achievement' => array('exp','total_achievement+'.$total_amount)));
         }

     if (empty($order_goods_list)) {
       return;
     }
	 $salenos = array();
	 $model_goods = Model('goods');
     foreach ($order_goods_list as $value) {
	   $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $value['goods_id']), 'goods_commonid');
	   $goods_common_detail = $model_goods->getGoodsCommonInfo(array('goods_commonid' => $goods_info['goods_commonid']), 'sale_no');
	   if($goods_common_detail['sale_no'] > 0) {
		   if(empty($salenos[$goods_info['goods_commonid']])) {
			   $salenos[$goods_info['goods_commonid']] = array('sale_no' => intval($goods_common_detail['sale_no']), 'num' => 0, 'price' => floatval($value['goods_price']));
		   }
		   $salenos[$goods_info['goods_commonid']]['num'] += intval($value['goods_num']);
	   }
	   
       if(intval($value['z_goods_type']) == 0) {
         $ym += floatval($value['goods_point_rate2']) * intval($value['goods_num']);
       }

     }
	  Model('points')->savePointsLog(
         'order_return',
         array('pl_memberid'=>$member['member_id'],
          'pl_membername'=>$member['member_name'],
          'pl_points'=> 100,
          'pl_desc' => '购买商品赠送积分'.ncPriceFormat(100).",订单编号".$order_info['order_sn']
        ),true);
     if($ym > 0) {
       //优惠券
       Model('points2')->savePointsLog(
         'order',
         array('pl_memberid'=>$member['member_id'],
          'pl_membername'=>$member['member_name'],
          'pl_points'=> $ym,
          'pl_desc' => '购买商品赠送优惠券'.ncPriceFormat($ym).",订单编号".$order_info['order_sn']
        ),true);
     }
	 if(!empty($salenos)) {
		 $this->handleSale($member, $salenos, $order_info, $list_setting);
	 }
   }
   
   public function getPdAmount($condition = array()) {
    return $this->table('pd_log')->where($condition)->sum('lg_av_amount');
  }

/**
* 处理挂卖
*
*/
   function handleSale($member, $salenos, $order_info, $list_setting) {
	   $model_order = Model('order');
	   $model_member = Model('member');
	  $model_goods = Model('goods');
	    $rate = 0.2638;
      if(floatval($list_setting['sale_fee']) > 0) {
        $rate = floatval($list_setting['sale_fee']);
      }
	   foreach($salenos as $goods_common_id => $saleinfo) {
		   $hnum = 0;
		   $num = $saleinfo['num'];
		   $saleno = $saleinfo['sale_no'];
		   $maxno = 0;
		   while($hnum < $num) {
			   $order_info2 = $model_order->getOrderInfo(array('is_wholesale' => 1, 'sale_state' => 1, 'sale_no' => array('gt', 0),'sale_goods_common_id' => $goods_common_id ),  
				array(), 'order_id,sale_no,sale_num,buyer_id,order_sn', 'sale_no asc');
				$p = intval($order_info2['sale_no']) - $saleno;//实际编号和订单编号差多少，意味着已经结算过多少
				if(empty($order_info2)) {
					break;
				}
				$ordernum = intval($order_info2['sale_num']);
				if($p < 0) {// 已经结算过p个
					$ordernum += $p;
					if($ordernum <= 0) {
						$model_order->editOrder(array('sale_state' => 20), array('order_id' => $order_info2['order_id']));
						continue;
					}
				}
				$realnum = 0;
				if($num < $ordernum + $hnum) {//不能够一次清算完成
					$realnum = $num - $hnum;//只能清算$realnum 个
				} else {
					$realnum = $ordernum;
				}
				
				$member_info = $model_member->getMemberInfo(array('member_id' => $order_info2['buyer_id']));
				$order_total = $saleinfo['price'] * $realnum;
				$fee = $order_total * $rate;
				$amount = $order_total - $fee;
				$this->genReward4('交易成功', $member['member_name'], $member_info, $order_info2['order_sn'], $amount, "交易订单,交易商品数量".$realnum."，获得总额"
				  . ncPriceFormat($order_total) . ", 扣除管理费" . ncPriceFormat($fee).",交易订单编号".$order_info2['order_sn'].",代售编号".$order_info['order_sn']);
				if($ordernum  <= $realnum) {
					$model_order->editOrder(array('sale_state' => 20, 'sale_finish_date' => time(),'sale_fee' => $fee, 'sale_amount' => $amount),array('order_id' => $order_info2['order_id']));
				}
				
				//交割获得积分
			   /*Model('points')->savePointsLog(
				 'jiaoge',
				 array('pl_memberid'=>$member_info['member_id'],
				  'pl_membername'=>$member_info['member_name'],
				  'pl_points'=> $fee,
				  'pl_desc' => '交割商品赠送积分'.ncPriceFormat($fee).",订单编号".$order_info2['order_sn']
				),true);*/
				$hnum += $realnum;
				$maxno = $saleno + $realnum;
		   }
		   $model_goods->table('goods_common')->where(array('goods_commonid' => $goods_common_id))
					->update(array('sale_no' => $maxno));
	   }
   }

	/*
   * 推荐奖
   */
   function inviterReward($member, $total_amount, $order_id, $order_sn, $list_setting)
   {
     $reward_name = '推荐奖';
     $model_member = Model('member');
     $pids = $member['inviter_id'];
     if(empty($pids)) {
       return;
     }

     $amount = 0;
     $has_give = floatval($list_setting['invite_fee']);
     $member_info = $model_member->getMemberInfo(array('member_id' => $pids));
       
     $amount = $total_amount * $has_give;
     $amount = round($amount, 2);
     $this->genReward4($reward_name, $member['member_name'], $member_info, $order_sn, $amount);
   }

   /**三商
   */
   function mercharReward($member,$total_amount,$order_id, $order_sn,$list_setting) {
     $model_member = Model('member');
     $pids = trim($member['inviter_ids'], ",");
     if(empty($pids)) {
       return;
     }
     $rate_array = explode(",",$list_setting['manage_fee']);
	 $pingji_fee = floatval($list_setting['pingji_fee']);
     $ids = explode(",", $pids);
     $ids_length = count($ids);
     $amount = 0;
     $has_give = 0;
     $reward_name = '管理奖';
	 $last_level = 0;
     for ($i = 0; $i < $ids_length; $i++) {//
       $mid = $ids[$ids_length - $i - 1];
       $member_info = $model_member->getMemberInfo(array('member_id' => $mid));
	   $total_count = $this->table('member')->where(array('inviter_id' => $mid,'vip'=>1))->count();
	   $rate = 0;
	   $level = 0;
       if($member_info['total_achievement'] >= 300 * 10000 && $total_count >= 10) {
		   $rate = 0.1;
		   $level = 5;
	   } else if($member_info['total_achievement'] >= 150 * 10000 && $total_count >= 7) {
		   $rate = 0.08;
		   $level = 4;
	   } else if($member_info['total_achievement'] >= 70 * 10000 && $total_count >= 7) {
		   $rate = 0.06;
		   $level = 3;
	   } else if($member_info['total_achievement'] >= 30 * 10000 && $total_count >= 5) {
		   $rate = 0.04;
		   $level = 2;
	   } else if($member_info['total_achievement'] >= 10 * 10000 && $total_count >= 5) {
		   $rate = 0.02;
		   $level = 1;
	   }
	   $rate = floatval($rate_array[$level - 1]);
       if($rate - $has_give > 0) {
         $amount = $total_amount * ($rate - $has_give);
         $has_give = $rate;
         $reward_name = "管理奖".$level."级";
		 $last_level = $level;
         $this->genReward4($reward_name, $member['member_name'], $member_info, $order_sn, $amount);
       } else if($last_level > 0 && $last_level == $level){//平级
		 $amount = $total_amount * $pingji_fee;
		 $this->genReward4('平级奖', $member['member_name'], $member_info, $order_sn, $amount);
		 $last_level = 0;
	   }
     }
   }

/*
生成奖金
*/
   function genReward4($reward_name,$by_username,$member_info,$order_sn,$amount,$desc = NULL, $lg_invite_member_id = NULL,$realamount = NULL) {
     if($desc == NULL) {
       $log_msg = $reward_name.",订单会员[".$by_username."]，购买订单为".$order_sn."，获得金额为".$amount;
     } else {
       $log_msg = $desc;
     }
     $admin_act="sys_add_money_reward";

     $data = array();
     $data['member_id'] = $member_info['member_id'];
     $data['member_name'] = $member_info['member_name'];
     $data['amount'] = $amount;
     $data['tax'] = 0;
     $data['order_sn'] = $order_sn;
     $data['pdr_sn'] = $order_sn;
     $data['lg_desc'] = "奖金分配,".$log_msg;
     if($lg_invite_member_id != NULL) {
       $data['lg_invite_member_id'] = $lg_invite_member_id;
     }
     if(floatval($realamount) > 0) {
       $data['realamount'] = $realamount;
     }
     $this->changePd($admin_act,$data);
   }

/*
生成优惠券
*/
function genRewardPoints3($reward_name,$by_username,$member_info,$order_sn,$order_id,$amount,$desc = NULL, $orderamount = NULL) {
      if($desc == NULL) {
        $log_msg = $reward_name.",订单会员[".$by_username."]，购买订单为".$order_id."，";
        if($orderamount != NULL) {
            $log_msg .= "订单金额为".ncPriceFormat($orderamount).",";
        }
        $log_msg .= "获得股权积分为".$amount;
      } else {
        $log_msg = $desc;
      }
      $log_msg .= ",编号为".$order_sn;

      $data = array();
      $data['pl_memberid'] = $member_info['member_id'];
      $data['pl_membername'] = $member_info['member_name'];
      $data['pl_points'] = $amount;
      $data['pl_desc'] = $log_msg;
      $obj_points = Model('points2');
      $result = $obj_points->savePointsLog('reward',$data,true);
    }

}
