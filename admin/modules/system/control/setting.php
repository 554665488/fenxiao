<?php
/**
 * 网站设置
 */



defined('In33hao') or exit('Access Invalid!');
class settingControl extends SystemControl{
    private $links = array(
        array('url'=>'act=setting&op=base','lang'=>'web_set'),
        array('url'=>'act=setting&op=dump','lang'=>'dis_dump'),
        array('url'=>'act=setting&op=login','lang'=>'loginSettings'),
        array('url'=>'act=setting&op=reward','lang'=>'rewardSettings'),
    );
    public function __construct(){
        parent::__construct();
        Language::read('setting');
    }

    public function indexOp() {
        $this->baseOp();
    }

    /**
     * 基本信息
     */
    public function baseOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            $update_array['time_zone'] = $this->setTimeZone($_POST['time_zone']);
            $update_array['site_name'] = $_POST['site_name'];
            $update_array['statistics_code'] = $_POST['statistics_code'];
            $update_array['icp_number'] = $_POST['icp_number'];
            $update_array['site_status'] = $_POST['site_status'];
            $update_array['closed_reason'] = $_POST['closed_reason'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('nc_edit,web_set'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                $this->log(L('nc_edit,web_set'),0);
                showMessage(L('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        foreach ($this->getTimeZone() as $k=>$v) {
            if ($v == $list_setting['time_zone']){
                $list_setting['time_zone'] = $k;break;
            }
        }
        Tpl::output('list_setting',$list_setting);

        //输出子菜单
        Tpl::output('top_link',$this->sublink($this->links,'base'));

		Tpl::setDirquna('system');
        Tpl::showpage('setting.base');
    }

    /**
     * 防灌水设置
     */
    public function dumpOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['captcha_status_login'] = $_POST['captcha_status_login'];
            $update_array['captcha_status_register'] = $_POST['captcha_status_register'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('nc_edit,dis_dump'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                $this->log(L('nc_edit,dis_dump'),0);
                showMessage(L('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        Tpl::output('list_setting',$list_setting);
        Tpl::output('top_link',$this->sublink($this->links,'dump'));
		Tpl::setDirquna('system');
        Tpl::showpage('setting.dump');
    }

    /**
     * 设置时区
     *
     * @param int $time_zone 时区键值
     */
    private function setTimeZone($time_zone){
        $zonelist = $this->getTimeZone();
        return empty($zonelist[$time_zone]) ? 'Asia/Shanghai' : $zonelist[$time_zone];
    }

    private function getTimeZone(){
        return array(
        '-12' => 'Pacific/Kwajalein',
        '-11' => 'Pacific/Samoa',
        '-10' => 'US/Hawaii',
        '-9' => 'US/Alaska',
        '-8' => 'America/Tijuana',
        '-7' => 'US/Arizona',
        '-6' => 'America/Mexico_City',
        '-5' => 'America/Bogota',
        '-4' => 'America/Caracas',
        '-3.5' => 'Canada/Newfoundland',
        '-3' => 'America/Buenos_Aires',
        '-2' => 'Atlantic/St_Helena',
        '-1' => 'Atlantic/Azores',
        '0' => 'Europe/Dublin',
        '1' => 'Europe/Amsterdam',
        '2' => 'Africa/Cairo',
        '3' => 'Asia/Baghdad',
        '3.5' => 'Asia/Tehran',
        '4' => 'Asia/Baku',
        '4.5' => 'Asia/Kabul',
        '5' => 'Asia/Karachi',
        '5.5' => 'Asia/Calcutta',
        '5.75' => 'Asia/Katmandu',
        '6' => 'Asia/Almaty',
        '6.5' => 'Asia/Rangoon',
        '7' => 'Asia/Bangkok',
        '8' => 'Asia/Shanghai',
        '9' => 'Asia/Tokyo',
        '9.5' => 'Australia/Adelaide',
        '10' => 'Australia/Canberra',
        '11' => 'Asia/Magadan',
        '12' => 'Pacific/Auckland'
        );
    }

    /**
     * 登录主题图片
     */
    public function loginOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $input = array();
            //上传图片
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_PATH.'/login');
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','1.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['login_pic1']['name'])){
                $result = $upload->upfile('login_pic1');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[] = $upload->file_name;
                }
            }elseif ($_POST['old_login_pic1'] != ''){
                $input[] = '1.jpg';
            }

            $upload->set('default_dir',ATTACH_PATH.'/login');
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','2.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['login_pic2']['name'])){
                $result = $upload->upfile('login_pic2');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[] = $upload->file_name;
                }
            }elseif ($_POST['old_login_pic2'] != ''){
                $input[] = '2.jpg';
            }

            $upload->set('default_dir',ATTACH_PATH.'/login');
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','3.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['login_pic3']['name'])){
                $result = $upload->upfile('login_pic3');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[] = $upload->file_name;
                }
            }elseif ($_POST['old_login_pic3'] != ''){
                $input[] = '3.jpg';
            }

            $upload->set('default_dir',ATTACH_PATH.'/login');
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','4.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['login_pic4']['name'])){
                $result = $upload->upfile('login_pic4');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[] = $upload->file_name;
                }
            }elseif ($_POST['old_login_pic4'] != ''){
                $input[] = '4.jpg';
            }

            $update_array = array();
            if (count($input) > 0){
                $update_array['login_pic'] = serialize($input);
            }

            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('nc_edit,loginSettings'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                $this->log(L('nc_edit,loginSettings'),0);
                showMessage(L('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        if ($list_setting['login_pic'] != ''){
            $list = unserialize($list_setting['login_pic']);
        }
        Tpl::output('list',$list);
        Tpl::output('top_link',$this->sublink($this->links,'login'));
        Tpl::setDirquna('system');
        Tpl::showpage('setting.login');
    }

    //执行计划任务 v5.2
    public function exetargetOp()
    {

        header("content-type:text/html; charset=utf-8");
        $page=BASE_SITE_URL.'/crontab/cj_index.php?act=minutes';
        $html = file_get_contents($page,'r');
        $page=BASE_SITE_URL.'/crontab/cj_index.php?act=hour';
        $html = file_get_contents($page,'r');
        $page=BASE_SITE_URL.'/crontab/cj_index.php?act=date';
        $html = file_get_contents($page,'r');

	showMessage(计划任务执行成功,'index.php?act=setting&op=base');
    }

    public function rewardOp(){
            $model_setting = Model('setting');
            if (chksubmit()){
                $update_array = array();
                $update_array['hongbao_amount'] = trim($_POST['hongbao_amount']);
                $update_array['cashout_fee'] = trim($_POST['cashout_fee']);
				$update_array['invite_fee'] = trim($_POST['invite_fee']);
				$update_array['manage_fee'] = trim($_POST['manage_fee']);
				$update_array['pingji_fee'] = trim($_POST['pingji_fee']);
				$update_array['starttime'] = trim($_POST['starttime']);
				$update_array['endtime'] = trim($_POST['endtime']);
				$update_array['sale_fee'] = trim($_POST['sale_fee']);
				$update_array['sale_days'] = trim($_POST['sale_days']);
				$update_array['sale_count'] = trim($_POST['sale_count']);
                $result = $model_setting->updateRewardSetting($update_array);
                if ($result === true){
                    $this->log(L('nc_edit,web_set'),1);
                    showMessage(L('nc_common_save_succ'));
                }else {
                    $this->log(L('nc_edit,web_set'),0);
                    showMessage(L('nc_common_save_fail'));
                }
            }

            $list_setting = $model_setting->getRewardSetting();
            if($list_setting['hongbao_amount'] == ''){
               $list_setting['hongbao_amount'] = '5';
            }
            if($list_setting['cashout_fee'] == ''){
             $list_setting['cashout_fee'] = '0.05';
            }
			if($list_setting['invite_fee'] == ''){
             $list_setting['invite_fee'] = '0.03';
            }
			if($list_setting['manage_fee'] == ''){
             $list_setting['manage_fee'] = '0.02,0.04,0.06,0.08,0.1';
            }
			if($list_setting['pingji_fee'] == ''){
             $list_setting['pingji_fee'] = '0.005';
            }
			if($list_setting['starttime'] == ''){
               $list_setting['starttime'] = '09:00:00';
            }
			if($list_setting['endtime'] == ''){
               $list_setting['endtime'] = '16:00:00';
            }
			
			if($list_setting['sale_fee'] == ''){
               $list_setting['sale_fee'] = '0.2638';
            }
			if($list_setting['sale_days'] == ''){
               $list_setting['sale_days'] = '7';
            }
			if($list_setting['sale_count'] == ''){
               $list_setting['sale_count'] = '30';
            }
            $na = array();

            $na['hongbao_amount'] = "红包金额";
            $na['cashout_fee'] = "提现费率";
			$na['invite_fee'] = "动态奖";
			$na['manage_fee'] = "管理奖";
			$na['pingji_fee'] = "平级奖";
			$na['starttime'] = "开始开放时间";
			$na['endtime'] = "结束开放时间";
			$na['sale_fee'] = "交易管理费";
			$na['sale_days'] = "交易延长时间";
			$na['sale_count'] = "每日交易数量";
            Tpl::output('rewardname',$na);
            Tpl::output('list',$list_setting);
            Tpl::output('top_link',$this->sublink($this->links,'reward'));
            Tpl::setDirquna('system');
            Tpl::showpage('setting.reward');
        }

}
