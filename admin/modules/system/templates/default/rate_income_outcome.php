<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="settingForm">
    <input type="hidden" name="form_submit" value="ok" />
	<input type="text" name="startTime" placeholder="yyyy-mm-dd" value="<?php echo date('Y-m-d',$output['startTime']);?>" ><input type="text" name="endTime"  placeholder="yyyy-mm-dd" value="<?php echo date('Y-m-d',$output['endTime']);?>" >
    <input type="submit" value="统计">

	<div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label>新增报单会员数</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['member_count'];?></p>
        </dd>
      </dl>
	  <!--<dl class="row">
        <dt class="tit">
          <label>拨币总额</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['amount'];?>元</p>
        </dd>
      </dl>
	  <dl class="row">
        <dt class="tit">
          <label>拨币减除总额</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['amount2'];?>元</p>
        </dd>
      </dl>
	  <dl class="row">
        <dt class="tit">
          <label>合计拨币总额</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['amount3'];?>元</p>
        </dd>
      </dl>
	  <dl class="row">
        <dt class="tit">
          <label>奖金支出</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['pd_amount'];?>元</p>
        </dd>
      </dl>-->
      <dl class="row">
        <dt class="tit">
          <label>新增首单总额</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['orderamount'];?>元 </p>
        </dd>
      </dl>
	  <dl class="row">
        <dt class="tit">
          <label>复投总额</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['orderamount2'];?>元 </p>
        </dd>
      </dl>
	  <dl class="row">
        <dt class="tit">
          <label>新增注册会员数</label>
        </dt>
        <dd class="opt">
          <p class="notic"><?php echo $output['member_count2'];?></p>
        </dd>
      </dl>
	 </div>
  </form>
</div>
