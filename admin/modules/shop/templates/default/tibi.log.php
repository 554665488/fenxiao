<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>增值券管理</h3>
        <h5></h5>
      </div>
      <ul class="tab-base nc-row">
        <li><a href="index.php?act=points&op=pointslog">增值券明细</a></li>
        <li><a href="index.php?act=points&op=addpoints">增值券增减</a></li>
        <li><a href="index.php?act=points&op=addpointsog">增值券原始仓</a></li>
        <li><a href="index.php?act=points&op=pvlog">PV值明细</a></li>
        <li><a href="JavaScript:void(0);" class="current">提币明细</a></li>
      </ul>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>提币明细</li>
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){ 
    $("#flexigrid").flexigrid({
        url: 'index.php?act=points&op=get_tibixml',
        colModel : [
            {display: '操作', name : 'operation', width :120, sortable : false, align: 'center'},
            {display: '日志ID', name : 'a_id', width : 60, sortable : true, align: 'center'},
            {display: '会员ID', name : 'a_owner_id', width : 60, sortable : true, align: 'center'},
            {display: '会员名称', name : 'a_owner_name', width : 100, sortable : true, align: 'left'},
            {display: '钱包地址', name : 'a_address', width : 80, sortable : true, align: 'center'},
            {display: '金额', name : 'a_num', width : 80, sortable : true, align: 'center'},
            {display: '操作阶段', name : 'a_state', width : 80, sortable : false, align: 'left'},
			      {display: '操作时间', name : 'a_addtime', width : 120, sortable : true, align: 'center'} 
            ],
        searchitems : [
            {display: '会员ID', name : 'a_owner_id'},
            {display: '会员名称', name : 'a_owner_name'} 
            ],
        sortname: "a_id",
        sortorder: "desc",
        title: '提币明细日志列表'
    });
  
});
function fg_take(kid,types){ 
				$.ajax({
					type: 'post',
					url:'index.php?act=points&op=voucher_saleback',
					data: { 
						kid: kid,
						types:types
					},
					dataType: 'json',
					async: false,
					success: function(result) {
						console.log(result)
						if (result.code == 200) {
              $('#flexigrid').flexReload();//表格重载
						}
					}
        });  
        $('#flexigrid').flexReload();//表格重载
    }
</script>
