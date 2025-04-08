<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var string $action
 * @var bool $showEventTime
 * @var bool $showEventLocation
 * @var bool $showEventLink
 * @var array{start_date:string,start_time:string,end_date:string,end_time:string,summary:string,linkadr:string,linktxt:string,location:string} $event
 * @var string $button_label
 * @var string $csrf_token
 */
?>

<form method="post" action="<?=$this->esc($action)?>" class="calendar_input">
  <div>
    <label>
      <?=$this->text('event_date_start')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="datestart" value="<?=$this->esc($event['start_date'])?>">
    </label>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="starttime" value="<?=$this->esc($event['start_time'])?>">
    </label>
    <label>
      <?=$this->text('event_date_end')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="dateend" value="<?=$this->esc($event['end_date'])?>">
    </label>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="endtime" value="<?=$this->esc($event['end_time'])?>">
    </label>
    <label>
      <?=$this->text('event_summary')?>
      <input class="calendar_input_event" type="text" name="event" value="<?=$this->esc($event['summary'])?>" required>
    </label>
    <label>
      <?=$this->text('event_location')?>
      <input type="text" class="calendar_input_event" name="location" value="<?=$this->esc($event['location'])?>">
    </label>
    <label>
      <?=$this->text('event_link')?>
      <input type="text" class="calendar_input_event" name="linkadr" value="<?=$this->esc($event['linkadr'])?>">
    </label>
    <label>
      <?=$this->text('event_link_txt')?>
      <input type="text" class="calendar_input_event" name="linktxt" value="<?=$this->esc($event['linktxt'])?>">
    </label>
  </div>
  <p>
    <button name="calendar_do"><?=$this->text($button_label)?></button>
  </p>
  <input type="hidden" name="calendar_token" value="<?=$this->raw($csrf_token)?>">
</form>
