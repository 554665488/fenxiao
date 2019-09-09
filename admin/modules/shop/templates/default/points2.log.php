<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['nc_member_points2manage']?></h3>
        <h5><?php echo $lang['nc_member_pointsmanage2_subhead']?></h5>
      </div>
      <ul class="tab-base nc-row">
        <li><a href="JavaScript:void(0);" class="current">优惠券明细</a></li>
       <!-- <li><a href="index.php?act=points2&op=setting">规则设置</a></li>-->
        <li><a href="index.php?act=points2&op=addpoints">优惠券增减</a></li>
      </ul>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo $lang['admin_points_log_help1'];?></li>
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=points2&op=get_xml',
        colModel : [
            {display: '操作', name : 'operation', width : 60, sortable : false, align: 'center', className: 'handle-s'},
            {display: '日志ID', name : 'pl_id', width : 60, sortable : true, align: 'center'},
            {display: '会员ID', name : 'pl_memberid', width : 60, sortable : true, align: 'center'},
            {display: '会员名称', name : 'pl_membername', width : 100, sortable : true, align: 'left'},
            {display: '优惠券', name : 'pl_points', width : 80, sortable : true, align: 'center'},            
            {display: '操作阶段', name : 'pl_stage', width : 80, sortable : false, align: 'left'},
			{display: '操作时间', name : 'pl_addtime', width : 120, sortable : true, align: 'center'},
            {display: '操作描述', name : 'pl_desc', width : 300, sortable : false, align: 'left'},			
            {display: '管理员名称', name : 'pl_adminname', width : 100, sortable : false, align: 'left'}
            ],
        searchitems : [
            {display: '会员ID', name : 'pl_memberid'},
            {display: '会员名称', name : 'pl_membername_like'},
            {display: '管理员名称', name : 'pl_adminname_like'}
            ],
        sortname: "pl_id",
        sortorder: "desc",
        title: '优惠券明细日志列表'
    });
});
</script> 
