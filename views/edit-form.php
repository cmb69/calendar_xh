<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<form method="post" action="<?=$this->action?>" class="calendar_input">
  <div>
    <label>
      <?=$this->text('event_date_start')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="datestart" value="<?=$this->escape($this->event->getIsoStartDate())?>">
    </label>
<?php if ($this->showEventTime):?>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="starttime" value="<?=$this->escape($this->event->getIsoStartTime())?>">
    </label>
<?php else:?>
    <input type="hidden" maxlength="5" name="starttime" value="<?=$this->escape($this->event->getIsoStartTime())?>">
<?php endif?>
    <label>
      <?=$this->text('event_date_end')?>
      <input type="date" class="calendar_input_date" maxlength="10" name="dateend" value="<?=$this->escape($this->event->getIsoEndDate())?>">
    </label>
<?php if ($this->showEventTime):?>
    <label>
      <?=$this->text('event_time')?>
      <input type="time" class="calendar_input_time" maxlength="5" name="endtime" value="<?=$this->escape($this->event->getIsoEndTime())?>">
    </label>
<?php else:?>
    <input type="hidden" maxlength="5" name="endtime" value="<?=$this->escape($this->event->getIsoEndTime())?>">
<?php endif?>
    <label>
      <?=$this->text('event_summary')?>
      <input class="calendar_input_event" type="text" name="event" value="<?=$this->escape($this->event->summary)?>" required>
    </label>
<?php if ($this->showEventLocation):?>
    <label>
      <?=$this->text('event_location')?>
      <input type="text" class="calendar_input_event" name="location" value="<?=$this->escape($this->event->location)?>">
    </label>
<?php else:?>
    <input type="hidden" name="location" value="<?=$this->escape($this->event->location)?>">
<?php endif?>
<?php if ($this->showEventLink):?>
    <label>
      <?=$this->text('event_link')?>
      <input type="text" class="calendar_input_event" name="linkadr" value="<?=$this->escape($this->event->linkadr)?>">
    </label>
    <label>
      <?=$this->text('event_link_txt')?>
      <input type="text" class="calendar_input_event" name="linktxt" value="<?=$this->escape($this->event->linktxt)?>">
    </label>
<?php else:?>
    <input type="hidden" name="linkadr" value="<?=$this->escape($this->event->linkadr)?>">
    <input type="hidden" name="linktxt" value="<?=$this->escape($this->event->linktxt)?>">
<?php endif?>
  </div>
  <p>
    <button><?=$this->button_label()?></button>
  </p>
  <?=$this->csrf_token?>
</form>
