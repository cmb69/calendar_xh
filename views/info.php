<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<h1>Calendar <?=$this->version()?></h1>
<div class="calendar_syscheck">
  <h2><?php echo $this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
  <p class="xh_<?php echo $this->escape($check->state)?>"><?php echo $this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
