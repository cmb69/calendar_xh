<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<form method="post" action="<?=$action?>" class="calendar_input">
  <div>
    <label>
      <?=$this->text('event_date_start')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="datestart" value="<?=$event['start_date']?>">
    </label>
<?php if ($showEventTime):?>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="starttime" value="<?=$event['start_time']?>">
    </label>
<?php else:?>
    <input type="hidden" maxlength="5" name="starttime" value="<?=$event['start_time']?>">
<?php endif?>
    <label>
      <?=$this->text('event_date_end')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="dateend" value="<?=$event['end_date']?>">
    </label>
<?php if ($showEventTime):?>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="endtime" value="<?=$event['end_time']?>">
    </label>
<?php else:?>
    <input type="hidden" maxlength="5" name="endtime" value="<?=$event['end_time']?>">
<?php endif?>
    <label>
      <?=$this->text('event_summary')?>
      <input class="calendar_input_event" type="text" name="event" value="<?=$event['summary']?>" required>
    </label>
<?php if ($showEventLocation):?>
    <label>
      <?=$this->text('event_location')?>
      <input type="text" class="calendar_input_event" name="location" value="<?=$event['location']?>">
    </label>
<?php else:?>
    <input type="hidden" name="location" value="<?=$event['location']?>">
<?php endif?>
<?php if ($showEventLink):?>
    <label>
      <?=$this->text('event_link')?>
      <input type="text" class="calendar_input_event" name="linkadr" value="<?=$event['linkadr']?>">
    </label>
    <label>
      <?=$this->text('event_link_txt')?>
      <input type="text" class="calendar_input_event" name="linktxt" value="<?=$event['linktxt']?>">
    </label>
<?php else:?>
    <input type="hidden" name="linkadr" value="<?=$event['linkadr']?>">
    <input type="hidden" name="linktxt" value="<?=$event['linktxt']?>">
<?php endif?>
  </div>
  <p>
    <button><?=$button_label?></button>
  </p>
  <?=$csrf_token?>
</form>
