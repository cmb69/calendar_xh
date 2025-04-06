<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var string $version
 * @var list<array{state:string,label:string,stateLabel:string}> $checks
 */
?>

<h1>Calendar <?=$this->esc($version)?></h1>
<div class="calendar_syscheck">
  <h2><?=$this->text('syscheck_title')?></h2>
<?foreach ($checks as $check):?>
  <p class="xh_<?=$this->esc($check['state'])?>"><?=$this->text('syscheck_message', $check['label'], $check['stateLabel'])?></p>
<?endforeach?>
</div>
