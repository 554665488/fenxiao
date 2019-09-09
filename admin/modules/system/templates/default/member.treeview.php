<?php defined('In33hao') or exit('Access Invalid!');?>

<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jqtree/tree.jquery.js"></script>
<link href="<?php echo ADMIN_RESOURCE_URL;?>/js/jqtree/jqtree.css" rel="stylesheet" type="text/css"/>

<div class="page">
   <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['member_index_manage']?></h3>
        <h5><?php echo $lang['member_system_manage_subhead']?></h5>
      </div> <?php echo $output['top_link'];?>
    </div>
  </div>
    <div id="tree1" data-url="index.php?act=member&op=treeview"></div>
</div>
<script type="text/javascript">
    var data = <?php echo $output['data'];?>;
$(function() {
    $('#tree1').tree({
        data: data
    });
});
</script>
