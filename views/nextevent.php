<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<div class="nextevent_date">
<?php if (isset($summary)):?>
  <?=$date?>
</div>
<div class="calendar_marquee_outer">
  <div class="calendar_marquee">
    <div class="nextevent_event"><?=$summary?></div>
    <div class="nextevent_date"><?=$event_text?></div>
<?php   if (isset($event_text_2)):?>
    <div class="nextevent_date"><?=$event_text_2?></div>
<?php   endif?>
    <div class="nextevent_location"><?=$location?></div>
  </div>
</div>
<?php else:?>
  <br><?=$this->text('notice_no_next_event')?>
</div>
<?php endif?>
