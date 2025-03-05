<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<h1>Calendar <?=$version?></h1>
<div class="calendar_syscheck">
  <h2><?=$this->text('syscheck_title')?></h2>
<?foreach ($checks as $check):?>
  <p class="xh_<?=$check['state']?>"><?=$this->text('syscheck_message', $check['label'], $check['stateLabel'])?></p>
<?endforeach?>
</div>
