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
    <p>
      <label>
        <span><?=$this->text('event_date_start')?></span>
        <input type="datetime-local" class="calendar_input_date" maxlength="10" name="datestart" value="<?=$this->esc($event['start_date'])?>">
      </label>
    </p>
    <p>
      <label>
        <span><?=$this->text('event_date_end')?></span>
        <input type="datetime-local" class="calendar_input_date" maxlength="10" name="dateend" value="<?=$this->esc($event['end_date'])?>">
      </label>
    </p>
    <p>
      <label>
        <span><?=$this->text('event_summary')?></span>
        <input class="calendar_input_event" type="text" name="event" value="<?=$this->esc($event['summary'])?>" required>
      </label>
    </p>
    <p>
      <label>
        <span><?=$this->text('event_location')?></span>
        <input type="text" class="calendar_input_event" name="location" value="<?=$this->esc($event['location'])?>">
      </label>
    </p>
    <p>
      <label>
        <span><?=$this->text('event_link')?></span>
        <input type="text" class="calendar_input_event" name="linkadr" value="<?=$this->esc($event['linkadr'])?>">
      </label>
    </p>
    <p>
      <label>
        <span><?=$this->text('event_link_txt')?></span>
        <input type="text" class="calendar_input_event" name="linktxt" value="<?=$this->esc($event['linktxt'])?>">
      </label>
    </p>
  </div>
  <p class="calendar_buttons">
    <button name="calendar_do"><?=$this->text($button_label)?></button>
  </p>
  <input type="hidden" name="calendar_token" value="<?=$this->raw($csrf_token)?>">
</form>
