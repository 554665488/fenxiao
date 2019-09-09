<?php
/**
 * 输入订单模型
 *
 *
*/
defined('In33hao') or exit('Access Invalid!');

class input_formModel extends Model {
    public function __construct() {
        parent::__construct('input_form');
    }
    
    public function addFormInfo($insert) {
        return $this->insert($insert);
    }

    public function getFormInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }
	
    public function saveFormInfo($update, $condition){
        return $this->where($condition)->update($update);
    }
    public function getFormList($condition = array(), $field = '*', $page = null, $order = 'id desc', $limit = '') {
       return $this->table('input_form')->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }
}
