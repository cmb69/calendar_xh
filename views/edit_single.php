<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $start_date
 * @var string $recurring
 * @var string $until
 * @var string $summary
 * @var string $date
 * @var string $csrf_token
 */
?>

<form method="post" class="calendar_input">
  <p>
    <label>
      <span><?=$this->text('event_summary')?></span>
      <input type="text" value="<?=$this->esc($summary)?>" disabled>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('event_date_start')?></span>
      <input type="date" value="<?=$this->esc($start_date)?>" disabled>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_recur')?></span>
      <input type="text" value="<?=$this->text("label_recur_$recurring")?>" disabled>
<?if ($until):?>
      <label>
        <span><?=$this->text('label_recur_until')?></span>
        <input type="date" value="<?=$this->esc($until)?>" disabled>
      </label>
<?endif?>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text("label_occurrence")?></span>
      <input type="date" name="editdate" value="<?=$this->esc($date)?>" min="<?=$this->esc($start_date)?>" max="<?=$this->esc($until)?>">
    </label>
  </p>
  <p class="calendar_buttons">
    <button name="calendar_do"><?=$this->text("label_edit_single")?></button>
  </p>
  <input type="hidden" name="calendar_token" value="<?=$this->esc($csrf_token)?>">
</form>
