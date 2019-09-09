<?php
/**
 * 我的商城
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */



defined('In33hao') or exit('Access Invalid!');

class member_indexControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 我的商城
     */
    public function indexOp() {
        $member_info = array();
        $member_info['member_truename'] = $this->member_info['member_truename'];
        if(empty($member_info['member_truename'])) {
          $member_info['member_truename'] = $this->member_info["member_mobile"];
        }
        $member_info['user_name'] = $this->member_info['member_name'];
        $member_info['avatar'] = getMemberAvatarForID($this->member_info['member_id']);
        $model_setting = Model('setting');
        $list_setting = $model_setting->getRewardSetting();
        $member_info['aprice'] =  floatval($list_setting['Aquan']);
        //$member_gradeinfo = Model('member')->getOneMemberGrade(intval($this->member_info['member_exppoints']));
        $member_info['level_name'] = "";
        if($this->member_info['level'] == 1) {
          $member_info['level_name'] = "一级会员";
        } else if($this->member_info['level'] == 2) {
          $member_info['level_name'] = "二级会员";
        } else if($this->member_info['level'] == 3) {
          $member_info['level_name'] = "三级会员";
        }
        if(empty($member_info['level_name'])) {
          $member_info['level_name'] = "普通会员";
        }else {
          $member_info['level_name'] = trim($member_info['level_name'], '/');
        }
        $member_info['achievement'] = $this->member_info['achievement'];
        $member_info['total_achievement'] = $this->member_info['total_achievement'];
        $member_info['favorites_store'] = Model('favorites')->getStoreFavoritesCountByMemberId($this->member_info['member_id']);
        $member_info['favorites_goods'] = Model('favorites')->getGoodsFavoritesCountByMemberId($this->member_info['member_id']);
        // 交易提醒
        $model_order = Model('order');
        $member_info['order_nopay_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'NewCount');
        $member_info['order_noreceipt_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'SendCount');
        $member_info['order_notakes_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'TakesCount');
        $member_info['order_noeval_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'EvalCount');

        // 售前退款
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['refund_state'] = array('lt', 3);
        $member_info['return'] = Model('refund_return')->getRefundReturnCount($condition);

        output_data(array('member_info' => $member_info));
    }

    /**
     * 我的资产
     */
    public function my_assetOp() {
        $param = $_GET;
        $fields_arr = array('point','predepoit','available_rc_balance','redpacket','voucher','member_points2',
      'points_original','points_freeze','points_send','release_apoints','predepoit_fenghong','coupon_point','pv');
        $fields_str = trim($param['fields']);
        if ($fields_str) {
            $fields_arr = explode(',',$fields_str);
        }
        $member_info = array();
        if (in_array('point',$fields_arr)) {
            $member_info['point'] = $this->member_info['member_points'];
        }
        if (in_array('predepoit',$fields_arr)) {
            $member_info['predepoit'] = $this->member_info['available_predeposit'];
        }
        if (in_array('predepoit_fenghong',$fields_arr)) {
          $v = Model('predeposit')->getPdLogAmount(array('lg_type' => 'sys_add_money_reward', 'lg_add_time' => array('egt', strtotime(date('Y-m-d'))), 'lg_member_id' => $this->member_info['member_id']));
            $member_info['predepoit_today'] = floatval($v);
            $member_info['predepoit_all'] = $this->member_info['fenhong'];
        }
        if (in_array('available_rc_balance',$fields_arr)) {
            $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        }
        if (in_array('redpacket',$fields_arr)) {
            $member_info['redpacket'] = Model('redpacket')->getCurrentAvailableRedpacketCount($this->member_info['member_id']);
        }
        if (in_array('voucher',$fields_arr)) {
            $member_info['voucher'] = Model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        }
        if (in_array('member_points2',$fields_arr)) {
            $member_info['member_points2'] = $this->member_info['member_points2'];
        }
        if (in_array('points_original',$fields_arr)) {
          $member_info['points_original'] = $this->member_info['points_original'];
        }
        if (in_array('points_freeze',$fields_arr)) {
          $member_info['points_freeze'] = $this->member_info['points_freeze'];
        }
        if (in_array('points_send',$fields_arr)) {
          $member_info['points_send'] = $this->member_info['points_send'];
        }
        if (in_array('points_recv',$fields_arr)) {
          $member_info['points_recv'] = $this->member_info['points_recv'];
        }
        if (in_array('points_cashout',$fields_arr)) {
          $member_info['points_cashout'] = $this->member_info['points_cashout'];
        }
        if (in_array('coupon_point',$fields_arr)) {
          $member_info['coupon_point'] = $this->member_info['coupon_point'];
        }
        if (in_array('pv',$fields_arr)) {
          $member_info['pv'] = $this->member_info['guquan'];
        }
        output_data($member_info);
    }

    public function predepoit_transferOp()
    {
      $money = $_POST['money'];
      $target = $_POST['target'];
      $paypwd = $_POST['paypsd'];
      $model_setting = Model('setting');
      $list_setting = $model_setting->getRewardSetting();
      // $is_can_transfer = $list_setting['is_can_transfer'];
      // if($is_can_transfer == "0") {
      //   output_error('当前禁止转帐');
      // }
      // if($this->member_info['member_transfer_state'] == 0) {
      //   output_error('您当前被禁止转账');
      // }
      if (floatval($money) <= 0 || !is_numeric($money)) {
        output_error('请输入正确的金额');
      }

      if (empty($target)) {
        output_error('请输入收款人用户名');
      }

      if (empty($paypwd)) {
        output_error('请输入支付密码,如果未显示支付密码,请刷新页面尝试');
      }
      if(md5($paypwd) != $this->member_info['member_paypwd']) {
        output_error('支付密码验证失败');
      }

      $model_setting = Model('setting');
      $avcash = $this->member_info['available_predeposit'];
      $list_setting = $model_setting->getRewardSetting();
      $recmember = Model('member')->getMemberInfo(array('member_name' => $target));

      if (empty($recmember)) {
        output_error('收款人不存在');
      }
      if (floatval($this->member_info['available_predeposit']) < floatval($money)) {
        output_error('余额不足');
      }

      $rate = 0;
      $tFee = sprintf("%.2f", floatval($money) * floatval($rate));
      $trueMoney = sprintf("%.2f", floatval($money) - floatval($tFee));;

      $from_usern_name = $this->member_info['member_name'];
      $to_usern_name = $recmember['member_name'];

      $data = array();
      $data['member_id'] = $this->member_info['member_id'];
      $data['member_name'] = $this->member_info['member_name'];
      $data['amount'] = $money;
      $data['receive_amount'] = $trueMoney;
      $data['tax'] = 0;
      $data['lg_desc'] = "转账{$money}给用户{$to_usern_name},实到{$trueMoney},手续费{$tFee},费率{$rate}";
      Model('predeposit')->changePd("predepoit_transfer_from", $data);

      $rc_rate = 0;
      $to_money = $trueMoney * floatval($rc_rate);
      $from_money = $trueMoney - $to_money;

      $data = array();
      $data['member_id'] = $recmember['member_id'];
      $data['member_name'] = $to_usern_name;
      $data['amount'] = $money;
      $data['receive_amount'] = $from_money;
      $data['receive_amount_rc'] = $to_money;
      $data['tax'] = 0;
      $data['lg_desc'] = "{$from_usern_name}转账{$trueMoney}, 余额到账{$from_money}";

      Model('predeposit')->changePd("predepoit_transfer_to", $data);

      output_data('1');
    }
    public function get_last_cash_infoOp() {
      $model_pd = Model('predeposit');
      $condition = array('pdc_member_id' => $this->member_info['member_id']);
      //	$info = $model_pd->getPdCashInfo($condition);
      //var_dump($this->member_info);
      $info = array('pdc_bank_name' => $this->member_info['bankname'], 'pdc_bank_no' => $this->member_info['bankno'],
      'mobilenum' => $this->member_info['member_mobile'], 'pdc_bank_user' => $this->member_info['real_name']);
      output_data(array('pdc_bank_name' => $info['pdc_bank_name'], 'pdc_bank_no' => $info['pdc_bank_no'],
      'mobilenum'=> $info['mobilenum'], 'pdc_bank_user' => $info['pdc_bank_user']));
    }

    public function realinfoOp() {
      if($this->member_info['is_realname'] == 1) {
        output_error('审核中...');
      }
      else if($this->member_info['is_realname'] == 2) {
        output_error('已实名审核成功');
      } else if($this->member_info['is_realname'] == 3) {
        output_error('审核失败 ');
      }else {
        output_data('ok');
      }
    }

    public function voucher_transferOp() {
      $money = floatval($_POST['money']);
      $type = $_POST['type'];
      $target = $_POST['target'];
      $paypwd = $_POST['paypsd'];
      if (floatval($money) <= 0 || !is_numeric($money)) {
        output_error('请输入正确的数量');
      }

      if (empty($target)) {
        output_error('请输入接收人的手机号');
      }

      if (empty($paypwd)) {
        output_error('请输入支付密码,如果未显示支付密码,请刷新页面尝试');
      }
      if(md5($paypwd) != $this->member_info['member_paypwd']) {
        output_error('支付密码验证失败');
      }

      $recmember = Model('member')->getMemberInfo(array('member_mobile' => $target));
      $rec_members = $this->member_info['inviter_ids'];
      		if(empty($rec_members)) $rec_members = ",";
      		$rec_members .= $this->member_info['member_id'].",";
      		if(empty($recmember['inviter_ids'])) $recmember['inviter_ids'] = ",";
      		$recmember['inviter_ids'] .= $recmember['member_id'].",";

      if (empty($recmember)) {
        output_error('收款人不存在');
      }
      $avcash = 0;
      if($type == 'ys' || $type == 'pv') { // 只能上下转
        if($recmember['member_id'] != $this->member_info['inviter_id']) {
        			if( $this->member_info['member_id'] != $recmember['inviter_id']) {
        				output_error('转账目标会员不为上下级关系');
        			}
        }
      }

      if($type == 'ys') {
        $avcash = $this->member_info['points_original'];
      } else if($type == 'pv') {
        $avcash = $this->member_info['guquan'];
      } else if($type == 'sf') {
        $avcash = $this->member_info['member_points'];
      } else if($type == 'dh') {
        $avcash = $this->member_info['coupon_point'];
      } else {
        $type = 'dh';
        $avcash = $this->member_info['coupon_point'];
      }
      if ($avcash < $money) {
        output_error('数量不足，您当前数量为'.$avcash);
      }
      $guquan = 0;
      if($type == 'ys') {
        $model_setting = Model('setting');
        $list_setting = $model_setting->getRewardSetting();
        $guquan = floatval($list_setting['PV']) * abs($money);
        if($guquan > $this->member_info['guquan']) {
          output_error('PV数量不足，您当前数量为'.$this->member_info['guquan']);
        }
      }

      $rate = 0;
      $tFee = sprintf("%.2f", floatval($money) * floatval($rate));
      $trueMoney = sprintf("%.2f", floatval($money) - floatval($tFee));;

      $from_usern_name = $this->member_info['member_name'];
      $to_usern_name = $recmember['member_name'];
      $points_model = Model('points');
      $typeNames = array('ys' => '原始仓', 'dh' => '兑换仓', 'pv' => 'PV值', 'sf' => '释放仓');
      $typeName = $typeNames[$type];
      $pd_model = Model('predeposit');
      $sn = $pd_model->makeSn();
      try{
        $points_model->beginTransaction();
        $insert_arr = array();
        $insert_arr['pl_memberid'] = $this->member_info['member_id'];
        $insert_arr['pl_membername'] = $this->member_info['member_name'];
        $insert_arr['pl_points'] = -$trueMoney;
        //$insert_arr['pl_desc'] = "转赠{$typeName}{$money}给用户{$to_usern_name},实到{$trueMoney},手续费{$tFee},费率{$rate},单号{$sn}";
        $insert_arr['pl_desc'] = "转赠{$typeName}{$money}给用户{$to_usern_name},单号{$sn}";

        $insert_arr2 = array();
        $insert_arr2['pl_memberid'] = $recmember['member_id'];
        $insert_arr2['pl_membername'] = $recmember['member_name'];
        $insert_arr2['pl_points'] = $trueMoney;
        //$insert_arr2['pl_desc'] = "收到用户{$from_usern_name}转赠{$typeName}{$money},实到{$trueMoney},手续费{$tFee},费率{$rate},单号{$sn}";
        $insert_arr2['pl_desc'] = "收到用户{$from_usern_name}转赠{$typeName}{$money},单号{$sn}";
        if($type == 'ys') {
          $result = $points_model->savePointsOtherLog('transfer_to',$insert_arr,true);
          $result = $points_model->savePointsOtherLog('transfer_recv',$insert_arr2,true);

          $pv_model = Model('pvlog');
          $insert_arrpv = array();

          $insert_arrpv['pl_memberid'] = $insert_arr['pl_memberid'];
          $insert_arrpv['pl_membername'] = $insert_arr['pl_membername'];
          $insert_arrpv['pl_points'] = -$guquan;
          $insert_arrpv['pl_desc'] = "转赠{$typeName}{$money}给用户{$to_usern_name}".',扣除PV值'.$guquan.',单号'.$sn;
          $result = $pv_model->savePointsLog('transfer_to',$insert_arrpv);


          $insert_arrpv['pl_memberid'] = $insert_arr2['pl_memberid'];
          $insert_arrpv['pl_membername'] = $insert_arr2['pl_membername'];
          $insert_arrpv['pl_points'] = $guquan;
          $insert_arrpv['pl_desc'] = "收到用户{$from_usern_name}转赠{$typeName}{$money}".',获得PV值'.$guquan.',单号'.$sn;
          $result = $pv_model->savePointsLog('transfer_recv',$insert_arrpv);

        } else if($type == 'pv') {
          $pv_model = Model('pvlog');
          // $result = $pv_model->savePointsLog('transfer_to',$insert_arr);
          // $result = $pv_model->savePointsLog('transfer_recv',$insert_arr2);
        } else if($type == 'sf') {
          $result = $points_model->savePointsLog('transfer_to',$insert_arr);
          $result = $points_model->savePointsLog('transfer_recv',$insert_arr2);
        } else if($type == 'dh') {
          $result = $points_model->savePointsCouponLog('transfer_to',$insert_arr);
          $result = $points_model->savePointsCouponLog('transfer_recv',$insert_arr2);
        }
        $points_model->commit();
      } catch (Exception $e) {
          $points_model->rollback();
          output_error($e);
      }
      $data = array();
      $data['member_id'] = $this->member_info['member_id'];
      $data['member_name'] = $this->member_info['member_name'];
      $data['amount'] = $money;
      $data['receive_amount'] = $trueMoney;
      $data['tax'] = 0;
      $data['lg_desc'] = "转账{$money}给用户{$to_usern_name},实到{$trueMoney},手续费{$tFee},费率{$rate}";
      Model('predeposit')->changePd("predepoit_transfer_from", $data);

      $rc_rate = 0;
      $to_money = $trueMoney * floatval($rc_rate);
      $from_money = $trueMoney - $to_money;

      $data = array();
      $data['member_id'] = $recmember['member_id'];
      $data['member_name'] = $to_usern_name;
      $data['amount'] = $money;
      $data['receive_amount'] = $from_money;
      $data['receive_amount_rc'] = $to_money;
      $data['tax'] = 0;
      $data['lg_desc'] = "{$from_usern_name}转账{$trueMoney}, 余额到账{$from_money}";

      Model('predeposit')->changePd("predepoit_transfer_to", $data);

      output_data('1');
    }


	 public function submit_formOp() {
        $model_input_form = Model('input_form');
        if (empty($_POST['input_content']) 
                || empty($_POST['phone']) 
				) {
            output_error('缺少必要信息 ');
            return;
        }
        $param = array();
        $param['member_name'] = $this->member_info['member_name'];
        $param['member_id'] = $this->member_info['member_id'];
         $param['input_content'] = $_POST['input_content'];
		 $param['phone'] = $_POST['phone'];
        $param['img1'] = $this->upload_image('storeimg1');
        $param['img2'] = $this->upload_image('storeimg2');
        $param['img3'] = $this->upload_image('storeimg3');
		$param['img4'] = $this->upload_image('storeimg4');
        $param['create_date'] = time();
		
        $model_input_form->addFormInfo($param);
       output_data('ok');
    }

	public function submit_form_listOp() {
        $model_input_form = Model('input_form');
        $data = array();
        $list = $model_input_form->getFormList(array('member_id' => $this->member_info['member_id']), '*',  $this->page);
		if ($list) {
            foreach($list as $k=>$v){
                $v['create_date'] = @date('Y-m-d H:i:s',$v['create_date']);
                $list[$k] = $v;
            }
        }
        $page_count = $model_input_form->gettotalpage();
        output_data(array('list' => $list), mobile_page($page_count));
    }

    private function upload_image($file) {
		if(empty($_FILES[$file])) {
			return '';
		}
        $pic_name = '';
        $upload = new UploadFile();
        $uploaddir = ATTACH_PATH.DS.'store_joinin'.DS;
        $upload->set('default_dir',$uploaddir);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        if (!empty($_FILES[$file]['name'])){
            $result = $upload->upfile($file);
            if ($result){
                $pic_name = $upload->file_name;
                $upload->file_name = '';
            }
        }
        return $pic_name;
    }
   function copyfiles($file1, $file2) {
        $contentx = @file_get_contents($file1);
        $openedfile = fopen($file2, "w");
        fwrite($openedfile, $contentx);
        fclose($openedfile);
        if ($contentx === FALSE) {
            $status = false;
        } else
            $status = true;
        return $status;
    }

}
