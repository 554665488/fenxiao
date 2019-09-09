<?php
/**
 * 我的A金券
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */



defined('In33hao') or exit('Access Invalid!');

class member_voucherControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
        // 判断系统是否开启代金券功能
        if (intval(C('voucher_allow')) !== 1) {
            output_error('系统未开启代金券功能');
        }
    }


    /**
     * 我的A金券列表
     */
    public function voucher_listOp() {
        $param = $_GET;
        $model_voucher = Model('voucher2');
        $voucher_list = $model_voucher->getMemberVoucherList($this->member_info['member_id'], $param['voucher_state'], $this->page, 'voucher_state asc,voucher_id desc');
        $page_count = $model_voucher->gettotalpage();
        for($i = 0; $i < count($voucher_list); $i++) {
          $voucher_list[$i]['voucher_price'] = sprintf('%.6f', $voucher_list[$i]['voucher_price']);
        }
        output_data(array('voucher_list' => $voucher_list), mobile_page($page_count));
    }

    /**
     * 我的A金券可以购买的列表
     */
    public function voucher_CanlistOp() {
        $param = $_GET;
        $model_voucher = Model('voucher2');
        $voucher_list = $model_voucher->getMemberVoucherCanList($this->member_info['member_id'], $param['voucher_state'], $this->page, 'voucher_state asc,voucher_id desc');
        for($i = 0; $i < count($voucher_list); $i++) {
          $voucher_list[$i]['voucher_price'] = sprintf('%.6f', $voucher_list[$i]['voucher_price']);
        }
        $page_count = $model_voucher->gettotalpage();
        output_data(array('voucher_list' => $voucher_list), mobile_page($page_count));
    }


    /**
     * 增值券卖出
     */
    public function voucher_saleOp()
    {
        $param = $_POST;

        $amount = trim($param["amount"]);
        $priceamount = trim($param["price"]);
        $model_voucher = Model('voucher2');
        $where = array();
        $where['voucher_num'] =$amount;
        $where['voucher_price'] =$priceamount;
        try {
            $model_voucher->beginTransaction();
             //更新个人账户A金券数量
              //新增日志记录
              //生成A金券订单
            $data = $model_voucher->getVoucherOrder($where, $this->member_info['member_id'], $this->member_info['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            output_data('1');
        } catch (Exception $e) {
            $model_voucher->rollback();
            output_error($e->getMessage());
        }
    }

    /**
     * 增值券卖出撤回
     */
    public function voucher_saleCancleOp()
    {
        $param = $_POST;

        $voucher_id = trim($param["voucher_id"]);
        $voucher_num = trim($param["voucher_num"]);
        $model_voucher = Model('voucher2');
        $where = array();
        $where['voucher_id'] =$voucher_id;
        $where['voucher_num'] =$voucher_num;
        try {
            $model_voucher->beginTransaction();
             //更新个人账户A金券数量
              //新增日志记录
              //生成A金券订单
            $data = $model_voucher->getVoucherOrderCancle($where, $this->member_info['member_id'], $this->member_info['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            output_data('1');
        } catch (Exception $e) {
            $model_voucher->rollback();
            output_error($e->getMessage());
        }
    }
    /**
     * 增值券购买
     */
    public function voucher_buyorderOp()
    {
        $param = $_POST;

        $voucher_id = trim($param["voucher_id"]);
        $voucher_num = trim($param["voucher_num"]);
        $model_voucher = Model('voucher2');
        $where = array();
        $where['voucher_id'] =$voucher_id;
        $where['voucher_num'] =$voucher_num;
        try {
            $model_voucher->beginTransaction();
             //更新个人账户A金券数量
              //新增日志记录getVoucherInfo
              //生成A金券订单
            $data = $model_voucher->getVoucherOrderBuy($where, $this->member_info['member_id'], $this->member_info['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            output_data('1');
        } catch (Exception $e) {
            $model_voucher->rollback();
            output_error($e->getMessage());
        }
    }


    /**
	 * 提币
	 */
	public function voucher_tibiOp(){

        $a_address = $_POST['a_address'];
        $a_amount = abs(floatval($_POST['a_amount']));
          //查询会员信息 
        $member_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);
          
        if($member_info['member_points']<$a_amount){
            return output_error('数量超出释放仓总量!'); 
        }
		if ($a_amount <= 0) {
			output_error('数量不正确!');
		}
		else{
            $model_voucher = Model('voucher2');
			$data = array();
			$data['a_owner_id'] = $this->member_info['member_id'];
			$data['a_owner_name'] = $this->member_info['member_name'];
			$data['a_address'] = $a_address;
			$data['a_num'] = $a_amount;
			$data['a_state'] = 0;
			$data['a_addtime'] = TIMESTAMP;
            try {
                $model_voucher->beginTransaction();
                $model_voucher->addtibi($data);
                $model_voucher->commit();
                output_data('1');
            } catch (Exception $e) {
                $model_voucher->rollback();
                output_error($e->getMessage());
            }


		}

    }
    /**
     * 免费领取代金券
     */
    public function voucher_freeexOp() {
        $param = $_POST;

        $t_id = intval($param['tid']);
        if($t_id <= 0){
            output_error('代金券信息错误');
        }
        $model_voucher = Model('voucher');
        //验证是否可领取代金券
        $data = $model_voucher->getCanChangeTemplateInfo($t_id, $this->member_info['member_id'], $this->member_info['store_id']);
        if ($data['state'] == false){
            output_error($data['msg']);
        }
        try {
            $model_voucher->beginTransaction();
            //添加代金券信息
            $data = $model_voucher->exchangeVoucher($data['info'], $this->member_info['member_id'], $this->member_info['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            output_data('1');
        } catch (Exception $e) {
            $model_voucher->rollback();
            output_error($e->getMessage());
        }
    }

    /**
     * 其他仓库转兑换仓
     */
    public function voucher_PointChangeOp()
    {
        $param = $_POST;

        $selectValue = trim($param["selectValue"]);
        $txtAmout = trim($param["txtAmout"]);
        $where = array();
        $points_arr = array();
        $points_arr['pl_memberid'] =$this->member_info['member_id'] ;
        $points_arr['pl_membername'] =$this->member_info['member_name'];
        $points_arr['pl_points'] =$txtAmout;
        $points_arr['pl_addtime'] = time();

        $points_arr['pl_stage'] ='coupon_point' ;

        $points_arr1 = array();
        $points_arr1['pl_memberid'] =$this->member_info['member_id'] ;
        $points_arr1['pl_membername'] =$this->member_info['member_name'];
        $points_arr1['pl_points'] =$txtAmout;
        $points_arr1['pl_addtime'] = time();
        $points_arr1['pl_stage'] =$selectValue ;
        $model_member = Model('member');
        if($selectValue=="points_original"){
            $where['points_original'] =array('exp','points_original-'.$txtAmout);
            $points_arr['pl_desc'] ='原始仓数量转换到兑换仓,兑换仓增加'.$txtAmout;
            $points_arr1['pl_desc'] ='原始仓数量转换到兑换仓,原始仓减少'.$txtAmout;
        }
        if($selectValue=="points_freeze"){
            $where['points_freeze'] =array('exp','points_freeze-'.$txtAmout);
            $points_arr['pl_desc'] ='冻结仓数量转换到兑换仓,兑换仓增加'.$txtAmout;
            $points_arr1['pl_desc'] ='冻结仓数量转换到兑换仓,冻结仓减少'.$txtAmout;
        }
        if($selectValue=="member_points"){
            $where['member_points'] =array('exp','member_points-'.$txtAmout);
            $points_arr['pl_desc'] ='释放仓数量转换到兑换仓,兑换仓增加'.$txtAmout;
            $points_arr1['pl_desc'] ='释放仓数量转换到兑换仓,释放仓减少'.$txtAmout;
        }
        try {

            $where['coupon_point'] = array('exp','coupon_point+'.$txtAmout);
            $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),$where);

            if (!$update) {
                output_error('操作失败');
             }
             else{
                  //新增日志
                  Model('points')->addPointsLog($points_arr);
                  Model('points')->addPointsLog($points_arr1);
             }
            output_data(1);
        } catch (Exception $e) {
            output_error($e->getMessage());
        }
    }
}
