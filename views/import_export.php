<?php

use Plib\View;

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var View $this
 * @var string $url
 * @var string $export_url
 * @var list<string> $files
 * @var ?int $ignored
 */
?>

<div class="calendar_import">
  <h1>Calendar – <?=$this->text('label_import_export')?></h1>
<?if ($ignored !== null):?>
  <?=$this->pmessage("info", "message_ignored", $ignored)?>
<?endif?>
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
