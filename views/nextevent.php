<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<div class="nextevent_date">
<?php if (isset($this->event)):?>
  <?=$this->date?>
</div>
<div class="calendar_marquee_outer">
  <div class="calendar_marquee">
    <div class="nextevent_event"><?=$this->escape($this->event->summary)?></div>
    <div class="nextevent_date"><?=$this->escape($this->event_text)?></div>
    <div class="nextevent_location"><?=$this->escape($this->location)?></div>
  </div>
</div>
<?php else:?>
  <br><?=$this->text('notice_no_next_event')?>
</div>
<?php endif?>
