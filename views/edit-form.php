<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var string $js_url
 * @var string $action
 * @var string $full_day
 * @var array{start_date:string,start_time:string,end_date:string,end_time:string,summary:string,linkadr:string,linktxt:string,location:string} $event
 * @var array<string,string> $recur_options
 * @var string $button_label
 * @var string $csrf_token
 */
?>

<script type="module" src="<?=$this->esc($js_url)?>"></script>
<form method="post" action="<?=$this->esc($action)?>" class="calendar_input">
  <div>
    <p>
      <label>
        <input type="checkbox" name="full_day" <?=$this->esc($full_day)?>>
        <span><?=$this->text('label_full_day')?></span>
      </label>
    </p>
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
        <span><?=$this->text('label_recur')?></span>
        <select name="recur">
<?foreach ($recur_options as $key => $selected):?>
          <option value="<?=$this->esc($key)?>" <?=$this->esc($selected)?>><?=$this->text("label_recur_$key")?></option>
<?endforeach?>
        </select>
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
        <span><?=$this->text('label_description')?></span>
        <textarea class="calendar_input_event calendar_textarea_description" name="linktxt"><?=$this->esc($event['linktxt'])?></textarea>
      </label>
    </p>
    <input type="hidden" name="calendar_do">
  </div>
  <p class="calendar_buttons">
    <button><?=$this->text($button_label)?></button>
  </p>
  <input type="hidden" name="calendar_token" value="<?=$this->raw($csrf_token)?>">
</form>
