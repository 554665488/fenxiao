<?php
/**
 * 默认展示页面
 */



defined('In33hao') or exit('Access Invalid!');
class indexControl extends BaseLoginControl{
    public function __construct() {
        @header("location: " . urlMember('member_information'));
    }
}
