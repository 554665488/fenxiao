<?php
/**
 * 积分管理
 *
 *
 */



defined('In33hao') or exit('Access Invalid!');
class pointsControl extends SystemControl{
    const EXPORT_SIZE = 5000;
    public function __construct(){
        parent::__construct();
        Language::read('points');
        //判断系统是否开启积分功能
        if (C('points_isuse') != 1){
            showMessage(Language::get('admin_points_unavailable'),'index.php?act=setting','','error');
        }
    }

    public function indexOp() {
        $this->pointslogOp();
    }

    /**
     * 积分添加
     */
    public function addpointsOp(){
        if (chksubmit()){

            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["member_id"], "require"=>"true", "message"=>Language::get('admin_points_member_error_again')),
                array("input"=>$_POST["pointsnum"], "require"=>"true",'validator'=>'Compare','operator'=>' >= ','to'=>0,"message"=>Language::get('admin_points_points_min_error'))
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error,'','','error');
            }
            //查询会员信息
            $obj_member = Model('member');
            $member_id = intval($_POST['member_id']);
            $member_info = $obj_member->getMemberInfo(array('member_id'=>$member_id));

            if (!is_array($member_info) || count($member_info)<=0){
                showMessage(Language::get('admin_points_userrecord_error'),'index.php?act=points&op=addpoints','','error');
            }

            $pointsnum = intval($_POST['pointsnum']);
            /**/

            $obj_points = Model('points');
            $insert_arr['pl_memberid'] = $member_info['member_id'];
            $insert_arr['pl_membername'] = $member_info['member_name'];
            $admininfo = $this->getAdminInfo();
            $insert_arr['pl_adminid'] = $admininfo['id'];
            $insert_arr['pl_adminname'] = $admininfo['name'];
            if ($_POST['operatetype'] == 2){
                $insert_arr['pl_points'] = -$_POST['pointsnum'];
            }else {
                $insert_arr['pl_points'] = $_POST['pointsnum'];
            }
            if ($_POST['pointsdesc']){
                $insert_arr['pl_desc'] = trim($_POST['pointsdesc']);
            } else {
                $insert_arr['pl_desc'] = '管理员手动操作积分';
            }
            $result = FALSE;
            if($_POST['cang_type'] == 'dj') {
              if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['points_freeze'])){
                  showMessage(Language::get('admin_points_points_short_error').$member_info['points_freeze'],'index.php?act=points&op=addpoints','','error');
              }
              $result = $obj_points->savePointsOtherLog('system',$insert_arr,false);
            } else if($_POST['cang_type'] == 'dh') {
              if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['coupon_point'])){
                  showMessage(Language::get('admin_points_points_short_error').$member_info['coupon_point'],'index.php?act=points&op=addpoints','','error');
              }
              $result = $obj_points->savePointsCouponLog('system',$insert_arr);
            } else if($_POST['cang_type'] == 'pv') {
              if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['guquan'])){
                  showMessage(Language::get('admin_points_points_short_error').$member_info['guquan'],'index.php?act=points&op=addpoints','','error');
              }
              if ($_POST['pointsdesc']){
                  $insert_arr['pl_desc'] = trim($_POST['pointsdesc']);
              } else {
                  $insert_arr['pl_desc'] = '管理员手动操作PV值';
              }
              $pv_model = Model('pvlog');
              $result = $pv_model->savePointsLog('system',$insert_arr);
            } else {
              if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['member_points'])){
                  showMessage(Language::get('admin_points_points_short_error').$member_info['member_points'],'index.php?act=points&op=addpoints','','error');
              }
              $result = $obj_points->savePointsLog('system',$insert_arr,true);
            }

            if ($result){
                $this->log(L('admin_points_mod_tip').$member_info['member_name'].'['.(($_POST['operatetype'] == 2)?'':'+').strval($insert_arr['pl_points']).']',null);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=points&op=addpoints');
            }else {
                showMessage(Language::get('nc_common_save_fail'),'index.php?act=points&op=addpoints','','error');
            }
        }else {
			Tpl::setDirquna('shop');
            Tpl::showpage('points.add');
        }
    }

    /**
     * 积分添加
     */
    public function addpointsogOp(){
        if (chksubmit()){

            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["member_id"], "require"=>"true", "message"=>Language::get('admin_points_member_error_again')),
                array("input"=>$_POST["pointsnum"], "require"=>"true",'validator'=>'Compare','operator'=>' >= ','to'=>0,"message"=>Language::get('admin_points_points_min_error'))
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error,'','','error');
            }
            //查询会员信息
            $obj_member = Model('member');
            $member_id = intval($_POST['member_id']);
            $member_info = $obj_member->getMemberInfo(array('member_id'=>$member_id));

            if (!is_array($member_info) || count($member_info)<=0){
                showMessage(Language::get('admin_points_userrecord_error'),'index.php?act=points&op=addpointsog','','error');
            }

            $pointsnum = intval($_POST['pointsnum']);
            if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['points_original'])){
                showMessage(Language::get('admin_points_points_short_error').$member_info['points_original'],'index.php?act=points&op=addpointsog','','error');
            }

            $obj_points = Model('points');
            $insert_arr['pl_memberid'] = $member_info['member_id'];
            $insert_arr['pl_membername'] = $member_info['member_name'];
            $admininfo = $this->getAdminInfo();
            $insert_arr['pl_adminid'] = $admininfo['id'];
            $insert_arr['pl_adminname'] = $admininfo['name'];
            if ($_POST['operatetype'] == 2){
                $insert_arr['pl_points'] = -$_POST['pointsnum'];
            }else {
                $insert_arr['pl_points'] = $_POST['pointsnum'];
            }
            if ($_POST['pointsdesc']){
                $insert_arr['pl_desc'] = trim($_POST['pointsdesc']);
            } else {
                $insert_arr['pl_desc'] = '管理员手动操作原始仓积分';
            }
            if($insert_arr['pl_points'] > 0) {
              $model_setting = Model('setting');
              $list_setting = $model_setting->getRewardSetting();
              $guquan = floatval($list_setting['PV']) * floatval($insert_arr['pl_points']);
              $pv_model = Model('pvlog');
              $insert_arrpv = array();
              $insert_arrpv['pl_memberid'] = $member_info['member_id'];
              $insert_arrpv['pl_membername'] = $member_info['member_name'];
              $insert_arrpv['pl_adminid'] = $admininfo['id'];
              $insert_arrpv['pl_adminname'] = $admininfo['name'];
              $insert_arrpv['pl_points'] = $guquan;
              $insert_arrpv['pl_desc'] = '管理员操作原始仓'. $insert_arr['pl_points'].',获得PV值'.$guquan;
              $result = $pv_model->savePointsLog('system_og',$insert_arrpv);
            }


            $result = $obj_points->savePointsOtherLog('system',$insert_arr,true);
            if ($result){
                $this->log(L('admin_points_mod_tip').$member_info['member_name'].'['.(($_POST['operatetype'] == 2)?'':'+').strval($insert_arr['pl_points']).']',null);
                showMessage(Language::get('nc_common_save_succ'),'index.php?act=points&op=addpointsog');
            }else {
                showMessage(Language::get('nc_common_save_fail'),'index.php?act=points&op=addpointsog','','error');
            }
        }else {
			Tpl::setDirquna('shop');
            Tpl::showpage('pointsog.add');
        }
    }

    public function checkmemberOp(){
        $name = trim($_GET['name']);
        if (!$name){
            echo ''; die;
        }
        /**
         * 转码
         */
        if(strtoupper(CHARSET) == 'GBK'){
            $name = Language::getGBK($name);
        }
        $obj_member = Model('member');
        $member_info = $obj_member->getMemberInfo(array('member_name'=>$name));
        if (is_array($member_info) && count($member_info)>0){
            if(strtoupper(CHARSET) == 'GBK'){
                $member_info['member_name'] = Language::getUTF8($member_info['member_name']);
            }
            $points = $member_info['member_points'];
            if(!empty($_GET['type'])) {
              if($_GET['type'] == 'og') {
                $points = $member_info['points_original'];
              }
            } else {
              $points = '积分' . $member_info['member_points'] ;
            }
            echo json_encode(array('id'=>$member_info['member_id'],'name'=>$member_info['member_name'],'points'=> $points));
        }else {
            echo ''; die;
        }
    }
    /**
     * 积分日志列表
     */
    public function pointslogOp(){
		Tpl::setDirquna('shop');
        Tpl::showpage('points.log');
    }

    /**
     * 规则设置
     */
    public function settingOp() {
        Language::read('setting');
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['points_reg'] = intval($_POST['points_reg'])?$_POST['points_reg']:0;
            $update_array['points_login'] = intval($_POST['points_login'])?$_POST['points_login']:0;
            $update_array['points_comments'] = intval($_POST['points_comments'])?$_POST['points_comments']:0;
            $update_array['points_orderrate'] = intval($_POST['points_orderrate'])?$_POST['points_orderrate']:0;
            $update_array['points_ordermax'] = intval($_POST['points_ordermax'])?$_POST['points_ordermax']:0;
            $update_array['points_money_isuse'] = $_POST['points_money_isuse'];
            $update_array['points_money_parity'] = floatval($_POST['points_money_parity'])?$_POST['points_money_parity']:0.01;
			$result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log('积分设置',1);
                showMessage(L('nc_common_save_succ'));
            }else {
                showMessage(L('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        Tpl::output('list_setting',$list_setting);
		Tpl::setDirquna('shop');
        Tpl::showpage('points.setting');
    }

    /**
     * 输出XML数据
     */
    public function get_xmlOp() {
        $where = array();
        if ($_POST['query'] != '') {
            switch($_POST['qtype']){
                case 'pl_memberid':
                    $where['pl_memberid'] = $_POST['query'];
                    break;
                case 'pl_membername_like':
                    $where['pl_membername'] = array('like',"%{$_POST['query']}%");
                    break;
                case 'pl_adminname_like':
                    $where['pl_adminname'] = array('like',"%{$_POST['query']}%");
                    break;
            }
        }
        $order = '';
        $param = array('pl_id','pl_memberid','pl_membername','pl_points','pl_addtime');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = !empty($_POST['rp']) ? intval($_POST['rp']) : 15;
        $points_model = Model('points');
        $list_log = $points_model->getPointsLogList($where, '*', 0, $page, $order);
        if (empty($list_log)) $list_log = array();
        $data = array();
        $data['now_page'] = $points_model->shownowpage();
        $data['total_num'] = $points_model->gettotalnum();
        foreach ($list_log as $value) {
            $param = array();
            $param['operation'] = "--";
            $param['pl_id'] = $value['pl_id'];
            $param['pl_memberid'] = $value['pl_memberid'];
            $param['pl_membername'] = $value['pl_membername'];
            $param['pl_points'] = $value['pl_points'];
            $param['pl_stage'] = $value['stagetext'];
            $param['pl_addtime'] = $value['addtimetext'];
            $param['pl_desc'] = $value['pl_desc'];
            $param['pl_adminname'] = $value['pl_adminname'];
            $data['list'][$value['pl_id']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    /**
     * 积分日志列表
     */
    public function pvlogOp(){
		Tpl::setDirquna('shop');
        Tpl::showpage('pv.log');
    }

    /**
     * 输出XML数据
     */
    public function get_pvxmlOp() {
        $where = array();
        if ($_POST['query'] != '') {
            switch($_POST['qtype']){
                case 'pl_memberid':
                    $where['pl_memberid'] = $_POST['query'];
                    break;
                case 'pl_membername_like':
                    $where['pl_membername'] = array('like',"%{$_POST['query']}%");
                    break;
                case 'pl_adminname_like':
                    $where['pl_adminname'] = array('like',"%{$_POST['query']}%");
                    break;
            }
        }
        $order = '';
        $param = array('pl_id','pl_memberid','pl_membername','pl_points','pl_addtime');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = !empty($_POST['rp']) ? intval($_POST['rp']) : 15;
        $points_model = Model('pvlog');
        $list_log = $points_model->getPointsLogList($where, '*', 0, $page, $order);
        if (empty($list_log)) $list_log = array();
        $data = array();
        $data['now_page'] = $points_model->shownowpage();
        $data['total_num'] = $points_model->gettotalnum();
        foreach ($list_log as $value) {
            $param = array();
            $param['operation'] = "--";
            $param['pl_id'] = $value['pl_id'];
            $param['pl_memberid'] = $value['pl_memberid'];
            $param['pl_membername'] = $value['pl_membername'];
            $param['pl_points'] = $value['pl_points'];
            $param['pl_stage'] = $value['stagetext'];
            $param['pl_addtime'] = $value['addtimetext'];
            $param['pl_desc'] = $value['pl_desc'];
            $param['pl_adminname'] = $value['pl_adminname'];
            $data['list'][$value['pl_id']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

     /**
     * 提币日志列表
     */
    public function tibilogOp(){
		Tpl::setDirquna('shop');
        Tpl::showpage('tibi.log');
    }
  /**
     * 输出XML数据
     */
    public function get_tibixmlOp() {
        $where = array();
        if ($_POST['query'] != '') {
            switch($_POST['qtype']){
                case 'a_owner_id':
                    $where['a_owner_id'] = $_POST['query'];
                    break;
                case 'a_owner_name':
                    $where['a_owner_name'] = array('like',"%{$_POST['query']}%");
                    break; 
            }
        }
        $order = '';
        //a_id,a_address,a_num,a_state,a_owner_id,a_owner_name,a_addtime
        $param = array('a_id','a_address','a_num','a_owner_id','a_owner_name','a_state','a_addtime');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $page = !empty($_POST['rp']) ? intval($_POST['rp']) : 15;
        $points_model = Model('tibilog');
        $list_log = $points_model->getPointsLogList($where, '*', 0, $page, $order);
        if (empty($list_log)) $list_log = array();
        $data = array();
        $data['now_page'] = $points_model->shownowpage();
        $data['total_num'] = $points_model->gettotalnum();
        foreach ($list_log as $value) {
            $param = array(); 
            if($value['stagetext']=='申请中'){
            $param['operation'] = "<a class='btn red' onclick='fg_take(".$value['a_id'].",0)'>同意</a>
            <a class='btn red' onclick='fg_take(".$value['a_id'].",1)'>拒绝</a>";
            }else{
                $param['operation'] = "--";
            }
            $param['a_id'] = $value['a_id'];
           
            $param['a_owner_id'] = $value['a_owner_id'];
            $param['a_owner_name'] = $value['a_owner_name'];
            $param['a_address'] = $value['a_address'];
            $param['a_num'] = $value['a_num'];
            $param['a_state'] = $value['stagetext'];
            $param['a_addtime'] = $value['addtimetext']; 
            $data['list'][$value['a_id']] = $param;
        }
        echo Tpl::flexigridXML($data);exit();
    }

    /**
	 * 提币同意拒绝
	 */
	public function voucher_salebackOp(){

        $kid = $_POST['kid'];
        $types = $_POST['types'];
          //查询会员信息 aggrentibi  refusetibi($where,$card)
       
          
       
            $model_voucher = Model('voucher2');
            $member_info = Model('tibilog')->getPointsInfo($kid);
            $data = array();
            $where = array();
            $where['a_id'] = $kid;
			$data['a_owner_id'] = $member_info['a_owner_id'];
			$data['a_owner_name'] =$member_info['a_owner_name']; 
			$data['a_num'] =$member_info['a_num']; 
		
			$data['a_addtime'] = TIMESTAMP;
            try {
                $model_voucher->beginTransaction();
                if($types==0){
                    $data['a_state'] = 1;
                    $model_voucher->aggrentibi($where,$data);
                }
                else{
                    $data['a_state'] =2;
                    $model_voucher->refusetibi($where,$data);
                } 
                $model_voucher->commit();
                output_data('1');
            } catch (Exception $e) {
                $model_voucher->rollback();
                output_error($e->getMessage());
            } 

    }
}
