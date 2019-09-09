<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['web_set'];?></h3>
        <h5><?php echo $lang['web_set_subhead'];?></h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
  </div>
  <form method="post" enctype="multipart/form-data" name="form1">
    <input type="hidden" name="form_submit" value="ok" />
    <?php
    $na = $output['rewardname'];
    foreach ($output['list'] as $key => $value) :?>
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
            <label for="<?php echo $key; ?>"><?php echo $na[$key]; ?></label>
        </dt>
        <dd class="opt">
            <input id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo $value;?>" class="input-txt" type="text" />
        </dd>
      </dl>
    </div>
    <?php endforeach;?>
     <div class="ncap-form-default">
         <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" onclick="document.form1.submit()"><?php echo $lang['nc_submit'];?></a></div>
    </div>

  </form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">

</script>
