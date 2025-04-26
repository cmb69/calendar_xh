<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var bool $has_next_event
 * @var string $date
 * @var string $summary
 * @var string $event_text
 * @var ?string $event_text_2
 * @var string $location
 * @var string $class
 */
?>

<div class="nextevent_date">
<?if ($has_next_event):?>
  <?=$this->raw($date)?>
</div>
<div class="calendar_marquee_outer <?=$this->esc($class)?>">
  <div class="calendar_marquee">
    <div class="nextevent_event"><?=$this->esc($summary)?></div>
    <div class="nextevent_date"><?=$this->raw($event_text)?></div>
<?  if (isset($event_text_2)):?>
    <div class="nextevent_date"><?=$this->esc($event_text_2)?></div>
<?  endif?>
    <div class="nextevent_location"><?=$this->esc($location)?></div>
  </div>
</div>
<?else:?>
  <br><?=$this->text('notice_no_next_event')?>
</div>
<?endif?>
