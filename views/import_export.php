<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var string $url
 * @var string $export_url
 * @var list<string> $files
 */
?>

<div class="calendar_import">
  <h1>Calendar – <?=$this->text('label_import_export')?></h1>
  <form action="<?=$this->esc($url)?>" method="POST">
    <fieldset>
      <legend><?=$this->text('label_import')?></legend>
<?foreach ($files as $file):?>
      <button name="calendar_ics" value="<?=$this->esc($file)?>"><?=$this->esc($file)?></button>
<?endforeach?>
    </fieldset>
    <fieldset>
      <legend><?=$this->text('label_export')?></legend>
      <button formaction="<?=$this->esc($export_url)?>" name="calendar_ics" value="calendar.ics">calendar.ics</button>
    </fieldset>
  </form>
</div>
